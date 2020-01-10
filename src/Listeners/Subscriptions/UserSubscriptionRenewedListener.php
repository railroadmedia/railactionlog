<?php

namespace Railroad\ActionLog\Listeners\Subscriptions;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Contracts\UserProviderInterface;
use Railroad\Ecommerce\Entities\Payment;
use Railroad\Ecommerce\Entities\Subscription;
use Railroad\Ecommerce\Entities\User;
use Railroad\Ecommerce\Events\Subscriptions\UserSubscriptionRenewed;

class UserSubscriptionRenewedListener
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
     * @param UserSubscriptionRenewed $userSubscriptionRenewed
     *
     * @throws Exception
     */
    public function handle(UserSubscriptionRenewed $userSubscriptionRenewed)
    {
        /** @var $currentUser User */
        $currentUser = $this->userProvider->getCurrentUser();

        /** @var $subscription Subscription */
        $subscription = $userSubscriptionRenewed->getSubscription();

        $brand = $subscription->getBrand();
        $actor = $currentUser->getEmail();
        $actorId = $currentUser->getId();

        if (!empty($subscription->getUser())) {
            $actorRole = $currentUser->getId() == $subscription->getUser()->getId() ?
                ActionLogService::ROLE_USER:
                ActionLogService::ROLE_ADMIN;
        } else {
            $actorRole = ActionLogService::ROLE_ADMIN;
        }

        $this->actionLogService->recordAction($brand, Subscription::ACTION_RENEW, $subscription, $actor, $actorId, $actorRole);

        $payment = $userSubscriptionRenewed->getPayment();

        if ($payment) {

            /** @var $payment Payment */

            $this->actionLogService->recordAction(
                $brand,
                ActionLogService::ACTION_CREATE,
                $payment,
                $actor,
                $actorId,
                $actorRole
            );
        }
    }
}
