<?php

namespace Tourze\TrainSupervisorBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainSupervisorBundle\Entity\Supervisor;

/**
 * @method Supervisor|null find($id, $lockMode = null, $lockVersion = null)
 * @method Supervisor|null findOneBy(array $criteria, array $orderBy = null)
 * @method Supervisor[]    findAll()
 * @method Supervisor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupervisorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Supervisor::class);
    }

    /**
     * 查找供应商数据
     */
    public function findSupplierData(?string $supplierId, ?\DateTimeInterface $startDate = null, ?\DateTimeInterface $endDate = null): array
    {
        $qb = $this->createQueryBuilder('s');

        if ($supplierId !== null) {
            $qb->where('s.supplierId = :supplierId')
                ->setParameter('supplierId', $supplierId);
        }

        if ($startDate !== null) {
            $qb->andWhere('s.date >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        if ($endDate !== null) {
            $qb->andWhere('s.date <= :endDate')
                ->setParameter('endDate', $endDate);
        }

        return $qb->orderBy('s.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 按日期范围查找
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate, ?string $supplierId = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.date >= :startDate')
            ->andWhere('s.date <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        if ($supplierId !== null) {
            $qb->andWhere('s.supplierId = :supplierId')
                ->setParameter('supplierId', $supplierId);
        }

        return $qb->orderBy('s.date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
