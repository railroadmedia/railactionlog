<?php

namespace Railroad\ActionLog\Listeners;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Contracts\UserProviderInterface;
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
     * @param RefundEvent $refundEvent
     *
     * @throws Exception
     */
    public function handle(RefundEvent $refundEvent)
    {
        /** @var $currentUser User */
        $currentUser = $this->userProvider->getCurrentUser();

        /** @var $refund Refund */
        $refund = $refundEvent->getRefund();

        /** @var $user User */
        $user = $refundEvent->getUser();

        $actorRole = $currentUser->getId() == $user->getId() ?
                        ActionLogService::ROLE_USER:
                        ActionLogService::ROLE_ADMIN;

        $this->actionLogService->recordAction(
            $refund->getPayment()->getGatewayName(),
            ActionLogService::ACTION_CREATE,
            $refund,
            $currentUser->getEmail(),
            $currentUser->getId(),
            $actorRole
        );
    }
}
