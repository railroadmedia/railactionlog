<?php

namespace Railroad\ActionLog\Listeners\Subscriptions;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Contracts\UserProviderInterface;
use Railroad\Ecommerce\Entities\Payment;
use Railroad\Ecommerce\Entities\Subscription;
use Railroad\Ecommerce\Entities\User;
use Railroad\Ecommerce\Events\Subscriptions\SubscriptionRenewFailed;
use Railroad\Ecommerce\Services\RenewalService;

class SubscriptionRenewFailedListener
{
    /**
     * @var ActionLogService
     */
    private $actionLogService;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @param ActionLogService $actionLogService
     * @param UserProviderInterface $userProvider
     */
    public function __construct(
        ActionLogService $actionLogService,
        UserProviderInterface $userProvider
    )
    {
        $this->actionLogService = $actionLogService;
        $this->userProvider = $userProvider;
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

        /** @var $currentUser User */
        $currentUser = $this->userProvider->getCurrentUser();

        $brand = $subscription->getBrand();
        $actor = $currentUser->getEmail();
        $actorId = $currentUser->getId();
        $actorRole = $currentUser->getId() == $subscription->getUser()->getId() ?
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
