<?php

namespace Railroad\ActionLog\Repositories;

use Doctrine\ORM\EntityRepository;
use Railroad\ActionLog\Entities\ActionLog;
use Railroad\ActionLog\Managers\ActionLogEntityManager;

/**
 * Class ActionLogRepository
 *
 * @method ActionLog find($id, $lockMode = null, $lockVersion = null)
 * @method ActionLog findOneBy(array $criteria, array $orderBy = null)
 * @method ActionLog[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method ActionLog[] findAll()
 *
 * @package Railroad\ActionLog\Repositories
 */
class ActionLogRepository extends EntityRepository
{
    /**
     * ActionLogRepository constructor.
     *
     * @param ActionLogEntityManager $em
     */
    public function __construct(ActionLogEntityManager $em)
    {
        parent::__construct(
            $em,
            $em->getClassMetadata(ActionLog::class)
        );
    }
}
