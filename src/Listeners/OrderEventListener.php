<?php

namespace Railroad\ActionLog\Listeners;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Contracts\UserProviderInterface;
use Railroad\Ecommerce\Entities\Order;
use Railroad\Ecommerce\Entities\Payment;
use Railroad\Ecommerce\Entities\User;
use Railroad\Ecommerce\Events\OrderEvent;

class OrderEventListener
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
     * @param OrderEvent $orderEvent
     *
     * @throws Exception
     */
    public function handle(OrderEvent $orderEvent)
    {
        /** @var $currentUser User */
        $currentUser = $this->userProvider->getCurrentUser();

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
            $actor = $currentUser->getEmail();
            $actorId = $currentUser->getId();

            if (!empty($order->getUser())) {
                $actorRole = $currentUser->getId() == $order->getUser()->getId() ?
                    ActionLogService::ROLE_USER:
                    ActionLogService::ROLE_ADMIN;
            } else {
                $actorRole = ActionLogService::ROLE_ADMIN;
            }
        }

        $this->actionLogService->recordAction($brand, $actionName, $order, $actor, $actorId, $actorRole);

        
        $payment = $orderEvent->getPayment();

        if ($payment) {
            /** @var $payment Payment */
            $this->actionLogService->recordAction($brand, $actionName, $payment, $actor, $actorId, $actorRole);
        }
    }
}
