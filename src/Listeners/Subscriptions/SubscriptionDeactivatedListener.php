<?php

namespace Railroad\ActionLog\Listeners\Subscriptions;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Entities\Subscription;
use Railroad\Ecommerce\Events\Subscriptions\SubscriptionDeactivated;
use Railroad\Ecommerce\Services\RenewalService;

class SubscriptionDeactivatedListener
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
     * @param SubscriptionDeactivated $subscriptionDeactivatedEvent
     *
     * @throws Exception
     */
    public function handle(SubscriptionDeactivated $subscriptionDeactivatedEvent)
    {
        /** @var $subscription Subscription */
        $subscription = $subscriptionDeactivatedEvent->getSubscription();

        /** @var $currentUser array */
        $currentUser = auth()->user();

        $brand = $subscription->getBrand();
        $actor = $currentUser['email'];
        $actorId = $currentUser['id'];
        $actorRole = $currentUser['id'] == $subscription->getUser()->getId() ?
                        ActionLogService::ROLE_USER:
                        ActionLogService::ROLE_ADMIN;

        $this->actionLogService->recordAction(
            $brand,
            Subscription::ACTION_DEACTIVATED,
            $subscription,
            $actor,
            $actorId,
            $actorRole
        );
    }
}
