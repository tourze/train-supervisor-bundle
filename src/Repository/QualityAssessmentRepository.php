<?php

namespace Tourze\TrainSupervisorBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\TrainSupervisorBundle\Entity\QualityAssessment;

/**
 * 质量评估仓储类.
 *
 * @extends ServiceEntityRepository<QualityAssessment>
 */
#[AsRepository(entityClass: QualityAssessment::class)]
class QualityAssessmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QualityAssessment::class);
    }

    /**
     * 查找已完成的评估.
     *
     * @return array<int, QualityAssessment>
     */
    public function findCompletedAssessments(): array
    {
        /** @var array<int, QualityAssessment> */
        return $this->createQueryBuilder('qa')
            ->where('qa.assessmentStatus = :status')
            ->setParameter('status', '已完成')
            ->orderBy('qa.assessmentDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 按评估类型查找.
     *
     * @return array<int, QualityAssessment>
     */
    public function findByType(string $type): array
    {
        /** @var array<int, QualityAssessment> */
        return $this->createQueryBuilder('qa')
            ->where('qa.assessmentType = :type')
            ->setParameter('type', $type)
            ->orderBy('qa.assessmentDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找指定对象的评估记录.
     *
     * @return array<int, QualityAssessment>
     */
    public function findByTarget(string $targetId): array
    {
        /** @var array<int, QualityAssessment> */
        return $this->createQueryBuilder('qa')
            ->where('qa.targetId = :targetId')
            ->setParameter('targetId', $targetId)
            ->orderBy('qa.assessmentDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 按评估等级统计
     *
     * @return array<int, array<string, mixed>>
     */
    public function countByLevel(): array
    {
        /** @var array<int, array<string, mixed>> */
        return $this->createQueryBuilder('qa')
            ->select('qa.assessmentLevel, COUNT(qa.id) as count')
            ->groupBy('qa.assessmentLevel')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找不合格的评估.
     *
     * @return array<int, QualityAssessment>
     */
    public function findFailedAssessments(): array
    {
        /** @var array<int, QualityAssessment> */
        return $this->createQueryBuilder('qa')
            ->where('qa.assessmentLevel = :level')
            ->setParameter('level', '不合格')
            ->orderBy('qa.assessmentDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 计算平均分.
     */
    public function getAverageScore(): float
    {
        $result = $this->createQueryBuilder('qa')
            ->select('AVG(qa.totalScore) as avgScore')
            ->where('qa.assessmentStatus = :status')
            ->setParameter('status', '已完成')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return null !== $result ? (float) $result : 0.0;
    }

    /**
     * 按日期范围查找评估记录.
     *
     * @return array<int, QualityAssessment>
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        /** @var array<int, QualityAssessment> */
        return $this->createQueryBuilder('qa')
            ->where('qa.assessmentDate >= :startDate')
            ->andWhere('qa.assessmentDate <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('qa.assessmentDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(QualityAssessment $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(QualityAssessment $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
