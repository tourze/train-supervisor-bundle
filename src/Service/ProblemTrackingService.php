<?php

namespace Tourze\TrainSupervisorBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\TrainSupervisorBundle\Entity\ProblemTracking;
use Tourze\TrainSupervisorBundle\Entity\SupervisionInspection;
use Tourze\TrainSupervisorBundle\Repository\ProblemTrackingRepository;

/**
 * 问题跟踪服务
 * 负责问题的创建、跟踪、整改和验证管理
 */
class ProblemTrackingService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProblemTrackingRepository $problemRepository,
    ) {
    }

    /**
     * 创建问题记录
     */
    public function createProblem(
        SupervisionInspection $inspection,
        string $problemType,
        string $problemDescription,
        string $problemSeverity,
        \DateTimeInterface $correctionDeadline,
        string $responsiblePerson,
        array $correctionMeasures = []
    ): ProblemTracking {
        $problem = new ProblemTracking();
        $problem->setInspection($inspection)
            ->setProblemType($problemType)
            ->setProblemDescription($problemDescription)
            ->setProblemSeverity($problemSeverity)
            ->setCorrectionDeadline($correctionDeadline)
            ->setResponsiblePerson($responsiblePerson)
            ->setCorrectionMeasures($correctionMeasures)
            ->setCorrectionStatus('待整改');

        $this->entityManager->persist($problem);
        $this->entityManager->flush();

        return $problem;
    }

    /**
     * 批量创建问题记录
     */
    public function createProblemsFromInspection(
        SupervisionInspection $inspection,
        array $problemsData
    ): array {
        $problems = [];

        foreach ($problemsData as $problemData) {
            $problem = $this->createProblem(
                $inspection,
                $problemData['type'],
                $problemData['description'],
                $problemData['severity'],
                $problemData['deadline'],
                $problemData['responsible_person'],
                $problemData['measures'] ?? []
            );
            $problems[] = $problem;
        }

        return $problems;
    }

    /**
     * 开始整改
     */
    public function startCorrection(ProblemTracking $problem, array $measures = []): void
    {
        if (!empty($measures)) {
            $problem->setCorrectionMeasures($measures);
        }
        
        $problem->setCorrectionStatus('整改中');
        $this->entityManager->flush();
    }

    /**
     * 提交整改证据
     */
    public function submitCorrectionEvidence(
        ProblemTracking $problem,
        array $evidence,
        ?\DateTimeInterface $correctionDate = null
    ): void {
        $problem->setCorrectionEvidence($evidence)
            ->setCorrectionDate($correctionDate ?? new \DateTime())
            ->setCorrectionStatus('已整改');

        $this->entityManager->flush();
    }

    /**
     * 验证整改结果
     */
    public function verifyCorrection(
        ProblemTracking $problem,
        string $verificationResult,
        string $verifier,
        ?\DateTimeInterface $verificationDate = null
    ): void {
        $problem->setVerificationResult($verificationResult)
            ->setVerifier($verifier)
            ->setVerificationDate($verificationDate ?? new \DateTime());

        // 根据验证结果更新状态
        if ($verificationResult === '通过') {
            $problem->setCorrectionStatus('已验证');
        } elseif ($verificationResult === '不通过') {
            $problem->setCorrectionStatus('整改中');
        } else { // 部分通过
            $problem->setCorrectionStatus('整改中');
        }

        $this->entityManager->flush();
    }

    /**
     * 关闭问题
     */
    public function closeProblem(ProblemTracking $problem, ?string $remarks = null): void
    {
        if ($problem->getVerificationResult() !== '通过') {
            throw new \InvalidArgumentException('只有验证通过的问题才能关闭');
        }

        $problem->setCorrectionStatus('已关闭');
        if ($remarks) {
            $problem->setRemarks($remarks);
        }

        $this->entityManager->flush();
    }

    /**
     * 重新打开问题
     */
    public function reopenProblem(ProblemTracking $problem, string $reason): void
    {
        $problem->setCorrectionStatus('整改中')
            ->setRemarks($reason);

        $this->entityManager->flush();
    }

    /**
     * 延期整改
     */
    public function extendDeadline(
        ProblemTracking $problem,
        \DateTimeInterface $newDeadline,
        string $reason
    ): void {
        $problem->setCorrectionDeadline($newDeadline)
            ->setRemarks($reason);

        $this->entityManager->flush();
    }

    /**
     * 获取逾期问题
     */
    public function getOverdueProblems(): array
    {
        return $this->problemRepository->findOverdueProblems();
    }

    /**
     * 获取即将逾期的问题（3天内）
     */
    public function getUpcomingOverdueProblems(int $days = 3): array
    {
        $deadline = new \DateTime("+{$days} days");
        return $this->problemRepository->findUpcomingOverdueProblems($deadline);
    }

    /**
     * 获取待处理问题
     */
    public function getPendingProblems(): array
    {
        return $this->problemRepository->findBy(['correctionStatus' => '待整改']);
    }

    /**
     * 获取处理中问题
     */
    public function getInProgressProblems(): array
    {
        return $this->problemRepository->findBy(['correctionStatus' => '整改中']);
    }

    /**
     * 获取已整改待验证问题
     */
    public function getCorrectedProblems(): array
    {
        return $this->problemRepository->findBy(['correctionStatus' => '已整改']);
    }

    /**
     * 获取已验证问题
     */
    public function getVerifiedProblems(): array
    {
        return $this->problemRepository->findBy(['correctionStatus' => '已验证']);
    }

    /**
     * 按责任人获取问题
     */
    public function getProblemsByResponsiblePerson(string $responsiblePerson): array
    {
        return $this->problemRepository->findBy(['responsiblePerson' => $responsiblePerson]);
    }

    /**
     * 按严重程度获取问题
     */
    public function getProblemsBySeverity(string $severity): array
    {
        return $this->problemRepository->findBy(['problemSeverity' => $severity]);
    }

    /**
     * 获取问题统计信息
     */
    public function getProblemStatistics(
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): array {
        $criteria = [];
        if ($startDate && $endDate) {
            $problems = $this->problemRepository->findByDateRange($startDate, $endDate);
        } else {
            $problems = $this->problemRepository->findAll();
        }

        $statistics = [
            'total' => count($problems),
            'by_status' => [],
            'by_severity' => [],
            'by_type' => [],
            'overdue' => 0,
            'upcoming_overdue' => 0,
            'resolution_rate' => 0,
        ];

        $resolvedCount = 0;
        $now = new \DateTime();
        $upcomingDeadline = new \DateTime('+3 days');

        foreach ($problems as $problem) {
            // 按状态统计
            $status = $problem->getCorrectionStatus();
            $statistics['by_status'][$status] = ($statistics['by_status'][$status] ?? 0) + 1;

            // 按严重程度统计
            $severity = $problem->getProblemSeverity();
            $statistics['by_severity'][$severity] = ($statistics['by_severity'][$severity] ?? 0) + 1;

            // 按类型统计
            $type = $problem->getProblemType();
            $statistics['by_type'][$type] = ($statistics['by_type'][$type] ?? 0) + 1;

            // 逾期统计
            if ($problem->isOverdue()) {
                $statistics['overdue']++;
            }

            // 即将逾期统计
            if ($problem->getCorrectionDeadline() <= $upcomingDeadline && !$problem->isOverdue()) {
                $statistics['upcoming_overdue']++;
            }

            // 解决率统计
            if (in_array($status, ['已验证', '已关闭'])) {
                $resolvedCount++;
            }
        }

        // 计算解决率
        if ($statistics['total'] > 0) {
            $statistics['resolution_rate'] = round(($resolvedCount / $statistics['total']) * 100, 2);
        }

        return $statistics;
    }

    /**
     * 生成问题跟踪报告
     */
    public function generateTrackingReport(
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): array {
        $statistics = $this->getProblemStatistics($startDate, $endDate);
        $overdueProblems = $this->getOverdueProblems();
        $upcomingOverdueProblems = $this->getUpcomingOverdueProblems();

        return [
            'statistics' => $statistics,
            'overdue_problems' => array_map(fn($p) => [
                'id' => $p->getId(),
                'description' => $p->getProblemDescription(),
                'severity' => $p->getProblemSeverity(),
                'responsible_person' => $p->getResponsiblePerson(),
                'deadline' => $p->getCorrectionDeadline()->format('Y-m-d'),
                'overdue_days' => $p->getRemainingDays() * -1,
            ], $overdueProblems),
            'upcoming_overdue_problems' => array_map(fn($p) => [
                'id' => $p->getId(),
                'description' => $p->getProblemDescription(),
                'severity' => $p->getProblemSeverity(),
                'responsible_person' => $p->getResponsiblePerson(),
                'deadline' => $p->getCorrectionDeadline()->format('Y-m-d'),
                'remaining_days' => $p->getRemainingDays(),
            ], $upcomingOverdueProblems),
        ];
    }

    /**
     * 发送逾期提醒
     */
    public function sendOverdueReminders(): array
    {
        $overdueProblems = $this->getOverdueProblems();
        $upcomingOverdueProblems = $this->getUpcomingOverdueProblems();
        
        $reminders = [];

        // 逾期问题提醒
        foreach ($overdueProblems as $problem) {
            $reminders[] = [
                'type' => 'overdue',
                'problem_id' => $problem->getId(),
                'responsible_person' => $problem->getResponsiblePerson(),
                'message' => sprintf(
                    '问题"%s"已逾期%d天，请立即处理',
                    $problem->getProblemDescription(),
                    abs($problem->getRemainingDays())
                ),
            ];
        }

        // 即将逾期问题提醒
        foreach ($upcomingOverdueProblems as $problem) {
            $reminders[] = [
                'type' => 'upcoming_overdue',
                'problem_id' => $problem->getId(),
                'responsible_person' => $problem->getResponsiblePerson(),
                'message' => sprintf(
                    '问题"%s"将在%d天后到期，请及时处理',
                    $problem->getProblemDescription(),
                    $problem->getRemainingDays()
                ),
            ];
        }

        return $reminders;
    }

    /**
     * 批量更新问题状态
     */
    public function batchUpdateStatus(array $problemIds, string $status): int
    {
        $updatedCount = 0;
        
        foreach ($problemIds as $problemId) {
            $problem = $this->problemRepository->find($problemId);
            if ($problem) {
                $problem->setCorrectionStatus($status);
                $updatedCount++;
            }
        }

        $this->entityManager->flush();
        return $updatedCount;
    }

    /**
     * 批量分配责任人
     */
    public function batchAssignResponsiblePerson(array $problemIds, string $responsiblePerson): int
    {
        $updatedCount = 0;
        
        foreach ($problemIds as $problemId) {
            $problem = $this->problemRepository->find($problemId);
            if ($problem) {
                $problem->setResponsiblePerson($responsiblePerson);
                $updatedCount++;
            }
        }

        $this->entityManager->flush();
        return $updatedCount;
    }

    /**
     * 导出问题数据
     */
    public function exportProblems(
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
        array $filters = []
    ): array {
        if ($startDate && $endDate) {
            $problems = $this->problemRepository->findByDateRange($startDate, $endDate, $filters);
        } else {
            $problems = $this->problemRepository->findBy($filters);
        }

        return array_map(fn($problem) => [
            'id' => $problem->getId(),
            'inspection_id' => $problem->getInspection()->getId(),
            'problem_type' => $problem->getProblemType(),
            'problem_description' => $problem->getProblemDescription(),
            'problem_severity' => $problem->getProblemSeverity(),
            'correction_deadline' => $problem->getCorrectionDeadline()->format('Y-m-d'),
            'correction_status' => $problem->getCorrectionStatus(),
            'responsible_person' => $problem->getResponsiblePerson(),
            'correction_date' => $problem->getCorrectionDate()?->format('Y-m-d'),
            'verification_result' => $problem->getVerificationResult(),
            'verification_date' => $problem->getVerificationDate()?->format('Y-m-d'),
            'verifier' => $problem->getVerifier(),
            'is_overdue' => $problem->isOverdue(),
            'remaining_days' => $problem->getRemainingDays(),
            'create_time' => $problem->getCreateTime()?->format('Y-m-d H:i:s'),
            'update_time' => $problem->getUpdateTime()?->format('Y-m-d H:i:s'),
        ], $problems);
    }
} 