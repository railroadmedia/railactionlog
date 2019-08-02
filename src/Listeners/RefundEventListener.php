<?php

namespace Railroad\ActionLog\Listeners;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Entities\Refund;
use Railroad\Ecommerce\Entities\User;
use Railroad\Ecommerce\Events\RefundEvent;

class RefundEventListener
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
     * @param RefundEvent $refundEvent
     *
     * @throws Exception
     */
    public function handle(RefundEvent $refundEvent)
    {
        /** @var $currentUser array */
        $currentUser = auth()->user();

        /** @var $refund Refund */
        $refund = $refundEvent->getRefund();

        /** @var $user User */
        $user = $refundEvent->getUser();

        $actorRole = $currentUser['id'] == $user->getId() ?
                        ActionLogService::ROLE_USER:
                        ActionLogService::ROLE_ADMIN;

        $this->actionLogService->recordAction(
            $refund->getPayment()->getGatewayName(),
            ActionLogService::ACTION_CREATE,
            $refund,
            $currentUser['email'],
            $currentUser['id'],
            $actorRole
        );
    }
}
