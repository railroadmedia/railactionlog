<?php

namespace Railroad\ActionLog\Listeners;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Entities\Payment;
use Railroad\Ecommerce\Entities\User;
use Railroad\Ecommerce\Events\PaymentEvent;

class PaymentEventListener
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
     * @param PaymentEvent $paymentEvent
     *
     * @throws Exception
     */
    public function handle(PaymentEvent $paymentEvent)
    {
        /** @var $currentUser array */
        $currentUser = auth()->user();

        /** @var $payment Payment */
        $payment = $paymentEvent->getPayment();

        /** @var $user User */
        $user = $paymentEvent->getUser();

        $actorRole = $currentUser['id'] == $user->getId() ?
                        ActionLogService::ROLE_USER:
                        ActionLogService::ROLE_ADMIN;

        $this->actionLogService->recordAction(
            $payment->getGatewayName(),
            ActionLogService::ACTION_CREATE,
            $payment,
            $currentUser['email'],
            $currentUser['id'],
            $actorRole
        );
    }
}
