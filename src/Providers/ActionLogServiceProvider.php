<?php

namespace Railroad\ActionLog\Providers;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\DBAL\Logging\EchoSQLLogger;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Gedmo\DoctrineExtensions;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Railroad\ActionLog\Listeners\MobileOrderEventListener;
use Railroad\ActionLog\Listeners\Subscriptions\MobileSubscriptionCanceledListener;
use Railroad\ActionLog\Listeners\Subscriptions\MobileSubscriptionRenewedListener;
use Railroad\ActionLog\Listeners\Subscriptions\SubscriptionCreatedListener;
use Railroad\ActionLog\Listeners\Subscriptions\SubscriptionDeactivatedListener;
use Railroad\ActionLog\Listeners\Subscriptions\SubscriptionRenewedListener;
use Railroad\ActionLog\Listeners\Subscriptions\SubscriptionRenewFailedListener;
use Railroad\ActionLog\Listeners\Subscriptions\SubscriptionUpdatedListener;
use Railroad\ActionLog\Managers\ActionLogEntityManager;
use Railroad\Doctrine\TimestampableListener;
use Railroad\Ecommerce\Events\MobileOrderEvent;
use Railroad\Ecommerce\Events\Subscriptions\MobileSubscriptionCanceled;
use Railroad\Ecommerce\Events\Subscriptions\MobileSubscriptionRenewed;
use Railroad\Ecommerce\Events\Subscriptions\SubscriptionCreated;
use Railroad\Ecommerce\Events\Subscriptions\SubscriptionDeactivated;
use Railroad\Ecommerce\Events\Subscriptions\SubscriptionRenewed;
use Railroad\Ecommerce\Events\Subscriptions\SubscriptionRenewFailed;
use Railroad\Ecommerce\Events\Subscriptions\SubscriptionUpdated;
use Redis;

class ActionLogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->listen = [
            MobileSubscriptionCanceled::class => [MobileSubscriptionCanceledListener::class],
            MobileSubscriptionRenewed::class => [MobileSubscriptionRenewedListener::class],
            MobileOrderEvent::class => [MobileOrderEventListener::class],
            SubscriptionCreated::class => [SubscriptionCreatedListener::class],
            SubscriptionDeactivated::class => [SubscriptionDeactivatedListener::class], // todo - remove if not used
            SubscriptionRenewed::class => [SubscriptionRenewedListener::class],
            SubscriptionRenewFailed::class => [SubscriptionRenewFailedListener::class],
            SubscriptionUpdated::class => [SubscriptionUpdatedListener::class],
        ];

        parent::boot();

        $this->publishes(
            [
                __DIR__ . '/../../config/railactionlog.php' => config_path('railactionlog.php'),
            ]
        );

        if (config('railactionlog.data_mode') == 'host') {
            $this->loadMigrationsFrom(__DIR__ . '/../../migrations');
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->setupEntityManager();
    }

    private function setupEntityManager()
    {
        // set proxy dir to temp folder
        if (app()->runningUnitTests()) {
            $proxyDir = sys_get_temp_dir();
        }
        else {
            $proxyDir = sys_get_temp_dir() . '/railroad/railactionlog/proxies';
        }

        // setup redis
        $redis = new Redis();
        $redis->connect(config('railactionlog.redis_host'), config('railactionlog.redis_port'));

        $redisCache = new RedisCache();
        $redisCache->setRedis($redis);

        app()->instance('ActionLogRedisCache', $redisCache);
        app()->instance('ActionLogArrayCache', new ArrayCache());

        // annotation reader
        AnnotationRegistry::registerLoader('class_exists');

        $annotationReader = new AnnotationReader();

        $cachedAnnotationReader =
            new CachedReader($annotationReader, $redisCache, config('railactionlog.development_mode'));

        $driverChain = new MappingDriverChain();

        DoctrineExtensions::registerAbstractMappingIntoDriverChainORM($driverChain, $cachedAnnotationReader);

        // entities
        foreach (config('railactionlog.entities') as $driverConfig) {
            $annotationDriver = new AnnotationDriver($cachedAnnotationReader, $driverConfig['path']);

            $driverChain->addDriver($annotationDriver, $driverConfig['namespace']);
        }

        // timestamps
        $timestampableListener = new TimestampableListener();
        $timestampableListener->setAnnotationReader($cachedAnnotationReader);

        // event manager
        $eventManager = new EventManager();
        $eventManager->addEventSubscriber($timestampableListener);

        // orm config
        $ormConfiguration = new Configuration();
        $ormConfiguration->setMetadataCacheImpl($redisCache);
        $ormConfiguration->setQueryCacheImpl($redisCache);
        $ormConfiguration->setResultCacheImpl($redisCache);
        $ormConfiguration->setProxyDir($proxyDir);
        $ormConfiguration->setProxyNamespace('DoctrineProxies');
        $ormConfiguration->setAutoGenerateProxyClasses(
            config('railactionlog.development_mode') ? AbstractProxyFactory::AUTOGENERATE_ALWAYS :
                AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS
        );
        $ormConfiguration->setMetadataDriverImpl($driverChain);
        $ormConfiguration->setNamingStrategy(new UnderscoreNamingStrategy(CASE_LOWER));

        // database config
        if (config('railactionlog.database_in_memory') !== true) {
            $databaseOptions = [
                'driver' => config('railactionlog.database_driver'),
                'dbname' => config('railactionlog.database_name'),
                'user' => config('railactionlog.database_user'),
                'password' => config('railactionlog.database_password'),
                'host' => config('railactionlog.database_host'),
            ];
        }
        else {
            $databaseOptions = [
                'driver' => config('railactionlog.database_driver'),
                'user' => config('railactionlog.database_user'),
                'password' => config('railactionlog.database_password'),
                'memory' => true,
            ];
        }

        $entityManager = ActionLogEntityManager::create($databaseOptions, $ormConfiguration, $eventManager);

        if (config('railactionlog.enable_query_log')) {
            $logger = new EchoSQLLogger();

            $entityManager->getConnection()
                ->getConfiguration()
                ->setSQLLogger($logger);
        }

        app()->instance(ActionLogEntityManager::class, $entityManager);
    }
}
