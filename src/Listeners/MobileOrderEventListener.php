<?php

namespace Railroad\ActionLog\Listeners;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Entities\Order;
use Railroad\Ecommerce\Entities\Payment;
use Railroad\Ecommerce\Entities\Subscription;
use Railroad\Ecommerce\Events\MobileOrderEvent;

class MobileOrderEventListener
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
     * @param MobileOrderEvent $mobileOrderEvent
     *
     * @throws Exception
     */
    public function handle(MobileOrderEvent $mobileOrderEvent)
    {
        /** @var $order Order */
        $order = $mobileOrderEvent->getOrder();

        /** @var $subscription Subscription */
        $subscription = $mobileOrderEvent->getSubscription();

        $actionName = ActionLogService::ACTION_CREATE;
        $brand = $order->getBrand();

        $this->actionLogService->recordSystemAction($brand, $actionName, $order);
        $this->actionLogService->recordSystemAction($brand, $actionName, $subscription);
        
        $payment = $mobileOrderEvent->getPayment();

        if ($payment) {
            /** @var $payment Payment */
            $this->actionLogService->recordSystemAction($brand, $actionName, $payment);
        }
    }
}
