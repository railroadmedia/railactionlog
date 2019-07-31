<?php

namespace Railroad\ActionLog\Listeners\Subscriptions;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Entities\Subscription;
use Railroad\Ecommerce\Events\Subscriptions\SubscriptionCreated;

class SubscriptionCreatedListener
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
     * @param SubscriptionCreated $subscriptionCreatedEvent
     *
     * @throws Exception
     */
    public function handle(SubscriptionCreated $subscriptionCreatedEvent)
    {
        /** @var $currentUser array */
        $currentUser = auth()->user();

        /** @var $subscription Subscription */
        $subscription = $subscriptionCreatedEvent->getSubscription();

        $brand = $subscription->getBrand();
        $actor = $currentUser['email'];
        $actorId = $currentUser['id'];
        $actorRole = $currentUser['id'] == $subscription->getUser()->getId() ?
                        ActionLogService::ROLE_USER:
                        ActionLogService::ROLE_ADMIN;

        $this->actionLogService->recordAction($brand, ActionLogService::ACTION_CREATE, $subscription, $actor, $actorId, $actorRole);
    }
}
