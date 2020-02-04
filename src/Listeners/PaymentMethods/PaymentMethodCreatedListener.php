<?php

namespace Railroad\ActionLog\Listeners\PaymentMethods;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Contracts\IdentifiableInterface;
use Railroad\Ecommerce\Contracts\UserProviderInterface;
use Railroad\Ecommerce\Entities\PaymentMethod;
use Railroad\Ecommerce\Entities\User;
use Railroad\Ecommerce\Events\PaymentMethods\PaymentMethodCreated;

class PaymentMethodCreatedListener
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
     * @param PaymentMethodCreated $paymentMethodCreated
     *
     * @throws Exception
     */
    public function handle(PaymentMethodCreated $paymentMethodCreated)
    {
        try {

            /** @var $currentUser User */
            $currentUser = $this->userProvider->getCurrentUser();

            if (empty($currentUser)) {
                return;
            }

            /** @var $paymentMethod PaymentMethod */
            $paymentMethod = $paymentMethodCreated->getPaymentMethod();

            /** @var $user IdentifiableInterface */
            $user = $paymentMethodCreated->getUser();

            $brand = $paymentMethod->getBillingAddress()->getBrand();
            $actor = $currentUser->getEmail();
            $actorId = $currentUser->getId();
            $actorRole = $currentUser->getId() == $user->getId() ?
                ActionLogService::ROLE_USER :
                ActionLogService::ROLE_ADMIN;

            $this->actionLogService->recordAction(
                $brand,
                ActionLogService::ACTION_CREATE,
                $paymentMethod,
                $actor,
                $actorId,
                $actorRole
            );

        } catch (\Throwable $throwable) {
            error_log('Railactionlog ERROR --------------------');
            error_log($throwable);
        }
    }
}
