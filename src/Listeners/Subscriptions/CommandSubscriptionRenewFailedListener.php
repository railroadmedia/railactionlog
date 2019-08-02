<?php

namespace Railroad\ActionLog\Listeners\Subscriptions;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Entities\Payment;
use Railroad\Ecommerce\Entities\Subscription;
use Railroad\Ecommerce\Events\Subscriptions\CommandSubscriptionRenewFailed;
use Railroad\Ecommerce\Services\RenewalService;

class CommandSubscriptionRenewFailedListener
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
     * @param CommandSubscriptionRenewFailed $commandSubscriptionRenewFailed
     *
     * @throws Exception
     */
    public function handle(CommandSubscriptionRenewFailed $commandSubscriptionRenewFailed)
    {
        /** @var $subscription Subscription */
        $subscription = $commandSubscriptionRenewFailed->getSubscription();

        /** @var $oldSubscriptionState Subscription */
        $oldSubscriptionState = $commandSubscriptionRenewFailed->getOldSubscription();

        /** @var $currentUser array */
        $currentUser = auth()->user();

        $brand = $subscription->getBrand();
        $actor = $currentUser['email'];
        $actorId = $currentUser['id'];
        $actorRole = $currentUser['id'] == $subscription->getUser()->getId() ?
                        ActionLogService::ROLE_USER:
                        ActionLogService::ROLE_ADMIN;

        if ($subscription->getNote() == RenewalService::DEACTIVATION_MESSAGE &&
            $subscription->getIsActive() != $oldSubscriptionState->getIsActive()) {

            $this->actionLogService->recordCommandAction(
                $brand,
                Subscription::ACTION_DEACTIVATED,
                $subscription
            );
        }

        $payment = $commandSubscriptionRenewFailed->getPayment();

        if ($payment) {

            /** @var $payment Payment */

            $this->actionLogService->recordCommandAction(
                $brand,
                Payment::ACTION_FAILED_RENEW,
                $payment
            );
        }
    }
}
