<?php

namespace Railroad\ActionLog\Listeners;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Contracts\UserProviderInterface;
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
    ) {
        $this->actionLogService = $actionLogService;
        $this->userProvider = $userProvider;
    }

    /**
     * @param PaymentEvent $paymentEvent
     *
     * @throws Exception
     */
    public function handle(PaymentEvent $paymentEvent)
    {
        try {

            /** @var $currentUser User */
            $currentUser = $this->userProvider->getCurrentUser();

            if (empty($currentUser)) {
                return;
            }

            /** @var $payment Payment */
            $payment = $paymentEvent->getPayment();

            /** @var $user User */
            $user = $paymentEvent->getUser();

            $actorRole = $currentUser->getId() == $user->getId() ?
                ActionLogService::ROLE_USER :
                ActionLogService::ROLE_ADMIN;

            $this->actionLogService->recordAction(
                $payment->getGatewayName(),
                ActionLogService::ACTION_CREATE,
                $payment,
                $currentUser->getEmail(),
                $currentUser->getId(),
                $actorRole
            );

        } catch (\Throwable $throwable) {
            error_log('Railactionlog ERROR --------------------');
            error_log($throwable);
        }
    }
}
