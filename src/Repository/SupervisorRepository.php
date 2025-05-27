<?php

namespace Tourze\TrainSupervisorBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DoctrineEnhanceBundle\Repository\CommonRepositoryAware;
use SenboTrainingBundle\Entity\Supervisor;

/**
 * @method Supervisor|null find($id, $lockMode = null, $lockVersion = null)
 * @method Supervisor|null findOneBy(array $criteria, array $orderBy = null)
 * @method Supervisor[]    findAll()
 * @method Supervisor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupervisorRepository extends ServiceEntityRepository
{
    use CommonRepositoryAware;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Supervisor::class);
    }
}
