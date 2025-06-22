<?php

namespace Tourze\TrainSupervisorBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainSupervisorBundle\Entity\QualityAssessment;

/**
 * 质量评估仓储类
 * 
 * @method QualityAssessment|null find($id, $lockMode = null, $lockVersion = null)
 * @method QualityAssessment|null findOneBy(array $criteria, array $orderBy = null)
 * @method QualityAssessment[]    findAll()
 * @method QualityAssessment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QualityAssessmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QualityAssessment::class);
    }

    /**
     * 查找已完成的评估
     */
    public function findCompletedAssessments(): array
    {
        return $this->createQueryBuilder('qa')
            ->where('qa.assessmentStatus = :status')
            ->setParameter('status', '已完成')
            ->orderBy('qa.assessmentDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 按评估类型查找
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('qa')
            ->where('qa.assessmentType = :type')
            ->setParameter('type', $type)
            ->orderBy('qa.assessmentDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定对象的评估记录
     */
    public function findByTarget(string $targetId): array
    {
        return $this->createQueryBuilder('qa')
            ->where('qa.targetId = :targetId')
            ->setParameter('targetId', $targetId)
            ->orderBy('qa.assessmentDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 按评估等级统计
     */
    public function countByLevel(): array
    {
        return $this->createQueryBuilder('qa')
            ->select('qa.assessmentLevel, COUNT(qa.id) as count')
            ->groupBy('qa.assessmentLevel')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找不合格的评估
     */
    public function findFailedAssessments(): array
    {
        return $this->createQueryBuilder('qa')
            ->where('qa.assessmentLevel = :level')
            ->setParameter('level', '不合格')
            ->orderBy('qa.assessmentDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 计算平均分
     */
    public function getAverageScore(): float
    {
        $result = $this->createQueryBuilder('qa')
            ->select('AVG(qa.totalScore) as avgScore')
            ->where('qa.assessmentStatus = :status')
            ->setParameter('status', '已完成')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }

    /**
     * 按日期范围查找评估记录
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('qa')
            ->where('qa.assessmentDate >= :startDate')
            ->andWhere('qa.assessmentDate <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('qa.assessmentDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
} 