<?php

namespace Tourze\TrainSupervisorBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\TrainSupervisorBundle\Repository\SupervisorRepository;

/**
 * 学习统计服务
 * 提供各种学习数据统计查询功能
 */
class LearningStatisticsService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SupervisorRepository $supervisorRepository,
    ) {
    }

    /**
     * 获取学习统计数据
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
            ]
        ];
    }

    /**
     * 获取报名学习人数统计
     */
    public function getEnrollmentStatistics(array $filters): array
    {
        $query = $this->buildBaseQuery($filters);
        
        // 统计总报名人数
        $totalEnrolled = $query
            ->select('SUM(s.dailyLoginCount) as total_enrolled')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        // 按时间段统计
        $byPeriod = $this->getEnrollmentByPeriod($filters);
        
        // 按机构统计
        $byInstitution = $this->getEnrollmentByInstitution($filters);

        return [
            'total_enrolled' => (int)$totalEnrolled,
            'by_period' => $byPeriod,
            'by_institution' => $byInstitution,
            'growth_rate' => $this->calculateGrowthRate($byPeriod),
        ];
    }

    /**
     * 获取已完成学习人数统计
     */
    public function getCompletedLearningStatistics(array $filters): array
    {
        $query = $this->buildBaseQuery($filters);
        
        // 统计总完成人数
        $totalCompleted = $query
            ->select('SUM(s.dailyLearnCount) as total_completed')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        $enrollmentStats = $this->getEnrollmentStatistics($filters);
        $totalEnrolled = $enrollmentStats['total_enrolled'];
        
        $completionRate = $totalEnrolled > 0 ? ($totalCompleted / $totalEnrolled) * 100 : 0;

        // 按时间段统计
        $byPeriod = $this->getCompletionByPeriod($filters);
        
        // 按机构统计
        $byInstitution = $this->getCompletionByInstitution($filters);

        return [
            'total_completed' => (int)$totalCompleted,
            'total_enrolled' => $totalEnrolled,
            'completion_rate' => round($completionRate, 2),
            'by_period' => $byPeriod,
            'by_institution' => $byInstitution,
        ];
    }

    /**
     * 获取在线学习人数统计
     */
    public function getOnlineLearningStatistics(array $filters): array
    {
        // 获取当前在线人数（使用学习人数作为近似值）
        $currentDate = new \DateTime();
        $todayFilters = array_merge($filters, [
            'start_date' => $currentDate->format('Y-m-d'),
            'end_date' => $currentDate->format('Y-m-d')
        ]);

        $query = $this->buildBaseQuery($todayFilters);
        $currentOnline = $query
            ->select('SUM(s.dailyLearnCount) as current_online')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        // 获取峰值在线人数
        $peakQuery = $this->buildBaseQuery($filters);
        $peakOnline = $peakQuery
            ->select('MAX(s.dailyLearnCount) as peak_online')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        // 按时间段统计在线学习情况
        $byPeriod = $this->getOnlineByPeriod($filters);

        return [
            'current_online' => (int)$currentOnline,
            'peak_online' => (int)$peakOnline,
            'by_period' => $byPeriod,
            'average_online' => $this->calculateAverageOnline($byPeriod),
        ];
    }

    /**
     * 按机构统计
     */
    public function getStatisticsByInstitution(array $filters): array
    {
        $query = $this->buildBaseQuery($filters);
        
        $results = $query
            ->select([
                'supplier.name as institution_name',
                'supplier.id as institution_id',
                'SUM(s.dailyLoginCount) as enrolled_count',
                'SUM(s.dailyLearnCount) as completed_count',
                'SUM(s.totalClassroomCount) as classroom_count',
                'SUM(s.newClassroomCount) as new_classroom_count',
                'SUM(s.dailyCheatCount) as cheat_count'
            ])
            ->groupBy('supplier.id', 'supplier.name')
            ->orderBy('enrolled_count', 'DESC')
            ->getQuery()
            ->getArrayResult();

        return array_map(function($item) {
            $completionRate = $item['enrolled_count'] > 0 
                ? ($item['completed_count'] / $item['enrolled_count']) * 100 
                : 0;
            $cheatRate = $item['completed_count'] > 0 
                ? ($item['cheat_count'] / $item['completed_count']) * 100 
                : 0;

            return [
                'institution_id' => $item['institution_id'],
                'institution_name' => $item['institution_name'],
                'enrolled_count' => (int)$item['enrolled_count'],
                'completed_count' => (int)$item['completed_count'],
                'completion_rate' => round($completionRate, 2),
                'classroom_count' => (int)$item['classroom_count'],
                'new_classroom_count' => (int)$item['new_classroom_count'],
                'cheat_count' => (int)$item['cheat_count'],
                'cheat_rate' => round($cheatRate, 2),
            ];
        }, $results);
    }

    /**
     * 按区域统计
     */
    public function getStatisticsByRegion(array $filters): array
    {
        // 这里需要根据实际的区域字段来实现
        // 假设机构表有region字段
        $query = $this->buildBaseQuery($filters);
        
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
            ]
        ];
    }

    /**
     * 按年龄段统计
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
            ]
        ];
    }

    /**
     * 获取学习趋势
     */
    public function getLearningTrends(array $filters, string $periodType = 'daily'): array
    {
        $query = $this->buildBaseQuery($filters);
        
        $groupByFormat = match($periodType) {
            'daily' => 'DATE_FORMAT(s.date, \'%Y-%m-%d\')',
            'weekly' => 'YEARWEEK(s.date)',
            'monthly' => 'DATE_FORMAT(s.date, \'%Y-%m\')',
            default => 'DATE_FORMAT(s.date, \'%Y-%m-%d\')',
        };

        $results = $query
            ->select([
                $groupByFormat . ' as period',
                'SUM(s.dailyLoginCount) as enrolled_count',
                'SUM(s.dailyLearnCount) as completed_count',
                'AVG(s.dailyLearnCount) as avg_learn_count'
            ])
            ->groupBy('period')
            ->orderBy('period', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return array_map(function($item) {
            $completionRate = $item['enrolled_count'] > 0 
                ? ($item['completed_count'] / $item['enrolled_count']) * 100 
                : 0;

            return [
                'period' => $item['period'],
                'enrolled_count' => (int)$item['enrolled_count'],
                'completed_count' => (int)$item['completed_count'],
                'completion_rate' => round($completionRate, 2),
                'avg_learn_count' => round($item['avg_learn_count'], 2),
            ];
        }, $results);
    }

    /**
     * 导出学习统计数据
     */
    public function exportLearningStatistics(array $filters, string $format): array
    {
        $statistics = $this->getLearningStatistics($filters);
        
        switch ($format) {
            case 'csv':
                return $this->exportToCsv($statistics);
            case 'excel':
                return $this->exportToExcel($statistics);
            case 'pdf':
                return $this->exportToPdf($statistics);
            default:
                throw new \InvalidArgumentException('不支持的导出格式');
        }
    }

    /**
     * 获取学习概览
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
                'active_count' => count(array_filter($institutions, fn($inst) => $inst['enrolled_count'] > 0)),
                'top_performers' => array_slice($institutions, 0, 5),
            ]
        ];
    }

    /**
     * 获取实时统计数据
     */
    public function getRealtimeStatistics(array $filters): array
    {
        // 获取当日数据
        $today = new \DateTime();
        $todayFilters = array_merge($filters, [
            'start_date' => $today->format('Y-m-d'),
            'end_date' => $today->format('Y-m-d')
        ]);

        $todayStats = $this->getLearningStatistics($todayFilters);

        // 获取昨日数据用于对比
        $yesterday = (clone $today)->modify('-1 day');
        $yesterdayFilters = array_merge($filters, [
            'start_date' => $yesterday->format('Y-m-d'),
            'end_date' => $yesterday->format('Y-m-d')
        ]);

        $yesterdayStats = $this->getLearningStatistics($yesterdayFilters);

        return [
            'today' => $todayStats,
            'yesterday' => $yesterdayStats,
            'comparison' => [
                'enrollment_change' => $this->calculateChange(
                    $todayStats['summary']['total_enrolled'],
                    $yesterdayStats['summary']['total_enrolled']
                ),
                'completion_change' => $this->calculateChange(
                    $todayStats['summary']['total_completed'],
                    $yesterdayStats['summary']['total_completed']
                ),
            ],
            'timestamp' => time(),
        ];
    }

    /**
     * 构建基础查询
     */
    private function buildBaseQuery(array $filters)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->from('Tourze\TrainSupervisorBundle\Entity\Supervisor', 's')
           ->leftJoin('s.supplier', 'supplier');

        // 应用时间过滤
        if (!empty($filters['start_date'])) {
            $qb->andWhere('s.date >= :start_date')
               ->setParameter('start_date', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $qb->andWhere('s.date <= :end_date')
               ->setParameter('end_date', $filters['end_date']);
        }

        // 应用机构过滤
        if (!empty($filters['institution_id'])) {
            $qb->andWhere('supplier.id = :institution_id')
               ->setParameter('institution_id', $filters['institution_id']);
        }

        if (!empty($filters['institution_ids'])) {
            $qb->andWhere('supplier.id IN (:institution_ids)')
               ->setParameter('institution_ids', $filters['institution_ids']);
        }

        return $qb;
    }

    /**
     * 按时间段获取报名统计
     */
    private function getEnrollmentByPeriod(array $filters): array
    {
        $query = $this->buildBaseQuery($filters);
        
        return $query
            ->select([
                'DATE_FORMAT(s.date, \'%Y-%m-%d\') as date',
                'SUM(s.dailyLoginCount) as count'
            ])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * 按机构获取报名统计
     */
    private function getEnrollmentByInstitution(array $filters): array
    {
        $query = $this->buildBaseQuery($filters);
        
        return $query
            ->select([
                'supplier.name as institution_name',
                'SUM(s.dailyLoginCount) as count'
            ])
            ->groupBy('supplier.id')
            ->orderBy('count', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * 按时间段获取完成统计
     */
    private function getCompletionByPeriod(array $filters): array
    {
        $query = $this->buildBaseQuery($filters);
        
        return $query
            ->select([
                'DATE_FORMAT(s.date, \'%Y-%m-%d\') as date',
                'SUM(s.dailyLearnCount) as count'
            ])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * 按机构获取完成统计
     */
    private function getCompletionByInstitution(array $filters): array
    {
        $query = $this->buildBaseQuery($filters);
        
        return $query
            ->select([
                'supplier.name as institution_name',
                'SUM(s.dailyLearnCount) as count'
            ])
            ->groupBy('supplier.id')
            ->orderBy('count', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * 按时间段获取在线统计
     */
    private function getOnlineByPeriod(array $filters): array
    {
        $query = $this->buildBaseQuery($filters);
        
        return $query
            ->select([
                'DATE_FORMAT(s.date, \'%Y-%m-%d\') as date',
                'SUM(s.dailyLearnCount) as count'
            ])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * 计算增长率
     */
    private function calculateGrowthRate(array $periodData): float
    {
        if ((bool) count($periodData) < 2) {
            return 0;
        }

        $latest = end($periodData)['count'] ?? 0;
        $previous = prev($periodData)['count'] ?? 0;

        return $previous > 0 ? (($latest - $previous) / $previous) * 100 : 0;
    }

    /**
     * 计算平均在线人数
     */
    private function calculateAverageOnline(array $periodData): float
    {
        if ((bool) empty($periodData)) {
            return 0;
        }

        $total = array_sum(array_column($periodData, 'count'));
        return $total / count($periodData);
    }

    /**
     * 计算变化率
     */
    private function calculateChange(int $current, int $previous): array
    {
        $change = $current - $previous;
        $percentage = $previous > 0 ? ($change / $previous) * 100 : 0;

        return [
            'absolute' => $change,
            'percentage' => round($percentage, 2),
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable'),
        ];
    }

    /**
     * 导出为CSV格式
     */
    private function exportToCsv(array $data): array
    {
        $output = "机构名称,报名人数,完成人数,完成率,在线人数\n";
        
        foreach ($data['enrollment']['by_institution'] as $item) {
            $output .= sprintf("%s,%d,%d,%.2f%%,%d\n",
                $item['institution_name'],
                $item['count'],
                0, // 这里需要关联完成数据
                0,
                0
            );
        }

        return [
            'content' => $output,
            'mime_type' => 'text/csv; charset=utf-8'
        ];
    }

    /**
     * 导出为Excel格式
     */
    private function exportToExcel(array $data): array
    {
        // 这里应该使用PhpSpreadsheet等库来生成Excel文件
        // 目前返回CSV格式作为示例
        return $this->exportToCsv($data);
    }

    /**
     * 导出为PDF格式
     */
    private function exportToPdf(array $data): array
    {
        // 这里应该使用TCPDF或DomPDF等库来生成PDF文件
        // 目前返回简单的HTML格式作为示例
        $html = '<h1>学习统计报告</h1>';
        $html .= '<p>生成时间：' . date('Y-m-d H:i:s') . '</p>';
        
        return [
            'content' => $html,
            'mime_type' => 'text/html; charset=utf-8'
        ];
    }
} 