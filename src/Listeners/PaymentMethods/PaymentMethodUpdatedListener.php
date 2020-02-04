<?php

namespace Railroad\ActionLog\Listeners\PaymentMethods;

use Exception;
use Railroad\ActionLog\Services\ActionLogService;
use Railroad\Ecommerce\Contracts\UserProviderInterface;
use Railroad\Ecommerce\Entities\PaymentMethod;
use Railroad\Ecommerce\Entities\User;
use Railroad\Ecommerce\Contracts\IdentifiableInterface;
use Railroad\Ecommerce\Events\PaymentMethods\PaymentMethodUpdated;

class PaymentMethodUpdatedListener
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
     * @param PaymentMethodUpdated $paymentMethodUpdated
     *
     * @throws Exception
     */
    public function handle(PaymentMethodUpdated $paymentMethodUpdated)
    {
        try {

            /** @var $currentUser User */
            $currentUser = $this->userProvider->getCurrentUser();

            if (empty($currentUser)) {
                return;
            }

            /** @var $paymentMethod PaymentMethod */
            $paymentMethod = $paymentMethodUpdated->getNewPaymentMethod();

            /** @var $user IdentifiableInterface */
            $user = $paymentMethodUpdated->getUser();

            $brand = $paymentMethod->getBillingAddress()->getBrand();
            $actor = $currentUser->getEmail();
            $actorId = $currentUser->getId();
            $actorRole = $currentUser->getId() == $user->getId() ?
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

        } catch (\Throwable $throwable) {
            error_log('Railactionlog ERROR --------------------');
            error_log($throwable);
        }
    }
}
