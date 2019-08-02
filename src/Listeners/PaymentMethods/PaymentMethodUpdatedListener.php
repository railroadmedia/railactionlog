<?php

namespace Railroad\ActionLog\Listeners\PaymentMethods;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Entities\PaymentMethod;
use Railroad\Ecommerce\Contracts\IdentifiableInterface;
use Railroad\Ecommerce\Events\PaymentMethods\PaymentMethodUpdated;

class PaymentMethodUpdatedListener
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
     * @param PaymentMethodUpdated $paymentMethodUpdated
     *
     * @throws Exception
     */
    public function handle(PaymentMethodUpdated $paymentMethodUpdated)
    {
        /** @var $currentUser array */
        $currentUser = auth()->user();

        /** @var $paymentMethod PaymentMethod */
        $paymentMethod = $paymentMethodUpdated->getNewPaymentMethod();

        /** @var $user IdentifiableInterface */
        $user = $paymentMethodUpdated->getUser();

        $brand = $paymentMethod->getBillingAddress()->getBrand();
        $actor = $currentUser['email'];
        $actorId = $currentUser['id'];
        $actorRole = $currentUser['id'] == $user->getId() ?
                        ActionLogService::ROLE_USER:
                        ActionLogService::ROLE_ADMIN;

        $this->actionLogService->recordAction(
            $brand,
            ActionLogService::ACTION_UPDATE,
            $paymentMethod,
            $actor,
            $actorId,
            $actorRole
        );
    }
}
