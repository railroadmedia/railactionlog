<?php

namespace Railroad\ActionLog\Listeners\Subscriptions;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Entities\Payment;
use Railroad\Ecommerce\Entities\Subscription;
use Railroad\Ecommerce\Events\Subscriptions\UserSubscriptionRenewed;

class UserSubscriptionRenewedListener
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
     * @param UserSubscriptionRenewed $userSubscriptionRenewed
     *
     * @throws Exception
     */
    public function handle(UserSubscriptionRenewed $userSubscriptionRenewed)
    {
        /** @var $currentUser array */
        $currentUser = auth()->user();

        /** @var $subscription Subscription */
        $subscription = $userSubscriptionRenewed->getSubscription();

        $brand = $subscription->getBrand();
        $actor = $currentUser['email'];
        $actorId = $currentUser['id'];
        $actorRole = $currentUser['id'] == $subscription->getUser()->getId() ?
                        ActionLogService::ROLE_USER:
                        ActionLogService::ROLE_ADMIN;

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