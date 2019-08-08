<?php

namespace Railroad\ActionLog\Listeners;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Contracts\UserProviderInterface;
use Railroad\Ecommerce\Entities\Order;
use Railroad\Ecommerce\Entities\User;
use Railroad\Ecommerce\Events\UpdateOrderEvent;

class UpdateOrderEventListener
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
     * @param UpdateOrderEvent $updateOrderEvent
     *
     * @throws Exception
     */
    public function handle(UpdateOrderEvent $updateOrderEvent)
    {
        /** @var $currentUser User */
        $currentUser = $this->userProvider->getCurrentUser();

        /** @var $order Order */
        $order = $updateOrderEvent->getOrder();

        $actorRole = $currentUser->getId() == $order->getUser()->getId() ?
                        ActionLogService::ROLE_USER:
                        ActionLogService::ROLE_ADMIN;

        $this->actionLogService->recordAction(
            $order->getBrand(),
            ActionLogService::ACTION_UPDATE,
            $order,
            $currentUser->getEmail(),
            $currentUser->getId(),
            $actorRole
        );
    }
}
