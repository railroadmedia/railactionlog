<?php

namespace Railroad\ActionLog\Listeners\Subscriptions;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Entities\Payment;
use Railroad\Ecommerce\Entities\Subscription;
use Railroad\Ecommerce\Events\Subscriptions\CommandSubscriptionRenewed;

class CommandSubscriptionRenewedListener
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
     * @param CommandSubscriptionRenewed $commandSubscriptionRenewed
     *
     * @throws Exception
     */
    public function handle(CommandSubscriptionRenewed $commandSubscriptionRenewed)
    {
        try {

            /** @var $payment Payment */
            $payment = $commandSubscriptionRenewed->getPayment();

            /** @var $subscription Subscription */
            $subscription = $commandSubscriptionRenewed->getSubscription();

            $brand = $subscription->getBrand();

            $this->actionLogService->recordSystemAction(
                $brand,
                ActionLogService::ACTION_CREATE,
                $payment
            );

            $this->actionLogService->recordCommandAction(
                $brand,
                Subscription::ACTION_RENEW,
                $subscription
            );

        } catch (\Throwable $throwable) {
            error_log('Railactionlog ERROR --------------------');
            error_log($throwable);
        }
    }
}
