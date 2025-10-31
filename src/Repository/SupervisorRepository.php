<?php

namespace Tourze\TrainSupervisorBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\TrainSupervisorBundle\Entity\Supervisor;

/**
 * @extends ServiceEntityRepository<Supervisor>
 */
#[AsRepository(entityClass: Supervisor::class)]
class SupervisorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Supervisor::class);
    }

    /**
     * 查找供应商数据.
     *
     * @return array<int, Supervisor>
     */
    public function findSupplierData(?string $supplierId, ?\DateTimeInterface $startDate = null, ?\DateTimeInterface $endDate = null): array
    {
        $qb = $this->createQueryBuilder('s');

        if (null !== $supplierId) {
            $qb->where('s.supplierId = :supplierId')
                ->setParameter('supplierId', $supplierId)
            ;
        }

        if (null !== $startDate) {
            $qb->andWhere('s.date >= :startDate')
                ->setParameter('startDate', $startDate)
            ;
        }

        if (null !== $endDate) {
            $qb->andWhere('s.date <= :endDate')
                ->setParameter('endDate', $endDate)
            ;
        }

        /** @var array<int, Supervisor> */
        return $qb->orderBy('s.date', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 按日期范围查找.
     *
     * @return array<int, Supervisor>
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate, ?string $supplierId = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.date >= :startDate')
            ->andWhere('s.date <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
        ;

        if (null !== $supplierId) {
            $qb->andWhere('s.supplierId = :supplierId')
                ->setParameter('supplierId', $supplierId)
            ;
        }

        /** @var array<int, Supervisor> */
        return $qb->orderBy('s.date', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(Supervisor $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Supervisor $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
