<?php

namespace Railroad\ActionLog\Listeners\PaymentMethods;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Entities\PaymentMethod;
use Railroad\Ecommerce\Contracts\IdentifiableInterface;
use Railroad\Ecommerce\Events\PaymentMethods\PaymentMethodCreated;

class PaymentMethodCreatedListener
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
     * @param PaymentMethodCreated $paymentMethodCreated
     *
     * @throws Exception
     */
    public function handle(PaymentMethodCreated $paymentMethodCreated)
    {
        /** @var $currentUser array */
        $currentUser = auth()->user();

        /** @var $paymentMethod PaymentMethod */
        $paymentMethod = $paymentMethodCreated->getPaymentMethod();

        /** @var $user IdentifiableInterface */
        $user = $paymentMethodCreated->getUser();

        $brand = $paymentMethod->getBillingAddress()->getBrand();
        $actor = $currentUser['email'];
        $actorId = $currentUser['id'];
        $actorRole = $currentUser['id'] == $user->getId() ?
                        ActionLogService::ROLE_USER:
                        ActionLogService::ROLE_ADMIN;

        $this->actionLogService->recordAction(
            $brand,
            ActionLogService::ACTION_CREATE,
            $paymentMethod,
            $actor,
            $actorId,
            $actorRole
        );
    }
}
