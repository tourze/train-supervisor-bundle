<?php

namespace Tourze\TrainSupervisorBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\TrainSupervisorBundle\Entity\ProblemTracking;
use Tourze\TrainSupervisorBundle\Entity\QualityAssessment;
use Tourze\TrainSupervisorBundle\Entity\SupervisionInspection;
use Tourze\TrainSupervisorBundle\Entity\SupervisionReport;
use Tourze\TrainSupervisorBundle\Repository\ProblemTrackingRepository;
use Tourze\TrainSupervisorBundle\Repository\QualityAssessmentRepository;
use Tourze\TrainSupervisorBundle\Repository\SupervisionInspectionRepository;

/**
 * 监督报告服务
 * 负责生成各类监督报告、统计分析和数据导出.
 */
#[Autoconfigure(public: true)]
class ReportService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SupervisionInspectionRepository $inspectionRepository,
        private readonly ProblemTrackingRepository $problemRepository,
        private readonly QualityAssessmentRepository $assessmentRepository,
    ) {
    }

    /**
     * 生成日报.
     */
    public function generateDailyReport(\DateTimeInterface $date, string $reporter): SupervisionReport
    {
        $startDate = \DateTimeImmutable::createFromInterface($date)->setTime(0, 0, 0);
        $endDate = \DateTimeImmutable::createFromInterface($date)->setTime(23, 59, 59);

        $supervisionData = $this->collectSupervisionData($startDate, $endDate);
        $problemSummary = $this->collectProblemSummary($startDate, $endDate);
        $statisticsData = $this->generateStatistics($startDate, $endDate);

        $report = new SupervisionReport();
        $report->setReportType('日报');
        $report->setReportTitle(sprintf('%s 培训监督日报', $date->format('Y年m月d日')));
        $report->setReportPeriodStart($startDate);
        $report->setReportPeriodEnd($endDate);
        $report->setReportDate(\DateTimeImmutable::createFromInterface($date));
        $report->setReporter($reporter);
        $report->setSupervisionData($supervisionData);
        $report->setProblemSummary($problemSummary);
        $report->setStatisticsData($statisticsData);
        $report->setRecommendations($this->generateRecommendations($problemSummary));
        $report->setReportContent($this->generateReportContent('日报', $supervisionData, $problemSummary, $statisticsData));

        $this->entityManager->persist($report);
        $this->entityManager->flush();

        return $report;
    }

    /**
     * 生成周报.
     */
    public function generateWeeklyReport(\DateTimeInterface $weekStart, string $reporter): SupervisionReport
    {
        $startDate = \DateTimeImmutable::createFromInterface($weekStart)->setTime(0, 0, 0);
        $endDate = \DateTimeImmutable::createFromInterface($weekStart)->modify('+6 days')->setTime(23, 59, 59);

        $supervisionData = $this->collectSupervisionData($startDate, $endDate);
        $problemSummary = $this->collectProblemSummary($startDate, $endDate);
        $statisticsData = $this->generateStatistics($startDate, $endDate);

        $report = new SupervisionReport();
        $report->setReportType('周报');
        $report->setReportTitle(sprintf(
            '%s至%s 培训监督周报',
            $startDate->format('Y年m月d日'),
            $endDate->format('Y年m月d日')
        ));
        $report->setReportPeriodStart($startDate);
        $report->setReportPeriodEnd($endDate);
        $report->setReportDate(new \DateTimeImmutable());
        $report->setReporter($reporter);
        $report->setSupervisionData($supervisionData);
        $report->setProblemSummary($problemSummary);
        $report->setStatisticsData($statisticsData);
        $report->setRecommendations($this->generateRecommendations($problemSummary));
        $report->setReportContent($this->generateReportContent('周报', $supervisionData, $problemSummary, $statisticsData));

        $this->entityManager->persist($report);
        $this->entityManager->flush();

        return $report;
    }

    /**
     * 生成月报.
     */
    public function generateMonthlyReport(int $year, int $month, string $reporter): SupervisionReport
    {
        $startDate = new \DateTimeImmutable(sprintf('%d-%02d-01 00:00:00', $year, $month));
        $endDate = $startDate->modify('last day of this month')->setTime(23, 59, 59);

        $supervisionData = $this->collectSupervisionData($startDate, $endDate);
        $problemSummary = $this->collectProblemSummary($startDate, $endDate);
        $statisticsData = $this->generateStatistics($startDate, $endDate);
        $trendAnalysis = $this->generateTrendAnalysis($startDate, $endDate);

        $report = new SupervisionReport();
        $report->setReportType('月报');
        $report->setReportTitle(sprintf('%d年%d月 培训监督月报', $year, $month));
        $report->setReportPeriodStart($startDate);
        $report->setReportPeriodEnd($endDate);
        $report->setReportDate(new \DateTimeImmutable());
        $report->setReporter($reporter);
        $report->setSupervisionData($supervisionData);
        $report->setProblemSummary($problemSummary);
        $report->setStatisticsData(array_merge($statisticsData, $trendAnalysis));
        $report->setRecommendations($this->generateRecommendations($problemSummary));
        $report->setReportContent($this->generateReportContent('月报', $supervisionData, $problemSummary, $statisticsData));

        $this->entityManager->persist($report);
        $this->entityManager->flush();

        return $report;
    }

    /**
     * 生成专项报告.
     */
    /**
     * @param array<string, mixed> $specialCriteria
     */
    public function generateSpecialReport(
        string $reportTitle,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        string $reporter,
        array $specialCriteria = [],
    ): SupervisionReport {
        $supervisionData = $this->collectSupervisionData($startDate, $endDate, $specialCriteria);
        $problemSummary = $this->collectProblemSummary($startDate, $endDate, $specialCriteria);
        $statisticsData = $this->generateStatistics($startDate, $endDate, $specialCriteria);

        $report = new SupervisionReport();
        $report->setReportType('专项报告');
        $report->setReportTitle($reportTitle);
        $report->setReportPeriodStart($startDate);
        $report->setReportPeriodEnd($endDate);
        $report->setReportDate(new \DateTimeImmutable());
        $report->setReporter($reporter);
        $report->setSupervisionData($supervisionData);
        $report->setProblemSummary($problemSummary);
        $report->setStatisticsData($statisticsData);
        $report->setRecommendations($this->generateRecommendations($problemSummary));
        $report->setReportContent($this->generateReportContent('专项报告', $supervisionData, $problemSummary, $statisticsData));

        $this->entityManager->persist($report);
        $this->entityManager->flush();

        return $report;
    }

    /**
     * 发布报告.
     */
    public function publishReport(SupervisionReport $report): void
    {
        $report->setReportStatus('已发布');
        $this->entityManager->flush();
    }

    /**
     * 归档报告.
     */
    public function archiveReport(SupervisionReport $report): void
    {
        $report->setReportStatus('已归档');
        $this->entityManager->flush();
    }

    /**
     * 收集监督数据.
     *
     * @param array<string, mixed> $criteria
     *
     * @return array<string, mixed>
     */
    private function collectSupervisionData(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        array $criteria = [],
    ): array {
        // 收集检查数据
        $inspections = $this->inspectionRepository->findByDateRange($startDate, $endDate);

        // 收集质量评估数据
        $assessments = $this->assessmentRepository->findByDateRange($startDate, $endDate);

        return [
            'inspection_count' => count($inspections),
            'assessment_count' => count($assessments),
            'inspections' => array_map(fn ($inspection) => [
                'id' => $inspection->getId(),
                'inspection_type' => $inspection->getInspectionType(),
                'inspection_date' => $inspection->getInspectionDate()->format('Y-m-d'),
                'inspection_results' => $inspection->getInspectionResults(),
                'score' => $inspection->getOverallScore(),
            ], $inspections),
            'assessments' => array_map(fn ($assessment) => [
                'id' => $assessment->getId(),
                'assessment_type' => $assessment->getAssessmentType(),
                'assessment_date' => $assessment->getAssessmentDate()->format('Y-m-d'),
                'total_score' => $assessment->getTotalScore(),
                'assessment_level' => $assessment->getAssessmentLevel(),
                'is_passed' => $assessment->isPassed(),
            ], $assessments),
        ];
    }

    /**
     * 收集问题汇总.
     *
     * @param array<string, mixed> $criteria
     *
     * @return array<string, mixed>
     */
    private function collectProblemSummary(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        array $criteria = [],
    ): array {
        $problems = $this->problemRepository->findByDateRange($startDate, $endDate);

        $summary = [
            'total_problems' => count($problems),
            'by_severity' => [],
            'by_status' => [],
            'by_category' => [],
            'problems' => [],
        ];

        foreach ($problems as $problem) {
            // 按严重程度统计
            $severity = $problem->getProblemSeverity();
            $summary['by_severity'][$severity] = ($summary['by_severity'][$severity] ?? 0) + 1;

            // 按状态统计
            $status = $problem->getProblemStatus();
            $summary['by_status'][$status] = ($summary['by_status'][$status] ?? 0) + 1;

            // 按类别统计
            $category = $problem->getProblemType();
            $summary['by_category'][$category] = ($summary['by_category'][$category] ?? 0) + 1;

            // 问题详情
            $summary['problems'][] = [
                'id' => $problem->getId(),
                'title' => $problem->getProblemTitle(),
                'category' => $category,
                'severity' => $severity,
                'status' => $status,
                'found_date' => $problem->getFoundDate()->format('Y-m-d'),
                'deadline' => $problem->getDeadline()->format('Y-m-d'),
            ];
        }

        return $summary;
    }

    /**
     * 生成统计数据.
     *
     * @param array<string, mixed> $criteria
     *
     * @return array<string, mixed>
     */
    private function generateStatistics(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        array $criteria = [],
    ): array {
        $inspections = $this->inspectionRepository->findByDateRange($startDate, $endDate);
        $assessments = $this->assessmentRepository->findByDateRange($startDate, $endDate);
        $problems = $this->problemRepository->findByDateRange($startDate, $endDate);

        // 计算平均分数
        $inspectionScores = array_values(array_filter(array_map(fn ($i) => $i->getOverallScore(), $inspections), 'is_numeric'));
        $assessmentScores = array_values(array_filter(array_map(fn ($a) => $a->getTotalScore(), $assessments), 'is_numeric'));

        $diffDays = $startDate->diff($endDate)->days;
        $periodDays = (false === $diffDays ? 0 : $diffDays) + 1;

        return [
            'period_days' => $periodDays,
            'inspection_stats' => [
                'total' => count($inspections),
                'average_score' => [] !== $inspectionScores ? round(array_sum($inspectionScores) / count($inspectionScores), 2) : 0,
                'pass_rate' => $this->calculatePassRate($inspections),
            ],
            'assessment_stats' => [
                'total' => count($assessments),
                'average_score' => [] !== $assessmentScores ? round(array_sum($assessmentScores) / count($assessmentScores), 2) : 0,
                'excellent_rate' => $this->calculateExcellentRate($assessments),
            ],
            'problem_stats' => [
                'total' => count($problems),
                'resolved' => count(array_filter($problems, fn ($p) => '已解决' === $p->getProblemStatus())),
                'pending' => count(array_filter($problems, fn ($p) => '待处理' === $p->getProblemStatus())),
                'in_progress' => count(array_filter($problems, fn ($p) => '处理中' === $p->getProblemStatus())),
                'resolution_rate' => $this->calculateResolutionRate($problems),
            ],
        ];
    }

    /**
     * 生成趋势分析.
     *
     * @return array<string, mixed>
     */
    private function generateTrendAnalysis(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        // 获取上一个周期的数据进行对比
        $diffDays = $startDate->diff($endDate)->days;
        $periodDays = (false === $diffDays ? 0 : $diffDays) + 1;
        $previousStart = \DateTimeImmutable::createFromInterface($startDate)->modify("-{$periodDays} days");
        $previousEnd = \DateTimeImmutable::createFromInterface($startDate)->modify('-1 day');

        $currentStats = $this->generateStatistics($startDate, $endDate);
        $previousStats = $this->generateStatistics($previousStart, $previousEnd);

        // 类型守卫：确保统计数据结构正确
        assert(isset($currentStats['inspection_stats']) && is_array($currentStats['inspection_stats']));
        assert(isset($previousStats['inspection_stats']) && is_array($previousStats['inspection_stats']));
        assert(isset($currentStats['problem_stats']) && is_array($currentStats['problem_stats']));
        assert(isset($previousStats['problem_stats']) && is_array($previousStats['problem_stats']));

        $currentInspectionTotal = $currentStats['inspection_stats']['total'] ?? 0;
        $previousInspectionTotal = $previousStats['inspection_stats']['total'] ?? 0;
        $currentInspectionAvgScore = $currentStats['inspection_stats']['average_score'] ?? 0;
        $previousInspectionAvgScore = $previousStats['inspection_stats']['average_score'] ?? 0;
        $currentProblemTotal = $currentStats['problem_stats']['total'] ?? 0;
        $previousProblemTotal = $previousStats['problem_stats']['total'] ?? 0;
        $currentResolutionRate = $currentStats['problem_stats']['resolution_rate'] ?? 0;
        $previousResolutionRate = $previousStats['problem_stats']['resolution_rate'] ?? 0;

        assert(is_numeric($currentInspectionTotal));
        assert(is_numeric($previousInspectionTotal));
        assert(is_numeric($currentInspectionAvgScore));
        assert(is_numeric($previousInspectionAvgScore));
        assert(is_numeric($currentProblemTotal));
        assert(is_numeric($previousProblemTotal));
        assert(is_numeric($currentResolutionRate));
        assert(is_numeric($previousResolutionRate));

        return [
            'trend_analysis' => [
                'inspection_trend' => $this->calculateTrend(
                    (float) $currentInspectionTotal,
                    (float) $previousInspectionTotal
                ),
                'score_trend' => $this->calculateTrend(
                    (float) $currentInspectionAvgScore,
                    (float) $previousInspectionAvgScore
                ),
                'problem_trend' => $this->calculateTrend(
                    (float) $currentProblemTotal,
                    (float) $previousProblemTotal
                ),
                'resolution_trend' => $this->calculateTrend(
                    (float) $currentResolutionRate,
                    (float) $previousResolutionRate
                ),
            ],
        ];
    }

    /**
     * 生成建议措施.
     *
     * @param array<string, mixed> $problemSummary
     *
     * @return array<int, string>
     */
    /**
     * @param array<string, mixed> $problemSummary
     * @return array<string, mixed>
     */
    private function generateRecommendations(array $problemSummary): array
    {
        $items = [];

        // 类型守卫：确保数据结构正确
        $bySeverity = $problemSummary['by_severity'] ?? [];
        $byStatus = $problemSummary['by_status'] ?? [];
        $totalProblems = $problemSummary['total_problems'] ?? 0;

        assert(is_array($bySeverity));
        assert(is_array($byStatus));
        assert(is_int($totalProblems));

        // 根据问题严重程度生成建议
        $severeCount = $bySeverity['严重'] ?? 0;
        assert(is_numeric($severeCount));
        if ((int) $severeCount > 0) {
            $items[] = '发现严重问题，建议立即组织专项整改，加强现场监督检查。';
        }

        // 根据问题数量生成建议
        if ($totalProblems > 10) {
            $items[] = '问题数量较多，建议加强培训机构管理，完善质量控制体系。';
        }

        // 根据问题状态生成建议
        $pendingCount = $byStatus['待处理'] ?? 0;
        assert(is_numeric($pendingCount));
        if ((int) $pendingCount > 5) {
            $items[] = '待处理问题较多，建议加快问题处理进度，明确责任人和时限。';
        }

        // 默认建议
        if ([] === $items) {
            $items[] = '继续保持良好的监督管理水平，持续改进培训质量。';
        }

        return ['items' => $items];
    }

    /**
     * 生成报告内容.
     *
     * @param array<string, mixed> $supervisionData
     * @param array<string, mixed> $problemSummary
     * @param array<string, mixed> $statisticsData
     */
    private function generateReportContent(
        string $reportType,
        array $supervisionData,
        array $problemSummary,
        array $statisticsData,
    ): string {
        $content = "# {$reportType}\n\n";

        $content .= "## 监督概况\n";
        $inspectionCount = $supervisionData['inspection_count'] ?? 0;
        $assessmentCount = $supervisionData['assessment_count'] ?? 0;
        assert(is_numeric($inspectionCount) && is_numeric($assessmentCount));
        $content .= sprintf(
            "本期共进行检查 %d 次，质量评估 %d 次。\n\n",
            (int) $inspectionCount,
            (int) $assessmentCount
        );

        $content .= "## 问题情况\n";
        $totalProblems = $problemSummary['total_problems'] ?? 0;
        assert(is_numeric($totalProblems));
        $content .= sprintf("发现问题 %d 个，其中：\n", (int) $totalProblems);

        $bySeverity = $problemSummary['by_severity'] ?? [];
        assert(is_array($bySeverity));
        foreach ($bySeverity as $severity => $count) {
            $countNumeric = is_numeric($count) ? (int) $count : 0;
            $content .= sprintf("- %s：%d 个\n", (string) $severity, $countNumeric);
        }
        $content .= "\n";

        $content .= "## 统计分析\n";
        $inspectionStats = $statisticsData['inspection_stats'] ?? [];
        $problemStats = $statisticsData['problem_stats'] ?? [];
        assert(is_array($inspectionStats) && is_array($problemStats));

        $avgScore = $inspectionStats['average_score'] ?? 0;
        $passRate = $inspectionStats['pass_rate'] ?? 0;
        $resolutionRate = $problemStats['resolution_rate'] ?? 0;
        assert(is_numeric($avgScore) && is_numeric($passRate) && is_numeric($resolutionRate));
        $content .= sprintf("- 检查平均分：%.2f 分\n", (float) $avgScore);
        $content .= sprintf("- 检查通过率：%.2f%%\n", (float) $passRate);
        $content .= sprintf("- 问题解决率：%.2f%%\n", (float) $resolutionRate);

        return $content;
    }

    /**
     * 计算通过率.
     */
    /**
     * @param array<int, SupervisionInspection> $inspections
     */
    private function calculatePassRate(array $inspections): float
    {
        if ([] === $inspections) {
            return 0.0;
        }

        $passCount = count(array_filter($inspections, fn ($i) => (float) ($i->getOverallScore() ?? 0) >= 60));

        return round(($passCount / count($inspections)) * 100, 2);
    }

    /**
     * 计算优秀率.
     */
    /**
     * @param array<int, QualityAssessment> $assessments
     */
    private function calculateExcellentRate(array $assessments): float
    {
        if ([] === $assessments) {
            return 0.0;
        }

        $excellentCount = count(array_filter($assessments, fn ($a) => $a->getTotalScore() >= 90));

        return round(($excellentCount / count($assessments)) * 100, 2);
    }

    /**
     * 计算解决率.
     */
    /**
     * @param array<int, ProblemTracking> $problems
     */
    private function calculateResolutionRate(array $problems): float
    {
        if ([] === $problems) {
            return 0.0;
        }

        $resolvedCount = count(array_filter($problems, fn ($p) => '已解决' === $p->getProblemStatus()));

        return round(($resolvedCount / count($problems)) * 100, 2);
    }

    /**
     * 计算趋势
     *
     * @return array<string, mixed>
     */
    private function calculateTrend(float $current, float $previous): array
    {
        if (0.0 === $previous) {
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
