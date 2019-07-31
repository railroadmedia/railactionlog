<?php

namespace Railroad\ActionLog\Listeners;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Entities\Order;
use Railroad\Ecommerce\Entities\Payment;
use Railroad\Ecommerce\Events\OrderEvent;

class OrderEventListener
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
     * @param OrderEvent $orderEvent
     *
     * @throws Exception
     */
    public function handle(OrderEvent $orderEvent)
    {
        /** @var $currentUser array */
        $currentUser = auth()->user();

        /** @var $order Order */
        $order = $orderEvent->getOrder();

        $actionName = ActionLogService::ACTION_CREATE;
        $brand = $order->getBrand();
        $actor = $actorId = $actorRole = null;

        if (empty($currentUser)) {
            $customer = $order->getCustomer();

            $actor = $customer->getEmail();
            $actorId = $customer->getId();
            $actorRole = ActionLogService::ROLE_CUSTOMER;
        } else {
            $actor = $currentUser['email'];
            $actorId = $currentUser['id'];

            $actorRole = $currentUser['id'] == $order->getUser()->getId() ?
                            ActionLogService::ROLE_USER:
                            ActionLogService::ROLE_ADMIN;
        }

        $this->actionLogService->recordAction($brand, $actionName, $order, $actor, $actorId, $actorRole);

        
        $payment = $orderEvent->getPayment();

        if ($payment) {
            /** @var $payment Payment */
            $this->actionLogService->recordAction($brand, $actionName, $payment, $actor, $actorId, $actorRole);

            $paymentMethod = $payment->getPaymentMethod();
            $this->actionLogService->recordAction($brand, $actionName, $paymentMethod, $actor, $actorId, $actorRole);
        }
    }
}
