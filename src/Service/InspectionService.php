<?php

namespace Tourze\TrainSupervisorBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\TrainSupervisorBundle\Entity\SupervisionInspection;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;
use Tourze\TrainSupervisorBundle\Repository\SupervisionInspectionRepository;
use Tourze\TrainSupervisorBundle\Repository\SupervisionPlanRepository;

/**
 * 检查服务
 * 负责监督检查的创建、更新和管理
 */
class InspectionService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SupervisionInspectionRepository $inspectionRepository,
        private readonly SupervisionPlanRepository $planRepository
    ) {}

    /**
     * 根据监督计划创建检查任务
     */
    public function createInspectionsFromPlan(SupervisionPlan $plan, \DateTime $date): array
    {
        $inspections = [];

        // 这里可以根据计划的监督范围和项目创建相应的检查任务
        // 暂时返回空数组，具体业务逻辑可以后续实现

        return $inspections;
    }

    /**
     * 获取计划中即将创建的检查任务（用于试运行模式）
     */
    public function getPlannedInspectionsFromPlan(SupervisionPlan $plan, \DateTime $date): array
    {
        $plannedInspections = [];

        // 模拟计划将要创建的检查任务
        foreach ($plan->getSupervisionScope() as $scope) {
            $plannedInspections[] = [
                'type' => '现场检查',
                'institution' => $scope,
                'date' => $date->format('Y-m-d')
            ];
        }

        return $plannedInspections;
    }

    /**
     * 创建检查任务
     */
    public function createInspection(array $inspectionData): SupervisionInspection
    {
        $inspection = new SupervisionInspection();

        // 设置基本信息
        if (isset($inspectionData['plan'])) {
            $inspection->setPlan($inspectionData['plan']);
        }
        if (isset($inspectionData['inspectionType'])) {
            $inspection->setInspectionType($inspectionData['inspectionType']);
        }
        if (isset($inspectionData['inspectionDate'])) {
            $inspection->setInspectionDate($inspectionData['inspectionDate']);
        }
        if (isset($inspectionData['inspector'])) {
            $inspection->setInspector($inspectionData['inspector']);
        }

        $this->entityManager->persist($inspection);
        $this->entityManager->flush();

        return $inspection;
    }

    /**
     * 更新检查任务
     */
    public function updateInspection(string $inspectionId, array $inspectionData): SupervisionInspection
    {
        $inspection = $this->inspectionRepository->find($inspectionId);
        if ($inspection === null) {
            throw new \InvalidArgumentException("检查任务不存在: {$inspectionId}");
        }

        // 更新检查信息
        if (isset($inspectionData['inspectionStatus'])) {
            $inspection->setInspectionStatus($inspectionData['inspectionStatus']);
        }
        if (isset($inspectionData['overallScore'])) {
            $inspection->setOverallScore($inspectionData['overallScore']);
        }
        if (isset($inspectionData['inspectionReport'])) {
            $inspection->setInspectionReport($inspectionData['inspectionReport']);
        }
        if (isset($inspectionData['foundProblems'])) {
            $inspection->setFoundProblems($inspectionData['foundProblems']);
        }

        $this->entityManager->flush();

        return $inspection;
    }

    /**
     * 执行监督检查
     */
    public function conductInspection(string $planId, string $institutionId, array $inspectionData): SupervisionInspection
    {
        $plan = $this->planRepository->find($planId);
        if ($plan === null) {
            throw new \InvalidArgumentException("监督计划不存在: {$planId}");
        }

        $inspection = new SupervisionInspection();
        $inspection->setPlan($plan);
        $inspection->setSupplierId($institutionId);
        $inspection->setInstitutionName($inspectionData['institutionName'] ?? '');
        $inspection->setInspectionType($inspectionData['inspectionType']);
        $inspection->setInspectionDate($inspectionData['inspectionDate']);
        $inspection->setInspector($inspectionData['inspector']);
        $inspection->setInspectionItems($inspectionData['inspectionItems'] ?? []);
        $inspection->setInspectionResults($inspectionData['inspectionResults'] ?? []);
        $inspection->setFoundProblems($inspectionData['foundProblems'] ?? []);
        $inspection->setInspectionStatus($inspectionData['inspectionStatus'] ?? '进行中');
        $inspection->setOverallScore($inspectionData['overallScore'] ?? null);
        $inspection->setInspectionReport($inspectionData['inspectionReport'] ?? null);
        $inspection->setRemarks($inspectionData['remarks'] ?? null);

        $this->entityManager->persist($inspection);
        $this->entityManager->flush();

        return $inspection;
    }

    /**
     * 更新检查结果
     */
    public function updateInspectionResults(string $inspectionId, array $results): SupervisionInspection
    {
        $inspection = $this->inspectionRepository->find($inspectionId);
        if ($inspection === null) {
            throw new \InvalidArgumentException("监督检查不存在: {$inspectionId}");
        }

        if (isset($results['inspectionResults'])) {
            $inspection->setInspectionResults($results['inspectionResults']);
        }
        if (isset($results['foundProblems'])) {
            $inspection->setFoundProblems($results['foundProblems']);
        }
        if (isset($results['overallScore'])) {
            $inspection->setOverallScore($results['overallScore']);
        }
        if (isset($results['inspectionReport'])) {
            $inspection->setInspectionReport($results['inspectionReport']);
        }
        if (isset($results['inspectionStatus'])) {
            $inspection->setInspectionStatus($results['inspectionStatus']);
        }
        if (isset($results['remarks'])) {
            $inspection->setRemarks($results['remarks']);
        }

        $this->entityManager->flush();

        return $inspection;
    }

    /**
     * 计算检查评分
     */
    public function calculateInspectionScore(string $inspectionId): float
    {
        $inspection = $this->inspectionRepository->find($inspectionId);
        if ($inspection === null) {
            throw new \InvalidArgumentException("监督检查不存在: {$inspectionId}");
        }

        $inspectionResults = $inspection->getInspectionResults();
        if (empty($inspectionResults)) {
            return 0.0;
        }

        $totalScore = 0.0;
        $totalWeight = 0.0;

        foreach ($inspectionResults as $result) {
            if (isset($result['score'], $result['weight'])) {
                $totalScore += $result['score'] * $result['weight'];
                $totalWeight += $result['weight'];
            }
        }

        return $totalWeight > 0 ? $totalScore / $totalWeight : 0.0;
    }

    /**
     * 生成检查报告
     */
    public function generateInspectionReport(string $inspectionId): array
    {
        $inspection = $this->inspectionRepository->find($inspectionId);
        if ($inspection === null) {
            throw new \InvalidArgumentException("监督检查不存在: {$inspectionId}");
        }

        return [
            'inspectionInfo' => [
                'id' => $inspection->getId(),
                'planName' => $inspection->getPlan()->getPlanName(),
                'institutionName' => $inspection->getInstitutionName(),
                'inspectionType' => $inspection->getInspectionType(),
                'inspectionDate' => $inspection->getInspectionDate()->format('Y-m-d'),
                'inspector' => $inspection->getInspector(),
                'status' => $inspection->getInspectionStatus(),
                'overallScore' => $inspection->getOverallScore(),
                'scoreLevel' => $inspection->getScoreLevel(),
                'isCompleted' => $inspection->isCompleted(),
                'hasProblems' => $inspection->hasProblems(),
                'problemCount' => $inspection->getProblemCount()
            ],
            'inspectionItems' => $inspection->getInspectionItems(),
            'inspectionResults' => $inspection->getInspectionResults(),
            'foundProblems' => $inspection->getFoundProblems(),
            'inspectionReport' => $inspection->getInspectionReport(),
            'remarks' => $inspection->getRemarks(),
            'generatedAt' => new \DateTime()
        ];
    }

    /**
     * 获取机构检查历史
     */
    public function getInspectionHistory(string $institutionId): array
    {
        return $this->inspectionRepository->findBy(['supplierId' => $institutionId]);
    }

    /**
     * 完成检查
     */
    public function completeInspection(string $inspectionId): SupervisionInspection
    {
        $inspection = $this->inspectionRepository->find($inspectionId);
        if ($inspection === null) {
            throw new \InvalidArgumentException("监督检查不存在: {$inspectionId}");
        }

        $inspection->setInspectionStatus('已完成');
        $this->entityManager->flush();

        return $inspection;
    }

    /**
     * 获取有问题的检查
     */
    public function getInspectionsWithProblems(): array
    {
        return $this->inspectionRepository->findInspectionsWithProblems();
    }

    /**
     * 按类型统计检查
     */
    public function getStatisticsByType(): array
    {
        return $this->inspectionRepository->countByType();
    }

    /**
     * 获取指定日期范围内的检查
     */
    public function getInspectionsByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->inspectionRepository->findByDateRange($startDate, $endDate);
    }

    /**
     * 获取已完成的检查
     */
    public function getCompletedInspections(): array
    {
        return $this->inspectionRepository->findCompletedInspections();
    }

    /**
     * 分析检查趋势
     */
    public function analyzeInspectionTrends(int $days = 30): array
    {
        $endDate = new \DateTime();
        $startDate = (clone $endDate)->modify("-{$days} days");

        $inspections = $this->getInspectionsByDateRange($startDate, $endDate);

        $trends = [];
        $problemTrends = [];
        $scoreTrends = [];

        foreach ($inspections as $inspection) {
            $date = $inspection->getInspectionDate()->format('Y-m-d');

            if (!isset($trends[$date])) {
                $trends[$date] = 0;
                $problemTrends[$date] = 0;
                $scoreTrends[$date] = [];
            }

            $trends[$date]++;

            if ($inspection->hasProblems()) {
                $problemTrends[$date]++;
            }

            if ($inspection->getOverallScore() !== null) {
                $scoreTrends[$date][] = $inspection->getOverallScore();
            }
        }

        // 计算平均分
        foreach ($scoreTrends as $date => $scores) {
            $scoreTrends[$date] = !empty($scores) ? array_sum($scores) / count($scores) : 0;
        }

        return [
            'period' => [
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
                'days' => $days
            ],
            'inspectionTrends' => $trends,
            'problemTrends' => $problemTrends,
            'scoreTrends' => $scoreTrends,
            'summary' => [
                'totalInspections' => count($inspections),
                'totalProblems' => array_sum($problemTrends),
                'averageScore' => !empty($scoreTrends) ? array_sum($scoreTrends) / count(array_filter($scoreTrends)) : 0
            ]
        ];
    }
}
