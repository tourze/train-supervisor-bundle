<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Service;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;

/**
 * 学习统计计算器
 * 负责处理复杂的统计计算逻辑.
 */
class LearningStatisticsCalculator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * 执行 SQL 查询并映射趋势数据.
     *
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function calculateTrends(string $sql, array $filters): array
    {
        $connection = $this->entityManager->getConnection();
        $params = [];
        $types = [];

        [$sql, $params, $types] = $this->buildSqlFilters($sql, $filters, $params, $types);
        $sql .= ' GROUP BY period ORDER BY period ASC';

        $executionTypes = $this->convertTypes($types);
        $stmt = $connection->executeQuery($sql, $params, $executionTypes);
        $results = $stmt->fetchAllAssociative();

        return $this->mapTrendResults($results);
    }

    /**
     * 统一应用常见过滤条件到 SQL 查询.
     *
     * @param array<string, mixed> $filters
     * @param array<string, mixed> $params
     * @param array<string, int|string|ArrayParameterType> $types
     * @return array{0: string, 1: array<string, mixed>, 2: array<string, int|string|ArrayParameterType>}
     */
    public function buildSqlFilters(string $sql, array $filters, array $params = [], array $types = []): array
    {
        if (isset($filters['start_date']) && '' !== $filters['start_date']) {
            $sql .= ' AND s.date >= :start_date';
            $params['start_date'] = $filters['start_date'];
        }
        if (isset($filters['end_date']) && '' !== $filters['end_date']) {
            $sql .= ' AND s.date <= :end_date';
            $params['end_date'] = $filters['end_date'];
        }
        if (isset($filters['institution_id']) && '' !== $filters['institution_id']) {
            $sql .= ' AND s.supplier_id = :institution_id';
            $params['institution_id'] = $filters['institution_id'];
        }
        if (isset($filters['institution_ids']) && [] !== $filters['institution_ids']) {
            $sql .= ' AND s.supplier_id IN (:institution_ids)';
            $params['institution_ids'] = $filters['institution_ids'];
            $types['institution_ids'] = ArrayParameterType::STRING;
        }

        return [$sql, $params, $types];
    }

    /**
     * 转换types为Connection::executeQuery期望的格式.
     *
     * @param array<string, int|string|ArrayParameterType> $types
     * @return array<int<0, max>|string, ArrayParameterType|ParameterType|Type|string>
     */
    private function convertTypes(array $types): array
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
     * 映射趋势结果.
     *
     * @param array<int, array<string, mixed>> $results
     * @return array<int, array<string, mixed>>
     */
    private function mapTrendResults(array $results): array
    {
        $mappedResults = [];
        foreach ($results as $item) {
            assert(is_array($item));
            $enrolledCountRaw = $item['enrolled_count'] ?? 0;
            $completedCountRaw = $item['completed_count'] ?? 0;
            $avgLearnCountRaw = $item['avg_learn_count'] ?? 0;

            $enrolledCount = is_numeric($enrolledCountRaw) ? (float) $enrolledCountRaw : 0.0;
            $completedCount = is_numeric($completedCountRaw) ? (float) $completedCountRaw : 0.0;
            $avgLearnCount = is_numeric($avgLearnCountRaw) ? (float) $avgLearnCountRaw : 0.0;

            $completionRate = $enrolledCount > 0
                ? ($completedCount / $enrolledCount) * 100
                : 0;

            $periodRaw = $item['period'] ?? '';
            $periodStr = is_string($periodRaw) || is_numeric($periodRaw) ? (string) $periodRaw : '';

            $mappedResults[] = [
                'period' => $periodStr,
                'enrolled_count' => (int) $enrolledCount,
                'completed_count' => (int) $completedCount,
                'completion_rate' => round($completionRate, 2),
                'avg_learn_count' => round($avgLearnCount, 2),
            ];
        }

        return $mappedResults;
    }

    /**
     * 计算增长率.
     *
     * @param array<int, array{date: string, count: int}> $periodData
     */
    public function calculateGrowthRate(array $periodData): float
    {
        if (count($periodData) < 2) {
            return 0;
        }

        $lastIndex = count($periodData) - 1;
        $latest = $periodData[$lastIndex]['count'] ?? 0;
        $previous = $periodData[$lastIndex - 1]['count'] ?? 0;

        return $previous > 0 ? (($latest - $previous) / $previous) * 100 : 0;
    }

    /**
     * 计算平均在线人数.
     *
     * @param array<int, array{date: string, count: int}> $periodData
     */
    public function calculateAverageOnline(array $periodData): float
    {
        if ([] === $periodData) {
            return 0;
        }

        $total = array_sum(array_column($periodData, 'count'));

        return $total / count($periodData);
    }

    /**
     * 计算变化率.
     *
     * @return array{absolute: int, percentage: float, direction: string}
     */
    public function calculateChange(int $current, int $previous): array
    {
        $change = $current - $previous;
        $percentage = $previous > 0 ? ($change / $previous) * 100 : 0;

        return [
            'absolute' => $change,
            'percentage' => round($percentage, 2),
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable'),
        ];
    }

    public function isSqlite(): bool
    {
        $connection = $this->entityManager->getConnection();
        $databasePlatform = $connection->getDatabasePlatform()::class;

        return str_contains($databasePlatform, 'SQLite');
    }
}
