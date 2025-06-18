<?php

namespace Tourze\TrainSupervisorBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\TrainSupervisorBundle\Entity\SupervisionReport;
use Tourze\TrainSupervisorBundle\Repository\ProblemTrackingRepository;
use Tourze\TrainSupervisorBundle\Repository\QualityAssessmentRepository;
use Tourze\TrainSupervisorBundle\Repository\SupervisionInspectionRepository;
use Tourze\TrainSupervisorBundle\Repository\SupervisionReportRepository;

/**
 * 监督报告服务
 * 负责生成各类监督报告、统计分析和数据导出
 */
class ReportService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SupervisionReportRepository $reportRepository,
        private readonly SupervisionInspectionRepository $inspectionRepository,
        private readonly ProblemTrackingRepository $problemRepository,
        private readonly QualityAssessmentRepository $assessmentRepository,
    ) {
    }

    /**
     * 生成日报
     */
    public function generateDailyReport(\DateTimeInterface $date, string $reporter): SupervisionReport
    {
        $startDate = \DateTime::createFromInterface($date)->setTime(0, 0, 0);
        $endDate = \DateTime::createFromInterface($date)->setTime(23, 59, 59);

        $supervisionData = $this->collectSupervisionData($startDate, $endDate);
        $problemSummary = $this->collectProblemSummary($startDate, $endDate);
        $statisticsData = $this->generateStatistics($startDate, $endDate);

        $report = new SupervisionReport();
        $report->setReportType('日报')
            ->setReportTitle(sprintf('%s 培训监督日报', $date->format('Y年m月d日')))
            ->setReportPeriodStart($startDate)
            ->setReportPeriodEnd($endDate)
            ->setReportDate($date)
            ->setReporter($reporter)
            ->setSupervisionData($supervisionData)
            ->setProblemSummary($problemSummary)
            ->setStatisticsData($statisticsData)
            ->setRecommendations($this->generateRecommendations($problemSummary))
            ->setReportContent($this->generateReportContent('日报', $supervisionData, $problemSummary, $statisticsData));

        $this->entityManager->persist($report);
        $this->entityManager->flush();

        return $report;
    }

    /**
     * 生成周报
     */
    public function generateWeeklyReport(\DateTimeInterface $weekStart, string $reporter): SupervisionReport
    {
        $startDate = \DateTime::createFromInterface($weekStart)->setTime(0, 0, 0);
        $endDate = \DateTime::createFromInterface($weekStart)->modify('+6 days')->setTime(23, 59, 59);

        $supervisionData = $this->collectSupervisionData($startDate, $endDate);
        $problemSummary = $this->collectProblemSummary($startDate, $endDate);
        $statisticsData = $this->generateStatistics($startDate, $endDate);

        $report = new SupervisionReport();
        $report->setReportType('周报')
            ->setReportTitle(sprintf('%s至%s 培训监督周报', 
                $startDate->format('Y年m月d日'), 
                $endDate->format('Y年m月d日')
            ))
            ->setReportPeriodStart($startDate)
            ->setReportPeriodEnd($endDate)
            ->setReportDate(new \DateTime())
            ->setReporter($reporter)
            ->setSupervisionData($supervisionData)
            ->setProblemSummary($problemSummary)
            ->setStatisticsData($statisticsData)
            ->setRecommendations($this->generateRecommendations($problemSummary))
            ->setReportContent($this->generateReportContent('周报', $supervisionData, $problemSummary, $statisticsData));

        $this->entityManager->persist($report);
        $this->entityManager->flush();

        return $report;
    }

    /**
     * 生成月报
     */
    public function generateMonthlyReport(int $year, int $month, string $reporter): SupervisionReport
    {
        $startDate = new \DateTime(sprintf('%d-%02d-01 00:00:00', $year, $month));
        $endDate = (clone $startDate)->modify('last day of this month')->setTime(23, 59, 59);

        $supervisionData = $this->collectSupervisionData($startDate, $endDate);
        $problemSummary = $this->collectProblemSummary($startDate, $endDate);
        $statisticsData = $this->generateStatistics($startDate, $endDate);
        $trendAnalysis = $this->generateTrendAnalysis($startDate, $endDate);

        $report = new SupervisionReport();
        $report->setReportType('月报')
            ->setReportTitle(sprintf('%d年%d月 培训监督月报', $year, $month))
            ->setReportPeriodStart($startDate)
            ->setReportPeriodEnd($endDate)
            ->setReportDate(new \DateTime())
            ->setReporter($reporter)
            ->setSupervisionData($supervisionData)
            ->setProblemSummary($problemSummary)
            ->setStatisticsData(array_merge($statisticsData, $trendAnalysis))
            ->setRecommendations($this->generateRecommendations($problemSummary))
            ->setReportContent($this->generateReportContent('月报', $supervisionData, $problemSummary, $statisticsData));

        $this->entityManager->persist($report);
        $this->entityManager->flush();

        return $report;
    }

    /**
     * 生成专项报告
     */
    public function generateSpecialReport(
        string $reportTitle,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        string $reporter,
        array $specialCriteria = []
    ): SupervisionReport {
        $supervisionData = $this->collectSupervisionData($startDate, $endDate, $specialCriteria);
        $problemSummary = $this->collectProblemSummary($startDate, $endDate, $specialCriteria);
        $statisticsData = $this->generateStatistics($startDate, $endDate, $specialCriteria);

        $report = new SupervisionReport();
        $report->setReportType('专项报告')
            ->setReportTitle($reportTitle)
            ->setReportPeriodStart($startDate)
            ->setReportPeriodEnd($endDate)
            ->setReportDate(new \DateTime())
            ->setReporter($reporter)
            ->setSupervisionData($supervisionData)
            ->setProblemSummary($problemSummary)
            ->setStatisticsData($statisticsData)
            ->setRecommendations($this->generateRecommendations($problemSummary))
            ->setReportContent($this->generateReportContent('专项报告', $supervisionData, $problemSummary, $statisticsData));

        $this->entityManager->persist($report);
        $this->entityManager->flush();

        return $report;
    }

    /**
     * 发布报告
     */
    public function publishReport(SupervisionReport $report): void
    {
        $report->setReportStatus('已发布');
        $this->entityManager->flush();
    }

    /**
     * 归档报告
     */
    public function archiveReport(SupervisionReport $report): void
    {
        $report->setReportStatus('已归档');
        $this->entityManager->flush();
    }

    /**
     * 收集监督数据
     */
    private function collectSupervisionData(
        \DateTimeInterface $startDate, 
        \DateTimeInterface $endDate, 
        array $criteria = []
    ): array {
        // 收集检查数据
        $inspections = $this->inspectionRepository->findByDateRange($startDate, $endDate, $criteria);
        
        // 收集质量评估数据
        $assessments = $this->assessmentRepository->findByDateRange($startDate, $endDate, $criteria);

        return [
            'inspection_count' => count($inspections),
            'assessment_count' => count($assessments),
            'inspections' => array_map(fn($inspection) => [
                'id' => $inspection->getId(),
                'inspection_type' => $inspection->getInspectionType(),
                'inspection_date' => $inspection->getInspectionDate()->format('Y-m-d'),
                'inspection_result' => $inspection->getInspectionResult(),
                'score' => $inspection->getScore(),
            ], $inspections),
            'assessments' => array_map(fn($assessment) => [
                'id' => $assessment->getId(),
                'assessment_type' => $assessment->getAssessmentType(),
                'assessment_date' => $assessment->getAssessmentDate()->format('Y-m-d'),
                'overall_score' => $assessment->getOverallScore(),
                'assessment_result' => $assessment->getAssessmentResult(),
            ], $assessments),
        ];
    }

    /**
     * 收集问题汇总
     */
    private function collectProblemSummary(
        \DateTimeInterface $startDate, 
        \DateTimeInterface $endDate, 
        array $criteria = []
    ): array {
        $problems = $this->problemRepository->findByDateRange($startDate, $endDate, $criteria);

        $summary = [
            'total_problems' => count($problems),
            'by_severity' => [],
            'by_status' => [],
            'by_category' => [],
            'problems' => [],
        ];

        foreach ($problems as $problem) {
            // 按严重程度统计
            $severity = $problem->getSeverityLevel();
            $summary['by_severity'][$severity] = ($summary['by_severity'][$severity] ?? 0) + 1;

            // 按状态统计
            $status = $problem->getStatus();
            $summary['by_status'][$status] = ($summary['by_status'][$status] ?? 0) + 1;

            // 按类别统计
            $category = $problem->getProblemCategory();
            $summary['by_category'][$category] = ($summary['by_category'][$category] ?? 0) + 1;

            // 问题详情
            $summary['problems'][] = [
                'id' => $problem->getId(),
                'title' => $problem->getProblemTitle(),
                'category' => $category,
                'severity' => $severity,
                'status' => $status,
                'found_date' => $problem->getFoundDate()->format('Y-m-d'),
                'deadline' => $problem->getDeadline()?->format('Y-m-d'),
            ];
        }

        return $summary;
    }

    /**
     * 生成统计数据
     */
    private function generateStatistics(
        \DateTimeInterface $startDate, 
        \DateTimeInterface $endDate, 
        array $criteria = []
    ): array {
        $inspections = $this->inspectionRepository->findByDateRange($startDate, $endDate, $criteria);
        $assessments = $this->assessmentRepository->findByDateRange($startDate, $endDate, $criteria);
        $problems = $this->problemRepository->findByDateRange($startDate, $endDate, $criteria);

        // 计算平均分数
        $inspectionScores = array_filter(array_map(fn($i) => $i->getScore(), $inspections));
        $assessmentScores = array_filter(array_map(fn($a) => $a->getOverallScore(), $assessments));

        return [
            'period_days' => $startDate->diff($endDate)->days + 1,
            'inspection_stats' => [
                'total' => count($inspections),
                'average_score' => $inspectionScores ? round(array_sum($inspectionScores) / count($inspectionScores), 2) : 0,
                'pass_rate' => $this->calculatePassRate($inspections),
            ],
            'assessment_stats' => [
                'total' => count($assessments),
                'average_score' => $assessmentScores ? round(array_sum($assessmentScores) / count($assessmentScores), 2) : 0,
                'excellent_rate' => $this->calculateExcellentRate($assessments),
            ],
            'problem_stats' => [
                'total' => count($problems),
                'resolved' => count(array_filter($problems, fn($p) => $p->getStatus() === '已解决')),
                'pending' => count(array_filter($problems, fn($p) => $p->getStatus() === '待处理')),
                'in_progress' => count(array_filter($problems, fn($p) => $p->getStatus() === '处理中')),
                'resolution_rate' => $this->calculateResolutionRate($problems),
            ],
        ];
    }

    /**
     * 生成趋势分析
     */
    private function generateTrendAnalysis(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        // 获取上一个周期的数据进行对比
        $periodDays = $startDate->diff($endDate)->days + 1;
        $previousStart = \DateTime::createFromInterface($startDate)->modify("-{$periodDays} days");
        $previousEnd = \DateTime::createFromInterface($startDate)->modify('-1 day');

        $currentStats = $this->generateStatistics($startDate, $endDate);
        $previousStats = $this->generateStatistics($previousStart, $previousEnd);

        return [
            'trend_analysis' => [
                'inspection_trend' => $this->calculateTrend(
                    $currentStats['inspection_stats']['total'],
                    $previousStats['inspection_stats']['total']
                ),
                'score_trend' => $this->calculateTrend(
                    $currentStats['inspection_stats']['average_score'],
                    $previousStats['inspection_stats']['average_score']
                ),
                'problem_trend' => $this->calculateTrend(
                    $currentStats['problem_stats']['total'],
                    $previousStats['problem_stats']['total']
                ),
                'resolution_trend' => $this->calculateTrend(
                    $currentStats['problem_stats']['resolution_rate'],
                    $previousStats['problem_stats']['resolution_rate']
                ),
            ],
        ];
    }

    /**
     * 生成建议措施
     */
    private function generateRecommendations(array $problemSummary): array
    {
        $recommendations = [];

        // 根据问题严重程度生成建议
        if ((bool) isset($problemSummary['by_severity']['严重']) && $problemSummary['by_severity']['严重'] > 0) {
            $recommendations[] = '发现严重问题，建议立即组织专项整改，加强现场监督检查。';
        }

        // 根据问题数量生成建议
        if ($problemSummary['total_problems'] > 10) {
            $recommendations[] = '问题数量较多，建议加强培训机构管理，完善质量控制体系。';
        }

        // 根据问题状态生成建议
        $pendingCount = $problemSummary['by_status']['待处理'] ?? 0;
        if ($pendingCount > 5) {
            $recommendations[] = '待处理问题较多，建议加快问题处理进度，明确责任人和时限。';
        }

        // 默认建议
        if ((bool) empty($recommendations)) {
            $recommendations[] = '继续保持良好的监督管理水平，持续改进培训质量。';
        }

        return $recommendations;
    }

    /**
     * 生成报告内容
     */
    private function generateReportContent(
        string $reportType,
        array $supervisionData,
        array $problemSummary,
        array $statisticsData
    ): string {
        $content = "# {$reportType}\n\n";
        
        $content .= "## 监督概况\n";
        $content .= sprintf("本期共进行检查 %d 次，质量评估 %d 次。\n\n", 
            $supervisionData['inspection_count'], 
            $supervisionData['assessment_count']
        );

        $content .= "## 问题情况\n";
        $content .= sprintf("发现问题 %d 个，其中：\n", $problemSummary['total_problems']);
        foreach ($problemSummary['by_severity'] as $severity => $count) {
            $content .= sprintf("- %s：%d 个\n", $severity, $count);
        }
        $content .= "\n";

        $content .= "## 统计分析\n";
        $content .= sprintf("- 检查平均分：%.2f 分\n", $statisticsData['inspection_stats']['average_score']);
        $content .= sprintf("- 检查通过率：%.2f%%\n", $statisticsData['inspection_stats']['pass_rate']);
        $content .= sprintf("- 问题解决率：%.2f%%\n", $statisticsData['problem_stats']['resolution_rate']);

        return $content;
    }

    /**
     * 计算通过率
     */
    private function calculatePassRate(array $inspections): float
    {
        if ((bool) empty($inspections)) {
            return 0.0;
        }

        $passCount = count(array_filter($inspections, fn($i) => $i->getScore() >= 60));
        return round(($passCount / count($inspections)) * 100, 2);
    }

    /**
     * 计算优秀率
     */
    private function calculateExcellentRate(array $assessments): float
    {
        if ((bool) empty($assessments)) {
            return 0.0;
        }

        $excellentCount = count(array_filter($assessments, fn($a) => $a->getOverallScore() >= 90));
        return round(($excellentCount / count($assessments)) * 100, 2);
    }

    /**
     * 计算解决率
     */
    private function calculateResolutionRate(array $problems): float
    {
        if ((bool) empty($problems)) {
            return 0.0;
        }

        $resolvedCount = count(array_filter($problems, fn($p) => $p->getStatus() === '已解决'));
        return round(($resolvedCount / count($problems)) * 100, 2);
    }

    /**
     * 计算趋势
     */
    private function calculateTrend(float $current, float $previous): array
    {
        if ($previous == 0) {
            return ['change' => $current, 'percentage' => 0, 'direction' => 'stable'];
        }

        $change = $current - $previous;
        $percentage = round(($change / $previous) * 100, 2);
        
        $direction = 'stable';
        if ($percentage > 5) {
            $direction = 'up';
        } elseif ($percentage < -5) {
            $direction = 'down';
        }

        return [
            'change' => round($change, 2),
            'percentage' => $percentage,
            'direction' => $direction,
        ];
    }
} 