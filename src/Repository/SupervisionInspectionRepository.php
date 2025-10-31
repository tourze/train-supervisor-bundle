<?php

namespace Tourze\TrainSupervisorBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\TrainSupervisorBundle\Entity\SupervisionInspection;

/**
 * 监督检查仓储类.
 *
 * @extends ServiceEntityRepository<SupervisionInspection>
 */
#[AsRepository(entityClass: SupervisionInspection::class)]
class SupervisionInspectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SupervisionInspection::class);
    }

    /**
     * 查找已完成的检查.
     *
     * @return array<int, SupervisionInspection>
     */
    public function findCompletedInspections(): array
    {
        /** @var array<int, SupervisionInspection> */
        return $this->createQueryBuilder('si')
            ->where('si.inspectionStatus = :status')
            ->setParameter('status', '已完成')
            ->orderBy('si.inspectionDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找指定机构的检查记录.
     *
     * @return array<int, SupervisionInspection>
     */
    public function findByInstitution(string $institutionName): array
    {
        /** @var array<int, SupervisionInspection> */
        return $this->createQueryBuilder('si')
            ->where('si.institutionName = :institutionName')
            ->setParameter('institutionName', $institutionName)
            ->orderBy('si.inspectionDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找有问题的检查.
     *
     * @return array<int, SupervisionInspection>
     */
    public function findInspectionsWithProblems(): array
    {
        /** @var array<int, SupervisionInspection> */
        return $this->createQueryBuilder('si')
            ->where('si.foundProblems IS NOT NULL')
            ->andWhere('si.foundProblems != :emptyArray')
            ->setParameter('emptyArray', '[]')
            ->orderBy('si.inspectionDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 按检查类型统计
     *
     * @return array<int, array<string, mixed>>
     */
    public function countByType(): array
    {
        /** @var array<int, array<string, mixed>> */
        return $this->createQueryBuilder('si')
            ->select('si.inspectionType, COUNT(si.id) as count')
            ->groupBy('si.inspectionType')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找指定日期范围内的检查.
     *
     * @return array<int, SupervisionInspection>
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        /** @var array<int, SupervisionInspection> */
        return $this->createQueryBuilder('si')
            ->where('si.inspectionDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('si.inspectionDate', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(SupervisionInspection $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SupervisionInspection $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
