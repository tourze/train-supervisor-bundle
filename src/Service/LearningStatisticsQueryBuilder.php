<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Service;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Tourze\TrainSupervisorBundle\Repository\SupervisorDataRepository;

/**
 * 学习统计查询构建器
 * 负责构建各类统计查询.
 */
class LearningStatisticsQueryBuilder
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SupervisorDataRepository $supervisorDataRepository,
        private readonly LearningStatisticsCalculator $calculator,
    ) {
    }

    /**
     * 按时间段获取报名统计.
     *
     * @param array<string, mixed> $filters
     * @return array<int, array{date: string, count: int}>
     */
    public function getEnrollmentByPeriod(array $filters): array
    {
        $connection = $this->entityManager->getConnection();
        $isSqlite = $this->calculator->isSqlite();
        $sql = 'SELECT ' . ($isSqlite ? "strftime('%Y-%m-%d', s.date)" : "DATE_FORMAT(s.date, '%Y-%m-%d')") . ' as date,
                SUM(s.daily_login_count) as count
            FROM train_supervisor_data s
            WHERE 1=1';
        $params = [];
        $types = [];
        [$sql, $params, $types] = $this->calculator->buildSqlFilters($sql, $filters, $params, $types);
        $sql .= ' GROUP BY date ORDER BY date ASC';

        $executionTypes = $this->convertTypesForQuery($types);
        $stmt = $connection->executeQuery($sql, $params, $executionTypes);
        $rows = $stmt->fetchAllAssociative();

        return $this->mapPeriodData($rows);
    }

    /**
     * 按机构获取报名统计.
     *
     * @param array<string, mixed> $filters
     * @return array<int, array{institution_name: string, count: int}>
     */
    public function getEnrollmentByInstitution(array $filters): array
    {
        $query = $this->buildBaseQuery($filters);

        $rows = $query
            ->select([
                's.supplierId as institution_name',
                'SUM(s.dailyLoginCount) as count',
            ])
            ->groupBy('s.supplierId')
            ->orderBy('count', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult()
        ;

        /** @var array<int, array<string, mixed>> $typedRows */
        $typedRows = $rows;

        return $this->mapInstitutionData($typedRows);
    }

    /**
     * 按时间段获取完成统计.
     *
     * @param array<string, mixed> $filters
     * @return array<int, array{date: string, count: int}>
     */
    public function getCompletionByPeriod(array $filters): array
    {
        $connection = $this->entityManager->getConnection();
        $isSqlite = $this->calculator->isSqlite();
        $sql = 'SELECT ' . ($isSqlite ? "strftime('%Y-%m-%d', s.date)" : "DATE_FORMAT(s.date, '%Y-%m-%d')") . ' as date,
                SUM(s.daily_learn_count) as count
            FROM train_supervisor_data s
            WHERE 1=1';
        $params = [];
        $types = [];
        [$sql, $params, $types] = $this->calculator->buildSqlFilters($sql, $filters, $params, $types);
        $sql .= ' GROUP BY date ORDER BY date ASC';

        $executionTypes = $this->convertTypesForQuery($types);
        $stmt = $connection->executeQuery($sql, $params, $executionTypes);
        $rows = $stmt->fetchAllAssociative();

        return $this->mapPeriodData($rows);
    }

    /**
     * 按机构获取完成统计.
     *
     * @param array<string, mixed> $filters
     * @return array<int, array{institution_name: string, count: int}>
     */
    public function getCompletionByInstitution(array $filters): array
    {
        $query = $this->buildBaseQuery($filters);

        $rows = $query
            ->select([
                's.supplierId as institution_name',
                'SUM(s.dailyLearnCount) as count',
            ])
            ->groupBy('s.supplierId')
            ->orderBy('count', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult()
        ;

        /** @var array<int, array<string, mixed>> $typedRows */
        $typedRows = $rows;

        return $this->mapInstitutionData($typedRows);
    }

    /**
     * 按时间段获取在线统计.
     *
     * @param array<string, mixed> $filters
     * @return array<int, array{date: string, count: int}>
     */
    public function getOnlineByPeriod(array $filters): array
    {
        $connection = $this->entityManager->getConnection();
        $isSqlite = $this->calculator->isSqlite();
        $sql = 'SELECT ' . ($isSqlite ? "strftime('%Y-%m-%d', s.date)" : "DATE_FORMAT(s.date, '%Y-%m-%d')") . ' as date,
                SUM(s.daily_learn_count) as count
            FROM train_supervisor_data s
            WHERE 1=1';
        $params = [];
        $types = [];
        [$sql, $params, $types] = $this->calculator->buildSqlFilters($sql, $filters, $params, $types);
        $sql .= ' GROUP BY date ORDER BY date ASC';

        $executionTypes = $this->convertTypesForQuery($types);
        $stmt = $connection->executeQuery($sql, $params, $executionTypes);
        $rows = $stmt->fetchAllAssociative();

        return $this->mapPeriodData($rows);
    }

    /**
     * 构建基础查询.
     *
     * @param array<string, mixed> $filters
     */
    public function buildBaseQuery(array $filters): QueryBuilder
    {
        $qb = $this->supervisorDataRepository->createQueryBuilder('s');

        if (isset($filters['start_date']) && '' !== $filters['start_date']) {
            $qb->andWhere('s.date >= :start_date')
                ->setParameter('start_date', $filters['start_date'])
            ;
        }

        if (isset($filters['end_date']) && '' !== $filters['end_date']) {
            $qb->andWhere('s.date <= :end_date')
                ->setParameter('end_date', $filters['end_date'])
            ;
        }

        if (isset($filters['institution_id']) && '' !== $filters['institution_id']) {
            $qb->andWhere('s.supplierId = :institution_id')
                ->setParameter('institution_id', $filters['institution_id'])
            ;
        }

        if (isset($filters['institution_ids']) && [] !== $filters['institution_ids']) {
            $qb->andWhere('s.supplierId IN (:institution_ids)')
                ->setParameter('institution_ids', $filters['institution_ids'], ArrayParameterType::STRING)
            ;
        }

        return $qb;
    }

    /**
     * @param array<string, int|string|ArrayParameterType> $types
     * @return array<int<0, max>|string, ArrayParameterType|ParameterType|Type|string>
     */
    private function convertTypesForQuery(array $types): array
    {
        $executionTypes = [];
        foreach ($types as $key => $value) {
            if ($value instanceof ArrayParameterType
                || $value instanceof ParameterType
                || $value instanceof Type
                || is_string($value)) {
                $executionTypes[$key] = $value;
            }
        }

        return $executionTypes;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array{date: string, count: int}>
     */
    private function mapPeriodData(array $rows): array
    {
        $mappedRows = [];
        foreach ($rows as $row) {
            assert(is_array($row));
            $dateRaw = $row['date'] ?? '';
            $dateStr = is_string($dateRaw) || is_numeric($dateRaw) ? (string) $dateRaw : '';
            $countRaw = $row['count'] ?? 0;
            $mappedRows[] = [
                'date' => $dateStr,
                'count' => is_numeric($countRaw) ? (int) $countRaw : 0,
            ];
        }

        return $mappedRows;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array{institution_name: string, count: int}>
     */
    private function mapInstitutionData(array $rows): array
    {
        $mappedRows = [];
        foreach ($rows as $row) {
            assert(is_array($row));
            $institutionNameRaw = $row['institution_name'] ?? '';
            $institutionNameStr = is_string($institutionNameRaw) || is_numeric($institutionNameRaw)
                ? (string) $institutionNameRaw
                : '';
            $countRaw = $row['count'] ?? 0;
            $mappedRows[] = [
                'institution_name' => $institutionNameStr,
                'count' => is_numeric($countRaw) ? (int) $countRaw : 0,
            ];
        }

        return $mappedRows;
    }
}
