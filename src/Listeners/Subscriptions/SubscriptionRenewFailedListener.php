<?php

namespace Railroad\ActionLog\Listeners\Subscriptions;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Entities\Payment;
use Railroad\Ecommerce\Entities\Subscription;
use Railroad\Ecommerce\Events\Subscriptions\SubscriptionRenewFailed;
use Railroad\Ecommerce\Services\RenewalService;

class SubscriptionRenewFailedListener
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
     * @param SubscriptionRenewFailed $subscriptionRenewFailedEvent
     *
     * @throws Exception
     */
    public function handle(SubscriptionRenewFailed $subscriptionRenewFailedEvent)
    {
        /** @var $subscription Subscription */
        $subscription = $subscriptionRenewFailedEvent->getSubscription();

        /** @var $oldSubscriptionState Subscription */
        $oldSubscriptionState = $subscriptionRenewFailedEvent->getOldSubscription();

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

            $this->actionLogService->recordAction(
                $brand,
                Subscription::ACTION_DEACTIVATED,
                $subscription,
                $actor,
                $actorId,
                $actorRole
            );
        }

        $payment = $subscriptionRenewFailedEvent->getPayment();

        if ($payment) {

            /** @var $payment Payment */

            $this->actionLogService->recordAction(
                $brand,
                Payment::ACTION_FAILED_RENEW,
                $payment,
                $actor,
                $actorId,
                $actorRole
            );
        }
    }
}
