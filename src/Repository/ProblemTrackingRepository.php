<?php

namespace Tourze\TrainSupervisorBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\TrainSupervisorBundle\Entity\ProblemTracking;

/**
 * 问题跟踪仓储类.
 *
 * @extends ServiceEntityRepository<ProblemTracking>
 */
#[AsRepository(entityClass: ProblemTracking::class)]
class ProblemTrackingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProblemTracking::class);
    }

    /**
     * 查找过期的问题.
     *
     * @return array<int, ProblemTracking>
     */
    public function findOverdueProblems(): array
    {
        /** @var array<int, ProblemTracking> */
        return $this->createQueryBuilder('pt')
            ->where('pt.correctionDeadline < :now')
            ->andWhere('pt.correctionStatus NOT IN (:completedStatuses)')
            ->setParameter('now', new \DateTime())
            ->setParameter('completedStatuses', ['已整改', '已验证', '已关闭'])
            ->orderBy('pt.correctionDeadline', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找待整改的问题.
     *
     * @return array<int, ProblemTracking>
     */
    public function findPendingProblems(): array
    {
        /** @var array<int, ProblemTracking> */
        return $this->createQueryBuilder('pt')
            ->where('pt.correctionStatus = :status')
            ->setParameter('status', '待整改')
            ->orderBy('pt.correctionDeadline', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 按问题类型统计
     *
     * @return array<int, array<string, mixed>>
     */
    public function countByType(): array
    {
        /** @var array<int, array<string, mixed>> */
        return $this->createQueryBuilder('pt')
            ->select('pt.problemType, COUNT(pt.id) as count')
            ->groupBy('pt.problemType')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 按严重程度统计
     *
     * @return array<int, array<string, mixed>>
     */
    public function countBySeverity(): array
    {
        /** @var array<int, array<string, mixed>> */
        return $this->createQueryBuilder('pt')
            ->select('pt.problemSeverity, COUNT(pt.id) as count')
            ->groupBy('pt.problemSeverity')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找指定检查的问题.
     *
     * @return array<int, ProblemTracking>
     */
    public function findByInspection(string $inspectionId): array
    {
        /** @var array<int, ProblemTracking> */
        return $this->createQueryBuilder('pt')
            ->where('pt.inspection = :inspectionId')
            ->setParameter('inspectionId', $inspectionId)
            ->orderBy('pt.createTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找已验证通过的问题.
     *
     * @return array<int, ProblemTracking>
     */
    public function findVerifiedProblems(): array
    {
        /** @var array<int, ProblemTracking> */
        return $this->createQueryBuilder('pt')
            ->where('pt.verificationResult = :result')
            ->setParameter('result', '通过')
            ->orderBy('pt.verificationDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找指定责任人的问题.
     *
     * @return array<int, ProblemTracking>
     */
    public function findByResponsiblePerson(string $person): array
    {
        /** @var array<int, ProblemTracking> */
        return $this->createQueryBuilder('pt')
            ->where('pt.responsiblePerson = :person')
            ->setParameter('person', $person)
            ->orderBy('pt.correctionDeadline', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 统计整改完成率.
     */
    public function getCorrectionRate(): float
    {
        $total = $this->count([]);
        if (0 === $total) {
            return 0.0;
        }

        $corrected = $this->createQueryBuilder('pt')
            ->select('COUNT(pt.id)')
            ->where('pt.correctionStatus IN (:statuses)')
            ->setParameter('statuses', ['已整改', '已验证', '已关闭'])
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return ((int) $corrected / $total) * 100;
    }

    /**
     * 按日期范围查找问题.
     *
     * @return array<int, ProblemTracking>
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        /** @var array<int, ProblemTracking> */
        return $this->createQueryBuilder('pt')
            ->where('pt.discoveryDate >= :startDate')
            ->andWhere('pt.discoveryDate <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('pt.discoveryDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(ProblemTracking $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProblemTracking $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
