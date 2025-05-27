<?php

namespace Tourze\TrainSupervisorBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainSupervisorBundle\Entity\SupervisionInspection;

/**
 * 监督检查仓储类
 * 
 * @method SupervisionInspection|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupervisionInspection|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupervisionInspection[]    findAll()
 * @method SupervisionInspection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupervisionInspectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SupervisionInspection::class);
    }

    /**
     * 查找已完成的检查
     */
    public function findCompletedInspections(): array
    {
        return $this->createQueryBuilder('si')
            ->where('si.inspectionStatus = :status')
            ->setParameter('status', '已完成')
            ->orderBy('si.inspectionDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定机构的检查记录
     */
    public function findByInstitution(string $institutionId): array
    {
        return $this->createQueryBuilder('si')
            ->where('si.institution = :institutionId')
            ->setParameter('institutionId', $institutionId)
            ->orderBy('si.inspectionDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找有问题的检查
     */
    public function findInspectionsWithProblems(): array
    {
        return $this->createQueryBuilder('si')
            ->where('JSON_LENGTH(si.foundProblems) > 0')
            ->orderBy('si.inspectionDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 按检查类型统计
     */
    public function countByType(): array
    {
        return $this->createQueryBuilder('si')
            ->select('si.inspectionType, COUNT(si.id) as count')
            ->groupBy('si.inspectionType')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定日期范围内的检查
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('si')
            ->where('si.inspectionDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('si.inspectionDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
} 