<?php

namespace Tourze\TrainSupervisorBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainSupervisorBundle\Entity\ProblemTracking;

/**
 * 问题跟踪仓储类
 * 
 * @method ProblemTracking|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProblemTracking|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProblemTracking[]    findAll()
 * @method ProblemTracking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProblemTrackingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProblemTracking::class);
    }

    /**
     * 查找过期的问题
     */
    public function findOverdueProblems(): array
    {
        return $this->createQueryBuilder('pt')
            ->where('pt.correctionDeadline < :now')
            ->andWhere('pt.correctionStatus NOT IN (:completedStatuses)')
            ->setParameter('now', new \DateTime())
            ->setParameter('completedStatuses', ['已整改', '已验证', '已关闭'])
            ->orderBy('pt.correctionDeadline', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找待整改的问题
     */
    public function findPendingProblems(): array
    {
        return $this->createQueryBuilder('pt')
            ->where('pt.correctionStatus = :status')
            ->setParameter('status', '待整改')
            ->orderBy('pt.correctionDeadline', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 按问题类型统计
     */
    public function countByType(): array
    {
        return $this->createQueryBuilder('pt')
            ->select('pt.problemType, COUNT(pt.id) as count')
            ->groupBy('pt.problemType')
            ->getQuery()
            ->getResult();
    }

    /**
     * 按严重程度统计
     */
    public function countBySeverity(): array
    {
        return $this->createQueryBuilder('pt')
            ->select('pt.problemSeverity, COUNT(pt.id) as count')
            ->groupBy('pt.problemSeverity')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定检查的问题
     */
    public function findByInspection(string $inspectionId): array
    {
        return $this->createQueryBuilder('pt')
            ->where('pt.inspection = :inspectionId')
            ->setParameter('inspectionId', $inspectionId)
            ->orderBy('pt.createTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找已验证通过的问题
     */
    public function findVerifiedProblems(): array
    {
        return $this->createQueryBuilder('pt')
            ->where('pt.verificationResult = :result')
            ->setParameter('result', '通过')
            ->orderBy('pt.verificationDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定责任人的问题
     */
    public function findByResponsiblePerson(string $person): array
    {
        return $this->createQueryBuilder('pt')
            ->where('pt.responsiblePerson = :person')
            ->setParameter('person', $person)
            ->orderBy('pt.correctionDeadline', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 统计整改完成率
     */
    public function getCorrectionRate(): float
    {
        $total = $this->count([]);
        if ($total === 0) {
            return 0.0;
        }

        $corrected = $this->createQueryBuilder('pt')
            ->select('COUNT(pt.id)')
            ->where('pt.correctionStatus IN (:statuses)')
            ->setParameter('statuses', ['已整改', '已验证', '已关闭'])
            ->getQuery()
            ->getSingleScalarResult();

        return ($corrected / $total) * 100;
    }

    /**
     * 按日期范围查找问题
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('pt')
            ->where('pt.discoveryDate >= :startDate')
            ->andWhere('pt.discoveryDate <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('pt.discoveryDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
} 