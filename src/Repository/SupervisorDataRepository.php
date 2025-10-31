<?php

namespace Tourze\TrainSupervisorBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\TrainSupervisorBundle\Entity\SupervisorData;

/**
 * @extends ServiceEntityRepository<SupervisorData>
 */
#[AsRepository(entityClass: SupervisorData::class)]
class SupervisorDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SupervisorData::class);
    }

    /**
     * 根据日期范围查询监督数据.
     *
     * @return array<int, SupervisorData>
     */
    public function findByDateRange(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        ?string $supplierId = null,
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->where('s.date >= :startDate')
            ->andWhere('s.date <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('s.date', 'ASC')
        ;

        if (null !== $supplierId) {
            $qb->andWhere('s.supplierId = :supplierId')
                ->setParameter('supplierId', $supplierId)
            ;
        }

        /** @var array<int, SupervisorData> */
        return $qb->getQuery()->getResult();
    }

    public function save(SupervisorData $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SupervisorData $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
