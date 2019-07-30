<?php

namespace Railroad\ActionLog\Listeners\Subscriptions;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Entities\Subscription;
use Railroad\Ecommerce\Events\Subscriptions\SubscriptionUpdated;

class SubscriptionUpdatedListener
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
     * @param SubscriptionUpdated $subscriptionUpdatedEvent
     *
     * @throws Exception
     */
    public function handle(SubscriptionUpdated $subscriptionUpdatedEvent)
    {
        /** @var $currentUser array */
        $currentUser = auth()->user();

        /** @var $subscription Subscription */
        $subscription = $subscriptionUpdatedEvent->getNewSubscription();

        $brand = $subscription->getBrand();
        $actor = $currentUser['email'];
        $actorId = $currentUser['id'];
        $actorRole = $currentUser['id'] == $subscription->getUser()->getId() ?
                        ActionLogService::ROLE_USER:
                        ActionLogService::ROLE_ADMIN;

        $this->actionLogService->recordAction($brand, ActionLogService::ACTION_UPDATE, $subscription, $actor, $actorId, $actorRole);
    }
}
