<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\TrainSupervisorBundle\Entity\SupervisionInspection;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;
use Tourze\TrainSupervisorBundle\Exception\InspectionNotFoundException;
use Tourze\TrainSupervisorBundle\Exception\SupervisionPlanNotFoundException;
use Tourze\TrainSupervisorBundle\Repository\SupervisionInspectionRepository;
use Tourze\TrainSupervisorBundle\Repository\SupervisionPlanRepository;

/**
 * 检查服务
 * 负责监督检查的创建、更新和管理.
 */
#[Autoconfigure(public: true)]
class InspectionService
{
    use InspectionFieldUpdaterTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SupervisionInspectionRepository $inspectionRepository,
        private readonly SupervisionPlanRepository $planRepository,
        private readonly InspectionTrendAnalyzer $trendAnalyzer,
    ) {
    }

    /**
     * 根据监督计划创建检查任务
     *
     * @return SupervisionInspection[]
     */
    public function createInspectionsFromPlan(SupervisionPlan $plan, \DateTime $date): array
    {
        return [];

        // 这里可以根据计划的监督范围和项目创建相应的检查任务
        // 暂时返回空数组，具体业务逻辑可以后续实现
    }

    /**
     * 获取计划中即将创建的检查任务（用于试运行模式）.
     *
     * @return array<int, array<string, string>>
     */
    public function getPlannedInspectionsFromPlan(SupervisionPlan $plan, \DateTime $date): array
    {
        $plannedInspections = [];

        // 模拟计划将要创建的检查任务
        foreach ($plan->getSupervisionScope() as $scope) {
            $plannedInspections[] = [
                'type' => '现场检查',
                'institution' => $scope,
                'date' => $date->format('Y-m-d'),
            ];
        }

        return $plannedInspections;
    }

    /**
     * 创建检查任务
     *
     * @param array<string, mixed> $inspectionData
     */
    public function createInspection(array $inspectionData): SupervisionInspection
    {
        $inspection = new SupervisionInspection();

        // 设置基本信息
        if (isset($inspectionData['plan'])) {
            $plan = $inspectionData['plan'];
            assert($plan instanceof SupervisionPlan);
            $inspection->setPlan($plan);
        }
        if (isset($inspectionData['inspectionType'])) {
            $inspectionType = $inspectionData['inspectionType'];
            assert(is_string($inspectionType));
            $inspection->setInspectionType($inspectionType);
        }
        if (isset($inspectionData['inspectionDate'])) {
            $inspectionDate = $inspectionData['inspectionDate'];
            assert($inspectionDate instanceof \DateTimeInterface);
            $inspection->setInspectionDate($inspectionDate);
        }
        if (isset($inspectionData['inspector'])) {
            $inspector = $inspectionData['inspector'];
            assert(is_string($inspector));
            $inspection->setInspector($inspector);
        }
        if (isset($inspectionData['institutionName'])) {
            $institutionName = $inspectionData['institutionName'];
            assert(is_string($institutionName));
            $inspection->setInstitutionName($institutionName);
        }
        if (isset($inspectionData['supplierId'])) {
            $supplierId = $inspectionData['supplierId'];
            assert(is_int($supplierId) || is_string($supplierId));
            $inspection->setSupplierId((int) $supplierId);
        }

        $this->entityManager->persist($inspection);
        $this->entityManager->flush();

        return $inspection;
    }

    /**
     * 更新检查任务
     *
     * @param array<string, mixed> $inspectionData
     */
    public function updateInspection(string $inspectionId, array $inspectionData): SupervisionInspection
    {
        $inspection = $this->inspectionRepository->find($inspectionId);
        if (null === $inspection) {
            throw new InspectionNotFoundException("检查任务不存在: {$inspectionId}");
        }

        assert($inspection instanceof SupervisionInspection);

        // 更新检查信息
        if (isset($inspectionData['inspectionStatus'])) {
            $inspectionStatus = $inspectionData['inspectionStatus'];
            assert(is_string($inspectionStatus));
            $inspection->setInspectionStatus($inspectionStatus);
        }
        if (isset($inspectionData['overallScore'])) {
            $overallScore = $inspectionData['overallScore'];
            assert(is_float($overallScore) || is_int($overallScore));
            $inspection->setOverallScore($overallScore);
        }
        if (isset($inspectionData['inspectionReport'])) {
            $inspectionReport = $inspectionData['inspectionReport'];
            assert(is_string($inspectionReport));
            $inspection->setInspectionReport($inspectionReport);
        }
        if (isset($inspectionData['foundProblems'])) {
            $foundProblems = $inspectionData['foundProblems'];
            assert(is_array($foundProblems));
            // 确保数组索引为字符串类型
            $stringKeyedFoundProblems = [];
            foreach ($foundProblems as $key => $value) {
                $stringKeyedFoundProblems[(string) $key] = $value;
            }
            $inspection->setFoundProblems($stringKeyedFoundProblems);
        }

        $this->entityManager->flush();

        return $inspection;
    }

    /**
     * 执行监督检查.
     *
     * @param array<string, mixed> $inspectionData
     */
    public function conductInspection(string $planId, string $institutionId, array $inspectionData): SupervisionInspection
    {
        $plan = $this->findPlan($planId);
        $inspection = $this->createInspectionFromData($plan, $institutionId, $inspectionData);

        $this->entityManager->persist($inspection);
        $this->entityManager->flush();

        return $inspection;
    }

    private function findPlan(string $planId): SupervisionPlan
    {
        $plan = $this->planRepository->find($planId);
        if (null === $plan) {
            throw new SupervisionPlanNotFoundException("监督计划不存在: {$planId}");
        }

        assert($plan instanceof SupervisionPlan);

        return $plan;
    }

    /**
     * @param array<string, mixed> $inspectionData
     */
    private function createInspectionFromData(SupervisionPlan $plan, string $institutionId, array $inspectionData): SupervisionInspection
    {
        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setSupplierId((int) $institutionId);

        $this->setBasicInspectionFields($inspection, $inspectionData);
        $this->setInspectionArrayFields($inspection, $inspectionData);
        $this->setOptionalInspectionFields($inspection, $inspectionData);

        return $inspection;
    }

    /**
     * @param array<string, mixed> $inspectionData
     */
    private function setBasicInspectionFields(SupervisionInspection $inspection, array $inspectionData): void
    {
        $institutionName = $inspectionData['institutionName'] ?? '';
        assert(is_string($institutionName));
        $inspection->setInstitutionName($institutionName);

        $inspectionType = $inspectionData['inspectionType'];
        assert(is_string($inspectionType));
        $inspection->setInspectionType($inspectionType);

        $inspectionDate = $inspectionData['inspectionDate'];
        assert($inspectionDate instanceof \DateTimeInterface);
        $inspection->setInspectionDate($inspectionDate);

        $inspector = $inspectionData['inspector'];
        assert(is_string($inspector));
        $inspection->setInspector($inspector);
    }

    /**
     * @param array<string, mixed> $inspectionData
     */
    private function setInspectionArrayFields(SupervisionInspection $inspection, array $inspectionData): void
    {
        $inspectionItems = $inspectionData['inspectionItems'] ?? [];
        assert(is_array($inspectionItems));
        $inspection->setInspectionItems($this->normalizeInspectionItems($inspectionItems));

        $inspectionResults = $inspectionData['inspectionResults'] ?? [];
        assert(is_array($inspectionResults));
        $inspection->setInspectionResults($this->normalizeStringKeyedArray($inspectionResults));

        $foundProblems = $inspectionData['foundProblems'] ?? [];
        assert(is_array($foundProblems));
        $inspection->setFoundProblems($this->normalizeStringKeyedArray($foundProblems));
    }

    /**
     * @param array<mixed, mixed> $items
     * @return array<int, string>
     */
    private function normalizeInspectionItems(array $items): array
    {
        $normalized = [];
        foreach ($items as $key => $value) {
            if (is_string($value)) {
                $stringValue = $value;
            } elseif (is_scalar($value)) {
                $stringValue = (string) $value;
            } else {
                $encoded = json_encode($value);
                $stringValue = (false !== $encoded) ? $encoded : '';
            }
            $normalized[(int) $key] = $stringValue;
        }

        return $normalized;
    }

    /**
     * @param array<mixed, mixed> $array
     * @return array<string, mixed>
     */
    private function normalizeStringKeyedArray(array $array): array
    {
        $normalized = [];
        foreach ($array as $key => $value) {
            $normalized[(string) $key] = $value;
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $inspectionData
     */
    private function setOptionalInspectionFields(SupervisionInspection $inspection, array $inspectionData): void
    {
        $inspectionStatus = $inspectionData['inspectionStatus'] ?? '进行中';
        assert(is_string($inspectionStatus));
        $inspection->setInspectionStatus($inspectionStatus);

        $overallScore = $inspectionData['overallScore'] ?? null;
        assert(is_float($overallScore) || is_int($overallScore) || is_null($overallScore));
        $inspection->setOverallScore($overallScore);

        $inspectionReport = $inspectionData['inspectionReport'] ?? null;
        assert(is_string($inspectionReport) || is_null($inspectionReport));
        $inspection->setInspectionReport($inspectionReport);

        $remarks = $inspectionData['remarks'] ?? null;
        assert(is_string($remarks) || is_null($remarks));
        $inspection->setRemarks($remarks);
    }

    /**
     * 更新检查结果.
     *
     * @param array<string, mixed> $results
     */
    public function updateInspectionResults(string $inspectionId, array $results): SupervisionInspection
    {
        $inspection = $this->inspectionRepository->find($inspectionId);
        if (null === $inspection) {
            throw new InspectionNotFoundException("监督检查不存在: {$inspectionId}");
        }

        assert($inspection instanceof SupervisionInspection);

        $this->updateInspectionFromArray($inspection, $results);
        $this->entityManager->flush();

        return $inspection;
    }

    /**
     * 从数组数据更新检查对象
     *
     * @param array<string, mixed> $results
     */
    private function updateInspectionFromArray(SupervisionInspection $inspection, array $results): void
    {
        $this->updateArrayField($inspection, $results, 'inspectionResults', 'setInspectionResults');
        $this->updateArrayField($inspection, $results, 'foundProblems', 'setFoundProblems');
        $this->updateNumericField($inspection, $results, 'overallScore', 'setOverallScore');
        $this->updateStringField($inspection, $results, 'inspectionReport', 'setInspectionReport');
        $this->updateStringField($inspection, $results, 'inspectionStatus', 'setInspectionStatus');
        $this->updateStringField($inspection, $results, 'remarks', 'setRemarks');
    }

    /**
     * 计算检查评分.
     */
    public function calculateInspectionScore(string $inspectionId): float
    {
        $inspection = $this->inspectionRepository->find($inspectionId);
        if (null === $inspection) {
            throw new InspectionNotFoundException("监督检查不存在: {$inspectionId}");
        }

        assert($inspection instanceof SupervisionInspection);

        $inspectionResults = $inspection->getInspectionResults();
        if ([] === $inspectionResults) {
            return 0.0;
        }

        $totalScore = 0.0;
        $totalWeight = 0.0;

        foreach ($inspectionResults as $result) {
            if (is_array($result) && isset($result['score'], $result['weight'])) {
                $score = $result['score'];
                $weight = $result['weight'];
                assert(is_numeric($score) && is_numeric($weight));
                $totalScore += (float) $score * (float) $weight;
                $totalWeight += (float) $weight;
            }
        }

        return $totalWeight > 0 ? $totalScore / $totalWeight : 0.0;
    }

    /**
     * 生成检查报告.
     *
     * @return array<string, mixed>
     */
    public function generateInspectionReport(string $inspectionId): array
    {
        $inspection = $this->inspectionRepository->find($inspectionId);
        if (null === $inspection) {
            throw new InspectionNotFoundException("监督检查不存在: {$inspectionId}");
        }

        assert($inspection instanceof SupervisionInspection);

        $plan = $inspection->getPlan();
        $inspectionDate = $inspection->getInspectionDate();

        return [
            'inspectionInfo' => [
                'id' => $inspection->getId(),
                'planName' => $plan->getPlanName(),
                'institutionName' => $inspection->getInstitutionName(),
                'inspectionType' => $inspection->getInspectionType(),
                'inspectionDate' => $inspectionDate->format('Y-m-d'),
                'inspector' => $inspection->getInspector(),
                'status' => $inspection->getInspectionStatus(),
                'overallScore' => $inspection->getOverallScore(),
                'scoreLevel' => $inspection->getScoreLevel(),
                'isCompleted' => $inspection->isCompleted(),
                'hasProblems' => $inspection->hasProblems(),
                'problemCount' => $inspection->getProblemCount(),
            ],
            'overallScore' => $inspection->getOverallScore(), // 添加顶层overallScore字段
            'inspectionItems' => $inspection->getInspectionItems(),
            'inspectionResults' => $inspection->getInspectionResults(),
            'foundProblems' => $inspection->getFoundProblems(),
            'inspectionReport' => $inspection->getInspectionReport(),
            'remarks' => $inspection->getRemarks(),
            'generatedAt' => new \DateTime(),
        ];
    }

    /**
     * 获取机构检查历史.
     *
     * @return SupervisionInspection[]
     */
    public function getInspectionHistory(string $institutionId): array
    {
        return $this->inspectionRepository->findBy(['supplierId' => $institutionId]);
    }

    /**
     * 完成检查.
     */
    public function completeInspection(string $inspectionId): SupervisionInspection
    {
        $inspection = $this->inspectionRepository->find($inspectionId);
        if (null === $inspection) {
            throw new InspectionNotFoundException("监督检查不存在: {$inspectionId}");
        }

        assert($inspection instanceof SupervisionInspection);

        $inspection->setInspectionStatus('已完成');
        $this->entityManager->flush();

        return $inspection;
    }

    /**
     * 获取有问题的检查.
     *
     * @return SupervisionInspection[]
     */
    public function getInspectionsWithProblems(): array
    {
        return $this->inspectionRepository->findInspectionsWithProblems();
    }

    /**
     * 按类型统计检查.
     *
     * @return array<string, int>
     */
    public function getStatisticsByType(): array
    {
        $rows = $this->inspectionRepository->countByType();
        $statistics = [];
        foreach ($rows as $row) {
            // 期望形如 ['inspectionType' => '现场检查', 'count' => 10]
            if (!is_array($row)) {
                continue;
            }
            $type = $row['inspectionType'] ?? null;
            $count = $row['count'] ?? 0;
            if (!is_string($type)) {
                continue;
            }
            $statistics[$type] = is_numeric($count) ? (int) $count : 0;
        }

        return $statistics;
    }

    /**
     * 获取指定日期范围内的检查.
     *
     * @return SupervisionInspection[]
     */
    public function getInspectionsByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->inspectionRepository->findByDateRange($startDate, $endDate);
    }

    /**
     * 获取已完成的检查.
     *
     * @return SupervisionInspection[]
     */
    public function getCompletedInspections(): array
    {
        return $this->inspectionRepository->findCompletedInspections();
    }

    /**
     * 分析检查趋势
     *
     * @return array<string, mixed>
     */
    public function analyzeInspectionTrends(int $days = 30): array
    {
        $dateRange = $this->createDateRange($days);
        $inspections = $this->getInspectionsByDateRange($dateRange['start'], $dateRange['end']);

        $trends = $this->trendAnalyzer->buildTrendData($inspections);

        return [
            'period' => [
                'startDate' => $dateRange['start']->format('Y-m-d'),
                'endDate' => $dateRange['end']->format('Y-m-d'),
                'days' => $days,
            ],
            'inspectionTrends' => $trends['inspections'],
            'problemTrends' => $trends['problems'],
            'scoreTrends' => $trends['scores'],
            'summary' => $this->trendAnalyzer->calculateTrendsSummary($inspections, $trends),
        ];
    }

    /**
     * @return array{start: \DateTime, end: \DateTime}
     */
    private function createDateRange(int $days): array
    {
        $endDate = new \DateTime();
        $startDate = (clone $endDate)->modify("-{$days} days");

        return ['start' => $startDate, 'end' => $endDate];
    }
}
