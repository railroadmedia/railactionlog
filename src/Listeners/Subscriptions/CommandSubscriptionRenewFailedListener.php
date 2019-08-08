<?php

namespace Railroad\ActionLog\Listeners\Subscriptions;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Contracts\UserProviderInterface;
use Railroad\Ecommerce\Entities\Payment;
use Railroad\Ecommerce\Entities\Subscription;
use Railroad\Ecommerce\Entities\User;
use Railroad\Ecommerce\Events\Subscriptions\CommandSubscriptionRenewFailed;
use Railroad\Ecommerce\Services\RenewalService;

class CommandSubscriptionRenewFailedListener
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
