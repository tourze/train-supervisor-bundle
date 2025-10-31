<?php

namespace Tourze\TrainSupervisorBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;

/**
 * 监督计划仓储类.
 *
 * @extends ServiceEntityRepository<SupervisionPlan>
 */
#[AsRepository(entityClass: SupervisionPlan::class)]
class SupervisionPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SupervisionPlan::class);
    }

    /**
     * 查找活跃的监督计划.
     *
     * @return array<int, SupervisionPlan>
     */
    public function findActivePlans(): array
    {
        /** @var array<int, SupervisionPlan> */
        return $this->createQueryBuilder('sp')
            ->where('sp.planStatus IN (:statuses)')
            ->setParameter('statuses', ['待执行', '执行中', '激活'])
            ->orderBy('sp.planStartDate', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找指定日期范围内的监督计划.
     *
     * @return array<int, SupervisionPlan>
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        /** @var array<int, SupervisionPlan> */
        return $this->createQueryBuilder('sp')
            ->where('sp.planStartDate <= :endDate')
            ->andWhere('sp.planEndDate >= :startDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('sp.planStartDate', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找过期的监督计划.
     *
     * @return array<int, SupervisionPlan>
     */
    public function findExpiredPlans(): array
    {
        /** @var array<int, SupervisionPlan> */
        return $this->createQueryBuilder('sp')
            ->where('sp.planEndDate < :now')
            ->andWhere('sp.planStatus NOT IN (:completedStatuses)')
            ->setParameter('now', new \DateTime())
            ->setParameter('completedStatuses', ['已完成', '已取消'])
            ->orderBy('sp.planEndDate', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 按计划类型统计
     *
     * @return array<int, array<string, mixed>>
     */
    public function countByType(): array
    {
        /** @var array<int, array<string, mixed>> */
        return $this->createQueryBuilder('sp')
            ->select('sp.planType, COUNT(sp.id) as count')
            ->groupBy('sp.planType')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找指定日期需要执行的监督计划.
     *
     * @return array<int, SupervisionPlan>
     */
    public function findPlansToExecuteOnDate(\DateTimeInterface $date): array
    {
        /** @var array<int, SupervisionPlan> */
        return $this->createQueryBuilder('sp')
            ->where('sp.planStartDate <= :date')
            ->andWhere('sp.planEndDate >= :date')
            ->andWhere('sp.planStatus = :status')
            ->setParameter('date', $date)
            ->setParameter('status', '执行中')
            ->orderBy('sp.planStartDate', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 按计划类型查找.
     *
     * @return array<int, SupervisionPlan>
     */
    public function findByType(string $type): array
    {
        /** @var array<int, SupervisionPlan> */
        return $this->createQueryBuilder('sp')
            ->where('sp.planType = :type')
            ->setParameter('type', $type)
            ->orderBy('sp.planStartDate', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找已完成的计划.
     *
     * @return array<int, SupervisionPlan>
     */
    public function findCompletedPlans(): array
    {
        /** @var array<int, SupervisionPlan> */
        return $this->createQueryBuilder('sp')
            ->where('sp.planStatus = :status')
            ->setParameter('status', '已完成')
            ->orderBy('sp.planStartDate', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找未完成的计划.
     *
     * @return array<int, SupervisionPlan>
     */
    public function findIncompletePlans(): array
    {
        /** @var array<int, SupervisionPlan> */
        return $this->createQueryBuilder('sp')
            ->where('sp.planStatus NOT IN (:completedStatuses)')
            ->setParameter('completedStatuses', ['已完成', '已取消'])
            ->orderBy('sp.planStartDate', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(SupervisionPlan $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SupervisionPlan $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
