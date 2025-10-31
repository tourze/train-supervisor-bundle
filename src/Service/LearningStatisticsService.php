<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Service;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\TrainSupervisorBundle\Repository\SupervisorDataRepository;

/**
 * 学习统计服务
 * 提供各种学习数据统计查询功能.
 */
#[Autoconfigure(public: true)]
class LearningStatisticsService
{
    public function __construct(
        private readonly LearningStatisticsCalculator $calculator,
        private readonly LearningStatisticsQueryBuilder $queryBuilder,
        private readonly LearningStatisticsExporter $exporter,
    ) {
    }

    /**
     * 获取学习统计数据.
     *
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     */
    public function getLearningStatistics(array $filters): array
    {
        $enrollment = $this->getEnrollmentStatistics($filters);
        $completion = $this->getCompletedLearningStatistics($filters);
        $online = $this->getOnlineLearningStatistics($filters);

        return [
            'enrollment' => $enrollment,
            'completion' => $completion,
            'online' => $online,
            'summary' => [
                'total_enrolled' => $enrollment['total_enrolled'] ?? 0,
                'total_completed' => $completion['total_completed'] ?? 0,
                'completion_rate' => $completion['completion_rate'] ?? 0,
                'current_online' => $online['current_online'] ?? 0,
            ],
        ];
    }

    /**
     * 获取报名学习人数统计
     *
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     */
    public function getEnrollmentStatistics(array $filters): array
    {
        $query = $this->queryBuilder->buildBaseQuery($filters);

        // 统计总报名人数
        $totalEnrolled = $query
            ->select('SUM(s.dailyLoginCount) as total_enrolled')
            ->getQuery()
            ->getSingleScalarResult() ?? 0
        ;

        // 按时间段统计
        $byPeriod = $this->queryBuilder->getEnrollmentByPeriod($filters);

        // 按机构统计
        $byInstitution = $this->queryBuilder->getEnrollmentByInstitution($filters);

        return [
            'total_enrolled' => (int) $totalEnrolled,
            'by_period' => $byPeriod,
            'by_institution' => $byInstitution,
            'growth_rate' => $this->calculator->calculateGrowthRate($byPeriod),
        ];
    }

    /**
     * 获取已完成学习人数统计
     *
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     */
    public function getCompletedLearningStatistics(array $filters): array
    {
        $query = $this->queryBuilder->buildBaseQuery($filters);

        // 统计总完成人数
        $totalCompleted = $query
            ->select('SUM(s.dailyLearnCount) as total_completed')
            ->getQuery()
            ->getSingleScalarResult() ?? 0
        ;

        $enrollmentStats = $this->getEnrollmentStatistics($filters);
        $totalEnrolled = $enrollmentStats['total_enrolled'];
        assert(is_int($totalEnrolled));

        $totalCompletedFloat = is_numeric($totalCompleted) ? (float) $totalCompleted : 0;
        $totalEnrolledFloat = (float) $totalEnrolled;
        $completionRate = $totalEnrolledFloat > 0 ? ($totalCompletedFloat / $totalEnrolledFloat) * 100 : 0;

        // 按时间段统计
        $byPeriod = $this->queryBuilder->getCompletionByPeriod($filters);

        // 按机构统计
        $byInstitution = $this->queryBuilder->getCompletionByInstitution($filters);

        return [
            'total_completed' => (int) $totalCompleted,
            'total_enrolled' => $totalEnrolled,
            'completion_rate' => round($completionRate, 2),
            'by_period' => $byPeriod,
            'by_institution' => $byInstitution,
        ];
    }

    /**
     * 获取在线学习人数统计
     *
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     */
    public function getOnlineLearningStatistics(array $filters): array
    {
        // 获取当前在线人数（使用学习人数作为近似值）
        $currentDate = new \DateTime();
        $todayFilters = array_merge($filters, [
            'start_date' => $currentDate->format('Y-m-d'),
            'end_date' => $currentDate->format('Y-m-d'),
        ]);

        $query = $this->queryBuilder->buildBaseQuery($todayFilters);
        $currentOnline = $query
            ->select('SUM(s.dailyLearnCount) as current_online')
            ->getQuery()
            ->getSingleScalarResult() ?? 0
        ;

        // 获取峰值在线人数
        $peakQuery = $this->queryBuilder->buildBaseQuery($filters);
        $peakOnline = $peakQuery
            ->select('MAX(s.dailyLearnCount) as peak_online')
            ->getQuery()
            ->getSingleScalarResult() ?? 0
        ;

        // 按时间段统计在线学习情况
        $byPeriod = $this->queryBuilder->getOnlineByPeriod($filters);

        return [
            'current_online' => (int) $currentOnline,
            'peak_online' => (int) $peakOnline,
            'by_period' => $byPeriod,
            'average_online' => $this->calculator->calculateAverageOnline($byPeriod),
        ];
    }

    /**
     * 按机构统计
     *
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function getStatisticsByInstitution(array $filters): array
    {
        $query = $this->queryBuilder->buildBaseQuery($filters);

        $results = $query
            ->select([
                's.supplierId as institution_id',
                'SUM(s.dailyLoginCount) as enrolled_count',
                'SUM(s.dailyLearnCount) as completed_count',
                'SUM(s.totalClassroomCount) as classroom_count',
                'SUM(s.newClassroomCount) as new_classroom_count',
                'SUM(s.dailyCheatCount) as cheat_count',
            ])
            ->groupBy('s.supplierId')
            ->orderBy('enrolled_count', 'DESC')
            ->getQuery()
            ->getArrayResult()
        ;

        $mappedResults = [];
        foreach ($results as $item) {
            assert(is_array($item));
            $enrolledCountRaw = $item['enrolled_count'] ?? 0;
            $completedCountRaw = $item['completed_count'] ?? 0;
            $cheatCountRaw = $item['cheat_count'] ?? 0;

            $enrolledCount = is_numeric($enrolledCountRaw) ? (float) $enrolledCountRaw : 0.0;
            $completedCount = is_numeric($completedCountRaw) ? (float) $completedCountRaw : 0.0;
            $cheatCount = is_numeric($cheatCountRaw) ? (float) $cheatCountRaw : 0.0;

            $completionRate = $enrolledCount > 0
                ? ($completedCount / $enrolledCount) * 100
                : 0;
            $cheatRate = $completedCount > 0
                ? ($cheatCount / $completedCount) * 100
                : 0;

            $institutionIdRaw = $item['institution_id'] ?? 0;
            $classroomCountRaw = $item['classroom_count'] ?? 0;
            $newClassroomCountRaw = $item['new_classroom_count'] ?? 0;

            $institutionId = is_numeric($institutionIdRaw) ? (int) $institutionIdRaw : 0;
            $classroomCount = is_numeric($classroomCountRaw) ? (int) $classroomCountRaw : 0;
            $newClassroomCount = is_numeric($newClassroomCountRaw) ? (int) $newClassroomCountRaw : 0;

            $mappedResults[] = [
                'institution_id' => $institutionId,
                'institution_name' => (string) $institutionId,
                'enrolled_count' => (int) $enrolledCount,
                'completed_count' => (int) $completedCount,
                'completion_rate' => round($completionRate, 2),
                'classroom_count' => $classroomCount,
                'new_classroom_count' => $newClassroomCount,
                'cheat_count' => (int) $cheatCount,
                'cheat_rate' => round($cheatRate, 2),
            ];
        }

        return $mappedResults;
    }

    /**
     * 按区域统计
     *
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function getStatisticsByRegion(array $filters): array
    {
        // 这里需要根据实际的区域字段来实现
        // 假设机构表有region字段
        // 由于当前Supplier实体可能没有region字段，这里先返回模拟数据
        // 实际实现需要根据机构的区域信息来统计

        return [
            [
                'region' => '华北地区',
                'enrolled_count' => 1500,
                'completed_count' => 1200,
                'completion_rate' => 80.0,
                'institution_count' => 25,
            ],
            [
                'region' => '华东地区',
                'enrolled_count' => 2000,
                'completed_count' => 1800,
                'completion_rate' => 90.0,
                'institution_count' => 35,
            ],
            [
                'region' => '华南地区',
                'enrolled_count' => 1200,
                'completed_count' => 1000,
                'completion_rate' => 83.3,
                'institution_count' => 20,
            ],
        ];
    }

    /**
     * 按年龄段统计
     *
     * @param array<string, mixed> $filters
     * @return array<string, array<string, mixed>>
     */
    public function getStatisticsByAgeGroup(array $filters): array
    {
        // 这里需要根据实际的用户年龄数据来实现
        // 当前监督数据中没有年龄信息，这里返回模拟数据

        return [
            '18-25岁' => [
                'enrolled_count' => 800,
                'completed_count' => 720,
                'completion_rate' => 90.0,
            ],
            '26-35岁' => [
                'enrolled_count' => 1500,
                'completed_count' => 1350,
                'completion_rate' => 90.0,
            ],
            '36-45岁' => [
                'enrolled_count' => 1200,
                'completed_count' => 1080,
                'completion_rate' => 90.0,
            ],
            '46-55岁' => [
                'enrolled_count' => 800,
                'completed_count' => 680,
                'completion_rate' => 85.0,
            ],
            '55岁以上' => [
                'enrolled_count' => 400,
                'completed_count' => 320,
                'completion_rate' => 80.0,
            ],
        ];
    }

    /**
     * 获取学习趋势
     *
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function getLearningTrends(array $filters, string $periodType = 'daily'): array
    {
        $isSqlite = $this->calculator->isSqlite();
        $sql = $this->buildTrendSql($isSqlite);

        $params = [];
        $types = [];
        if (!$isSqlite) {
            $params['format'] = '%Y-%m-%d';
            $types['format'] = Types::STRING;
        }

        return $this->calculator->calculateTrends($sql, array_merge($filters, $params, ['types' => $types]));
    }

    private function buildTrendSql(bool $isSqlite): string
    {
        $dateFormat = $isSqlite ? 'strftime("%Y-%m-%d", s.date)' : 'DATE_FORMAT(s.date, :format)';

        return "SELECT {$dateFormat} as period,
                SUM(s.daily_login_count) as enrolled_count,
                SUM(s.daily_learn_count) as completed_count,
                AVG(s.daily_learn_count) as avg_learn_count
            FROM train_supervisor_data s
            WHERE 1=1";
    }

    /**
     * 导出学习统计数据.
     *
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function exportLearningStatistics(array $filters, string $format): array
    {
        $statistics = $this->getLearningStatistics($filters);

        return $this->exporter->export($statistics, $format);
    }

    /**
     * 获取学习概览.
     *
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function getLearningOverview(array $filters): array
    {
        $enrollment = $this->getEnrollmentStatistics($filters);
        $completion = $this->getCompletedLearningStatistics($filters);
        $online = $this->getOnlineLearningStatistics($filters);
        $institutions = $this->getStatisticsByInstitution($filters);

        return [
            'enrollment' => [
                'total' => $enrollment['total_enrolled'],
                'growth_rate' => $enrollment['growth_rate'] ?? 0,
            ],
            'completion' => [
                'total' => $completion['total_completed'],
                'rate' => $completion['completion_rate'],
            ],
            'online' => [
                'current' => $online['current_online'],
                'peak' => $online['peak_online'],
                'average' => $online['average_online'] ?? 0,
            ],
            'institutions' => [
                'total_count' => count($institutions),
                'active_count' => count(array_filter($institutions, fn ($inst) => $inst['enrolled_count'] > 0)),
                'top_performers' => array_slice($institutions, 0, 5),
            ],
        ];
    }

    /**
     * 获取实时统计数据.
     *
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function getRealtimeStatistics(array $filters): array
    {
        // 获取当日数据
        $today = new \DateTime();
        $todayFilters = array_merge($filters, [
            'start_date' => $today->format('Y-m-d'),
            'end_date' => $today->format('Y-m-d'),
        ]);

        $todayStats = $this->getLearningStatistics($todayFilters);

        // 获取昨日数据用于对比
        $yesterday = (clone $today)->modify('-1 day');
        $yesterdayFilters = array_merge($filters, [
            'start_date' => $yesterday->format('Y-m-d'),
            'end_date' => $yesterday->format('Y-m-d'),
        ]);

        $yesterdayStats = $this->getLearningStatistics($yesterdayFilters);

        $todayEnrolled = 0;
        $yesterdayEnrolled = 0;
        $todayCompleted = 0;
        $yesterdayCompleted = 0;

        $todaySummary = $todayStats['summary'] ?? [];
        $yesterdaySummary = $yesterdayStats['summary'] ?? [];

        if (is_array($todaySummary)) {
            $todayEnrolledRaw = $todaySummary['total_enrolled'] ?? 0;
            $todayEnrolled = is_int($todayEnrolledRaw) ? $todayEnrolledRaw : 0;
            $todayCompletedRaw = $todaySummary['total_completed'] ?? 0;
            $todayCompleted = is_int($todayCompletedRaw) ? $todayCompletedRaw : 0;
        }

        if (is_array($yesterdaySummary)) {
            $yesterdayEnrolledRaw = $yesterdaySummary['total_enrolled'] ?? 0;
            $yesterdayEnrolled = is_int($yesterdayEnrolledRaw) ? $yesterdayEnrolledRaw : 0;
            $yesterdayCompletedRaw = $yesterdaySummary['total_completed'] ?? 0;
            $yesterdayCompleted = is_int($yesterdayCompletedRaw) ? $yesterdayCompletedRaw : 0;
        }

        return [
            'today' => $todayStats,
            'yesterday' => $yesterdayStats,
            'comparison' => [
                'enrollment_change' => $this->calculator->calculateChange($todayEnrolled, $yesterdayEnrolled),
                'completion_change' => $this->calculator->calculateChange($todayCompleted, $yesterdayCompleted),
            ],
            'timestamp' => time(),
        ];
    }
}
