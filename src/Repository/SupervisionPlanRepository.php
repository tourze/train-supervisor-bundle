<?php

namespace Tourze\TrainSupervisorBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;

/**
 * 监督计划仓储类
 * 
 * @method SupervisionPlan|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupervisionPlan|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupervisionPlan[]    findAll()
 * @method SupervisionPlan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupervisionPlanRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SupervisionPlan::class);
    }

    /**
     * 查找活跃的监督计划
     */
    public function findActivePlans(): array
    {
        return $this->createQueryBuilder('sp')
            ->where('sp.planStatus IN (:statuses)')
            ->setParameter('statuses', ['待执行', '执行中'])
            ->orderBy('sp.planStartDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定日期范围内的监督计划
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('sp')
            ->where('sp.planStartDate <= :endDate')
            ->andWhere('sp.planEndDate >= :startDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('sp.planStartDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找过期的监督计划
     */
    public function findExpiredPlans(): array
    {
        return $this->createQueryBuilder('sp')
            ->where('sp.planEndDate < :now')
            ->andWhere('sp.planStatus NOT IN (:completedStatuses)')
            ->setParameter('now', new \DateTime())
            ->setParameter('completedStatuses', ['已完成', '已取消'])
            ->orderBy('sp.planEndDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 按计划类型统计
     */
    public function countByType(): array
    {
        return $this->createQueryBuilder('sp')
            ->select('sp.planType, COUNT(sp.id) as count')
            ->groupBy('sp.planType')
            ->getQuery()
            ->getResult();
    }
} 