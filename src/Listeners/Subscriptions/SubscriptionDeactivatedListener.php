<?php

namespace Railroad\ActionLog\Listeners\Subscriptions;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Entities\Subscription;
use Railroad\Ecommerce\Events\Subscriptions\SubscriptionDeactivated;

class SubscriptionDeactivatedListener
{
    /**
     * @var ActionLogService
     */
    private $actionLogService;

    /**
     * @param ActionLogService $actionLogService
     */
    public function __construct(ActionLogService $actionLogService)
    {
        $this->actionLogService = $actionLogService;
    }

    /**
     * @param SubscriptionDeactivated $subscriptionDeactivatedEvent
     *
     * @throws Exception
     */
    public function handle(SubscriptionDeactivated $subscriptionDeactivatedEvent)
    {
        try {

            /** @var $subscription Subscription */
            $subscription = $subscriptionDeactivatedEvent->getSubscription();

            $this->actionLogService->recordCommandAction(
                $subscription->getBrand(),
                Subscription::ACTION_DEACTIVATED,
                $subscription
            );

        } catch (\Throwable $throwable) {
            error_log('Railactionlog ERROR --------------------');
            error_log($throwable);
        }
    }
}
