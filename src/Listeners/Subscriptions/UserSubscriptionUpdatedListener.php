<?php

namespace Railroad\ActionLog\Listeners\Subscriptions;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Contracts\UserProviderInterface;
use Railroad\Ecommerce\Entities\Subscription;
use Railroad\Ecommerce\Entities\User;
use Railroad\Ecommerce\Events\Subscriptions\UserSubscriptionUpdated;

class UserSubscriptionUpdatedListener
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
     * @param UserSubscriptionUpdated $userSubscriptionUpdated
     *
     * @throws Exception
     */
    public function handle(UserSubscriptionUpdated $userSubscriptionUpdated)
    {
        /** @var $currentUser User */
        $currentUser = $this->userProvider->getCurrentUser();

        /** @var $subscription Subscription */
        $subscription = $userSubscriptionUpdated->getNewSubscription();

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

        $this->actionLogService->recordAction($brand, ActionLogService::ACTION_UPDATE, $subscription, $actor, $actorId, $actorRole);
    }
}
