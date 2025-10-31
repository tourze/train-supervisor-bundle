<?php

namespace Tourze\TrainSupervisorBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\TrainSupervisorBundle\Entity\SupervisionReport;

/**
 * 监督报告仓储类.
 *
 * @extends ServiceEntityRepository<SupervisionReport>
 */
#[AsRepository(entityClass: SupervisionReport::class)]
class SupervisionReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SupervisionReport::class);
    }

    /**
     * 查找已发布的报告.
     *
     * @return array<int, SupervisionReport>
     */
    public function findPublishedReports(): array
    {
        /** @var array<int, SupervisionReport> */
        return $this->createQueryBuilder('sr')
            ->where('sr.reportStatus = :status')
            ->setParameter('status', '已发布')
            ->orderBy('sr.reportDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 按报告类型查找.
     *
     * @return array<int, SupervisionReport>
     */
    public function findByType(string $type): array
    {
        /** @var array<int, SupervisionReport> */
        return $this->createQueryBuilder('sr')
            ->where('sr.reportType = :type')
            ->setParameter('type', $type)
            ->orderBy('sr.reportDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找指定期间的报告.
     *
     * @return array<int, SupervisionReport>
     */
    public function findByPeriod(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        /** @var array<int, SupervisionReport> */
        return $this->createQueryBuilder('sr')
            ->where('sr.reportPeriodStart <= :endDate')
            ->andWhere('sr.reportPeriodEnd >= :startDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('sr.reportDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找草稿报告.
     *
     * @return array<int, SupervisionReport>
     */
    public function findDraftReports(): array
    {
        /** @var array<int, SupervisionReport> */
        return $this->createQueryBuilder('sr')
            ->where('sr.reportStatus = :status')
            ->setParameter('status', '草稿')
            ->orderBy('sr.updateTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 按报告类型统计
     *
     * @return array<int, array<string, mixed>>
     */
    public function countByType(): array
    {
        /** @var array<int, array<string, mixed>> */
        return $this->createQueryBuilder('sr')
            ->select('sr.reportType, COUNT(sr.id) as count')
            ->groupBy('sr.reportType')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找最新的报告.
     *
     * @return array<int, SupervisionReport>
     */
    public function findLatestReports(int $limit = 10): array
    {
        /** @var array<int, SupervisionReport> */
        return $this->createQueryBuilder('sr')
            ->where('sr.reportStatus = :status')
            ->setParameter('status', '已发布')
            ->orderBy('sr.reportDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(SupervisionReport $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SupervisionReport $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
