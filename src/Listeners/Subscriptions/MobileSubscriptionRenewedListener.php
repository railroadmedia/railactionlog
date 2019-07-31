<?php

namespace Railroad\ActionLog\Listeners\Subscriptions;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Entities\Payment;
use Railroad\Ecommerce\Entities\Subscription;
use Railroad\Ecommerce\Events\Subscriptions\MobileSubscriptionRenewed;

class MobileSubscriptionRenewedListener
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
     * @param MobileSubscriptionRenewed $mobileSubscriptionRenewedEvent
     *
     * @throws Exception
     */
    public function handle(MobileSubscriptionRenewed $mobileSubscriptionRenewedEvent)
    {
        /** @var $payment Payment */
        $payment = $mobileSubscriptionRenewedEvent->getPayment();

        /** @var $subscription Subscription */
        $subscription = $mobileSubscriptionRenewedEvent->getSubscription();

        $brand = $subscription->getBrand();

        if ($mobileSubscriptionRenewedEvent->getActor() == MobileSubscriptionRenewed::ACTOR_SYSTEM) {
            $this->actionLogService->recordSystemAction(
                $brand,
                ActionLogService::ACTION_CREATE,
                $payment
            );

            $this->actionLogService->recordSystemAction(
                $brand,
                Subscription::ACTION_RENEW,
                $subscription
            );
        } else {
            $this->actionLogService->recordCommandAction(
                $brand,
                ActionLogService::ACTION_CREATE,
                $payment
            );

            $this->actionLogService->recordCommandAction(
                $brand,
                Subscription::ACTION_RENEW,
                $subscription
            );
        }
    }
}
