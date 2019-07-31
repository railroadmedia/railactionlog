<?php

namespace Railroad\ActionLog\Listeners\Subscriptions;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Entities\Payment;
use Railroad\Ecommerce\Entities\Subscription;
use Railroad\Ecommerce\Events\Subscriptions\MobileSubscriptionCanceled;

class MobileSubscriptionCanceledListener
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
     * @param MobileSubscriptionCanceled $mobileSubscriptionCanceled
     *
     * @throws Exception
     */
    public function handle(MobileSubscriptionCanceled $mobileSubscriptionCanceled)
    {
        /** @var $subscription Subscription */
        $subscription = $mobileSubscriptionCanceled->getSubscription();

        if ($mobileSubscriptionCanceled->getActor() == MobileSubscriptionCanceled::ACTOR_SYSTEM) {
            $this->actionLogService->recordSystemAction(
                $subscription->getBrand(),
                Subscription::ACTION_CANCEL,
                $subscription
            );
        } else {
            $this->actionLogService->recordCommandAction(
                $subscription->getBrand(),
                Subscription::ACTION_CANCEL,
                $subscription
            );
        }
    }
}
