<?php

namespace Railroad\ActionLog\Listeners;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Entities\Order;
use Railroad\Ecommerce\Events\UpdateOrderEvent;

class UpdateOrderEventListener
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
     * @param UpdateOrderEvent $updateOrderEvent
     *
     * @throws Exception
     */
    public function handle(UpdateOrderEvent $updateOrderEvent)
    {
        /** @var $currentUser array */
        $currentUser = auth()->user();

        /** @var $order Order */
        $order = $updateOrderEvent->getOrder();

        $actorRole = $currentUser['id'] == $order->getUser()->getId() ?
                        ActionLogService::ROLE_USER:
                        ActionLogService::ROLE_ADMIN;

        $this->actionLogService->recordAction(
            $order->getBrand(),
            ActionLogService::ACTION_UPDATE,
            $order,
            $currentUser['email'],
            $currentUser['id'],
            $actorRole
        );
    }
}
