<?php

namespace Railroad\ActionLog\Payments\Listeners;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Entities\Payment;
use Railroad\Ecommerce\Entities\User;
use Railroad\Ecommerce\Events\Payments\SubscriptionPaymentFailed;
use Railroad\Ecommerce\Services\RenewalService;

class SubscriptionPaymentFailedListener
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
     * @param SubscriptionPaymentFailed $subscriptionPaymentFailedEvent
     * @throws Exception
     */
    public function handle(SubscriptionPaymentFailed $subscriptionPaymentFailedEvent)
    {
        /** @var $payment Payment */
        $payment = $subscriptionPaymentFailedEvent->getPayment();

        /** @var $paymentUser User */
        $paymentUser = $subscriptionPaymentFailedEvent->getPaymentUser();

        /** @var $currentUser array */
        $currentUser = auth()->user();

        $brand = $payment->getGatewayName();
        $actor = $currentUser['email'];
        $actorId = $currentUser['id'];
        $actorRole = $currentUser['id'] == $paymentUser->getId() ?
                        ActionLogService::ROLE_USER:
                        ActionLogService::ROLE_ADMIN;

        $this->actionLogService->recordAction($brand, Payment::ACTION_FAILED_RENEW, $payment, $actor, $actorId, $actorRole);
    }
}
