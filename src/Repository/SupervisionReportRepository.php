<?php

namespace Tourze\TrainSupervisorBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainSupervisorBundle\Entity\SupervisionReport;

/**
 * 监督报告仓储类
 * 
 * @method SupervisionReport|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupervisionReport|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupervisionReport[]    findAll()
 * @method SupervisionReport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupervisionReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SupervisionReport::class);
    }

    /**
     * 查找已发布的报告
     */
    public function findPublishedReports(): array
    {
        return $this->createQueryBuilder('sr')
            ->where('sr.reportStatus = :status')
            ->setParameter('status', '已发布')
            ->orderBy('sr.reportDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 按报告类型查找
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('sr')
            ->where('sr.reportType = :type')
            ->setParameter('type', $type)
            ->orderBy('sr.reportDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定期间的报告
     */
    public function findByPeriod(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('sr')
            ->where('sr.reportPeriodStart <= :endDate')
            ->andWhere('sr.reportPeriodEnd >= :startDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('sr.reportDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找草稿报告
     */
    public function findDraftReports(): array
    {
        return $this->createQueryBuilder('sr')
            ->where('sr.reportStatus = :status')
            ->setParameter('status', '草稿')
            ->orderBy('sr.updateTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 按报告类型统计
     */
    public function countByType(): array
    {
        return $this->createQueryBuilder('sr')
            ->select('sr.reportType, COUNT(sr.id) as count')
            ->groupBy('sr.reportType')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找最新的报告
     */
    public function findLatestReports(int $limit = 10): array
    {
        return $this->createQueryBuilder('sr')
            ->where('sr.reportStatus = :status')
            ->setParameter('status', '已发布')
            ->orderBy('sr.reportDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
} 