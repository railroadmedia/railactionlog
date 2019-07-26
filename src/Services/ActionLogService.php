<?php

namespace Railroad\ActionLog\Services;

use Carbon\Carbon;
use Railroad\ActionLog\Entities\ActionLog;
use Railroad\ActionLog\Managers\ActionLogEntityManager;

class ActionLogService
{
    const ACTOR_SYSTEM = 'system';
    const ACTOR_COMMAND = 'command';

    const ROLE_CUSTOMER = 'customer';
    const ROLE_USER = 'user';
    const ROLE_ADMIN = 'administrator';
    const ROLE_COMMAND = 'command';
    const ROLE_SYSTEM = 'system';

    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    /**
     * @var ActionLogEntityManager
     */
    private $entityManager;

    /**
     * ActionLogService constructor.
     */
    public function __construct(
        ActionLogEntityManager $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $brand
     * @param string $actionName
     * @param object $resource
     * @param string $actor
     * @param int $actorId
     * @param string $actorRole
     *
     * @throws Throwable
     */
    public function recordAction(
        string $brand,
        string $actionName,
        $resource,
        string $actor,
        int $actorId,
        string $actorRole
    )
    {
        $actionLog = $this->createActionLogEntity();

        $actionLog->setBrand($brand);
        $actionLog->setResourceName(get_class($resource));
        $actionLog->setResourceId($resource->getId());
        $actionLog->setActionName($actionName);
        $actionLog->setActor($actor);
        $actionLog->setActorId($actorId);
        $actionLog->setActorRole($actorRole);

        $this->saveActionLogEntity($actionLog);
    }

    /**
     * @param string $brand
     * @param string $actionName
     * @param object $resource
     *
     * @throws Throwable
     */
    public function recordSystemAction(
        string $brand,
        string $actionName,
        $resource
    )
    {
        $actionLog = $this->createActionLogEntity();

        $actionLog->setBrand($brand);
        $actionLog->setResourceName(get_class($resource));
        $actionLog->setResourceId($resource->getId());
        $actionLog->setActionName($actionName);
        $actionLog->setActor(self::ACTOR_SYSTEM);
        $actionLog->setActorRole(self::ROLE_SYSTEM);

        $this->saveActionLogEntity($actionLog);
    }

    /**
     * @param string $brand
     * @param string $actionName
     * @param object $resource
     *
     * @throws Throwable
     */
    public function recordCommandAction(
        string $brand,
        string $actionName,
        $resource
    )
    {
        $actionLog = $this->createActionLogEntity();

        $actionLog->setBrand($brand);
        $actionLog->setResourceName(get_class($resource));
        $actionLog->setResourceId($resource->getId());
        $actionLog->setActionName($actionName);
        $actionLog->setActor(self::ACTOR_COMMAND);
        $actionLog->setActorRole(self::ROLE_COMMAND);

        $this->saveActionLogEntity($actionLog);
    }

    protected function createActionLogEntity(): ActionLog
    {
        return new ActionLog();
    }

    protected function saveActionLogEntity(ActionLog $actionLog)
    {
        $this->entityManager->persist($actionLog);
        $this->entityManager->flush();
    }
}
