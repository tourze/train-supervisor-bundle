<?php

namespace Tourze\TrainSupervisorBundle\Service;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\TrainSupervisorBundle\Entity\ProblemTracking;
use Tourze\TrainSupervisorBundle\Entity\SupervisionInspection;
use Tourze\TrainSupervisorBundle\Exception\InvalidProblemStatusException;
use Tourze\TrainSupervisorBundle\Repository\ProblemTrackingRepository;

/**
 * 问题跟踪服务
 * 负责问题的创建、跟踪、整改和验证管理.
 */
#[Autoconfigure(public: true)]
class ProblemTrackingService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProblemTrackingRepository $problemRepository,
    ) {
    }

    /**
     * 创建问题记录.
     *
     * @param array<int, string> $correctionMeasures
     */
    public function createProblem(
        SupervisionInspection $inspection,
        string $problemType,
        string $problemDescription,
        string $problemSeverity,
        \DateTimeInterface $correctionDeadline,
        string $responsiblePerson,
        array $correctionMeasures = [],
    ): ProblemTracking {
        $problem = new ProblemTracking();
        $problemTitle = sprintf('[%s] %s', $problemType, mb_substr($problemDescription, 0, 50));
        $problem->setInspection($inspection);
        $problem->setProblemTitle($problemTitle);
        $problem->setProblemType($problemType);
        $problem->setProblemDescription($problemDescription);
        $problem->setProblemSeverity($problemSeverity);
        $problem->setDiscoveryDate(new \DateTimeImmutable());
        $problem->setCorrectionDeadline($correctionDeadline);
        $problem->setResponsiblePerson($responsiblePerson);
        $problem->setCorrectionMeasures($correctionMeasures);
        $problem->setCorrectionStatus('待整改');

        $this->entityManager->persist($problem);
        $this->entityManager->flush();

        return $problem;
    }

    /**
     * 批量创建问题记录.
     *
     * @param array<int, array<string, mixed>> $problemsData
     *
     * @return array<int, ProblemTracking>
     */
    public function createProblemsFromInspection(
        SupervisionInspection $inspection,
        array $problemsData,
    ): array {
        $problems = [];

        foreach ($problemsData as $problemData) {
            assert(is_array($problemData));
            assert(isset($problemData['type']) && is_string($problemData['type']));
            assert(isset($problemData['description']) && is_string($problemData['description']));
            assert(isset($problemData['severity']) && is_string($problemData['severity']));
            assert(isset($problemData['deadline']) && $problemData['deadline'] instanceof \DateTimeInterface);
            assert(isset($problemData['responsible_person']) && is_string($problemData['responsible_person']));

            /** @var array<int, string> $measures */
            $measures = [];
            if (isset($problemData['measures'])) {
                assert(is_array($problemData['measures']));
                /** @var array<int, string> $measures */
                $measures = $problemData['measures'];
            }

            $problem = $this->createProblem(
                $inspection,
                $problemData['type'],
                $problemData['description'],
                $problemData['severity'],
                $problemData['deadline'],
                $problemData['responsible_person'],
                $measures
            );
            $problems[] = $problem;
        }

        return $problems;
    }

    /**
     * 开始整改.
     *
     * @param array<int, string> $measures
     */
    public function startCorrection(ProblemTracking $problem, array $measures = []): void
    {
        if ([] !== $measures) {
            $problem->setCorrectionMeasures($measures);
        }

        $problem->setCorrectionStatus('整改中');
        $this->entityManager->flush();
    }

    /**
     * 提交整改证据.
     *
     * @param array<string, mixed> $evidence
     */
    public function submitCorrectionEvidence(
        ProblemTracking $problem,
        array $evidence,
        ?\DateTimeInterface $correctionDate = null,
    ): void {
        $problem->setCorrectionEvidence($evidence);
        $problem->setCorrectionDate($correctionDate ?? new \DateTimeImmutable());
        $problem->setCorrectionStatus('已整改');

        $this->entityManager->flush();
    }

    /**
     * 验证整改结果.
     */
    public function verifyCorrection(
        ProblemTracking $problem,
        string $verificationResult,
        string $verifier,
        ?\DateTimeInterface $verificationDate = null,
    ): void {
        $problem->setVerificationResult($verificationResult);
        $problem->setVerifier($verifier);
        $problem->setVerificationDate($verificationDate ?? new \DateTimeImmutable());

        // 根据验证结果更新状态
        if ('通过' === $verificationResult) {
            $problem->setCorrectionStatus('已验证');
        } elseif ('不通过' === $verificationResult) {
            $problem->setCorrectionStatus('整改中');
        } else { // 部分通过
            $problem->setCorrectionStatus('整改中');
        }

        $this->entityManager->flush();
    }

    /**
     * 关闭问题.
     */
    public function closeProblem(ProblemTracking $problem, ?string $remarks = null): void
    {
        if ('通过' !== $problem->getVerificationResult()) {
            throw new InvalidProblemStatusException('只有验证通过的问题才能关闭');
        }

        $problem->setCorrectionStatus('已关闭');
        if ((bool) $remarks) {
            $problem->setRemarks($remarks);
        }

        $this->entityManager->flush();
    }

    /**
     * 重新打开问题.
     */
    public function reopenProblem(ProblemTracking $problem, string $reason): void
    {
        $problem->setCorrectionStatus('整改中');
        $problem->setRemarks($reason);

        $this->entityManager->flush();
    }

    /**
     * 延期整改.
     */
    public function extendDeadline(
        ProblemTracking $problem,
        \DateTimeInterface $newDeadline,
        string $reason,
    ): void {
        $problem->setCorrectionDeadline($newDeadline);
        $problem->setRemarks($reason);

        $this->entityManager->flush();
    }

    /**
     * 获取逾期问题.
     *
     * @return ProblemTracking[]
     */
    public function getOverdueProblems(): array
    {
        return $this->problemRepository->findOverdueProblems();
    }

    /**
     * 获取即将逾期的问题（3天内）.
     *
     * @return array<int, ProblemTracking>
     */
    public function getUpcomingOverdueProblems(int $days = 3): array
    {
        // 使用基础的查询方法获取即将逾期的问题
        $now = new \DateTime();
        $futureDate = new \DateTime("+{$days} days");

        $result = $this->problemRepository->createQueryBuilder('p')
            ->where('p.correctionDeadline BETWEEN :now AND :futureDate')
            ->andWhere('p.correctionStatus NOT IN (:completedStatuses)')
            ->setParameter('now', $now)
            ->setParameter('futureDate', $futureDate)
            ->setParameter('completedStatuses', ['已验证', '已关闭'])
            ->getQuery()
            ->getResult()
        ;

        assert(is_array($result));

        /** @var array<int, ProblemTracking> $result */
        return $result;
    }

    /**
     * 获取待处理问题.
     */
    /**
     * @return ProblemTracking[]
     */
    public function getPendingProblems(): array
    {
        return $this->problemRepository->findBy(['correctionStatus' => '待整改']);
    }

    /**
     * 获取处理中问题.
     */
    /**
     * @return ProblemTracking[]
     */
    public function getInProgressProblems(): array
    {
        return $this->problemRepository->findBy(['correctionStatus' => '整改中']);
    }

    /**
     * 获取已整改待验证问题.
     */
    /**
     * @return ProblemTracking[]
     */
    public function getCorrectedProblems(): array
    {
        return $this->problemRepository->findBy(['correctionStatus' => '已整改']);
    }

    /**
     * 获取已验证问题.
     */
    /**
     * @return ProblemTracking[]
     */
    public function getVerifiedProblems(): array
    {
        return $this->problemRepository->findBy(['correctionStatus' => '已验证']);
    }

    /**
     * 按责任人获取问题.
     *
     * @return array<int, ProblemTracking>
     */
    public function getProblemsByResponsiblePerson(string $responsiblePerson): array
    {
        return $this->problemRepository->findBy(['responsiblePerson' => $responsiblePerson]);
    }

    /**
     * 按严重程度获取问题.
     *
     * @return array<int, ProblemTracking>
     */
    public function getProblemsBySeverity(string $severity): array
    {
        return $this->problemRepository->findBy(['problemSeverity' => $severity]);
    }

    /**
     * 获取问题统计信息.
     *
     * @return array<string, mixed>
     */
    public function getProblemStatistics(
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
    ): array {
        $problems = $this->getProblemsForStatistics($startDate, $endDate);

        $statistics = $this->initializeStatistics(count($problems));
        $statistics = $this->calculateBasicStatistics($problems, $statistics);
        $statistics = $this->calculateTimeBasedStatistics($problems, $statistics);

        return $this->calculateResolutionRate($problems, $statistics);
    }

    /**
     * @return array<int, ProblemTracking>
     */
    private function getProblemsForStatistics(?\DateTimeInterface $startDate, ?\DateTimeInterface $endDate): array
    {
        if (null !== $startDate && null !== $endDate) {
            $result = $this->problemRepository->createQueryBuilder('p')
                ->where('p.discoveryDate BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate)
                ->getQuery()
                ->getResult()
            ;
            assert(is_array($result));

            /** @var array<int, ProblemTracking> $result */
            return $result;
        }

        return $this->problemRepository->findAll();
    }

    /**
     * @return array<string, mixed>
     */
    private function initializeStatistics(int $totalCount): array
    {
        return [
            'total' => $totalCount,
            'by_status' => [],
            'by_severity' => [],
            'by_type' => [],
            'overdue' => 0,
            'upcoming_overdue' => 0,
            'resolution_rate' => 0,
        ];
    }

    /**
     * @param array<int, ProblemTracking> $problems
     * @param array<string, mixed> $statistics
     *
     * @return array<string, mixed>
     */
    private function calculateBasicStatistics(array $problems, array $statistics): array
    {
        foreach ($problems as $problem) {
            assert($problem instanceof ProblemTracking);
            $statistics = $this->updateStatusStatistics($problem, $statistics);
            $statistics = $this->updateSeverityStatistics($problem, $statistics);
            $statistics = $this->updateTypeStatistics($problem, $statistics);
        }

        return $statistics;
    }

    /**
     * @param array<string, mixed> $statistics
     *
     * @return array<string, mixed>
     */
    private function updateStatusStatistics(ProblemTracking $problem, array $statistics): array
    {
        $status = $problem->getCorrectionStatus();
        assert(isset($statistics['by_status']));
        assert(is_array($statistics['by_status']));
        $currentCount = $statistics['by_status'][$status] ?? 0;
        assert(is_int($currentCount));
        $statistics['by_status'][$status] = $currentCount + 1;

        return $statistics;
    }

    /**
     * @param array<string, mixed> $statistics
     *
     * @return array<string, mixed>
     */
    private function updateSeverityStatistics(ProblemTracking $problem, array $statistics): array
    {
        $severity = $problem->getProblemSeverity();
        assert(isset($statistics['by_severity']));
        assert(is_array($statistics['by_severity']));
        $currentCount = $statistics['by_severity'][$severity] ?? 0;
        assert(is_int($currentCount));
        $statistics['by_severity'][$severity] = $currentCount + 1;

        return $statistics;
    }

    /**
     * @param array<string, mixed> $statistics
     *
     * @return array<string, mixed>
     */
    private function updateTypeStatistics(ProblemTracking $problem, array $statistics): array
    {
        $type = $problem->getProblemType();
        assert(isset($statistics['by_type']));
        assert(is_array($statistics['by_type']));
        $currentCount = $statistics['by_type'][$type] ?? 0;
        assert(is_int($currentCount));
        $statistics['by_type'][$type] = $currentCount + 1;

        return $statistics;
    }

    /**
     * @param array<int, ProblemTracking> $problems
     * @param array<string, mixed> $statistics
     *
     * @return array<string, mixed>
     */
    private function calculateTimeBasedStatistics(array $problems, array $statistics): array
    {
        $upcomingDeadline = new \DateTime('+3 days');
        $overdueCount = $statistics['overdue'] ?? 0;
        $upcomingOverdueCount = $statistics['upcoming_overdue'] ?? 0;
        assert(is_int($overdueCount));
        assert(is_int($upcomingOverdueCount));

        foreach ($problems as $problem) {
            assert($problem instanceof ProblemTracking);
            if ($problem->isOverdue()) {
                ++$overdueCount;
            } elseif ($problem->getCorrectionDeadline() <= $upcomingDeadline) {
                ++$upcomingOverdueCount;
            }
        }

        $statistics['overdue'] = $overdueCount;
        $statistics['upcoming_overdue'] = $upcomingOverdueCount;

        return $statistics;
    }

    /**
     * @param array<int, ProblemTracking> $problems
     * @param array<string, mixed> $statistics
     *
     * @return array<string, mixed>
     */
    private function calculateResolutionRate(array $problems, array $statistics): array
    {
        $resolvedCount = 0;

        foreach ($problems as $problem) {
            assert($problem instanceof ProblemTracking);
            if (in_array($problem->getCorrectionStatus(), ['已验证', '已关闭'], true)) {
                ++$resolvedCount;
            }
        }

        $total = $statistics['total'];
        assert(is_int($total));
        if ($total > 0) {
            $statistics['resolution_rate'] = round(($resolvedCount / $total) * 100, 2);
        }

        return $statistics;
    }

    /**
     * 生成问题跟踪报告.
     *
     * @return array<string, mixed>
     */
    public function generateTrackingReport(
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
    ): array {
        $statistics = $this->getProblemStatistics($startDate, $endDate);
        $overdueProblems = $this->getOverdueProblems();
        $upcomingOverdueProblems = $this->getUpcomingOverdueProblems();

        return [
            'summary' => [
                'total_problems' => $statistics['total_count'] ?? 0,
                'overdue_count' => count($overdueProblems),
                'upcoming_overdue_count' => count($upcomingOverdueProblems),
                'completion_rate' => $statistics['correction_rate'] ?? 0,
            ],
            'statistics' => $statistics,
            'problems' => [
                'overdue_problems' => array_map(fn ($p) => [
                    'id' => $p->getId(),
                    'description' => $p->getProblemDescription(),
                    'severity' => $p->getProblemSeverity(),
                    'responsible_person' => $p->getResponsiblePerson(),
                    'deadline' => $p->getCorrectionDeadline()->format('Y-m-d'),
                    'overdue_days' => $p->getRemainingDays() * -1,
                ], $overdueProblems),
                'upcoming_overdue_problems' => array_map(fn ($p) => [
                    'id' => $p->getId(),
                    'description' => $p->getProblemDescription(),
                    'severity' => $p->getProblemSeverity(),
                    'responsible_person' => $p->getResponsiblePerson(),
                    'deadline' => $p->getCorrectionDeadline()->format('Y-m-d'),
                    'remaining_days' => $p->getRemainingDays(),
                ], $upcomingOverdueProblems),
            ],
            'generatedAt' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * 发送逾期提醒.
     *
     * @return array<int, array<string, mixed>>
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
     *
     * @param array<int, mixed> $problemIds
     */
    public function batchUpdateStatus(array $problemIds, string $status): int
    {
        $updatedCount = 0;

        foreach ($problemIds as $problemId) {
            $problem = $this->problemRepository->find($problemId);
            if (null !== $problem) {
                assert($problem instanceof ProblemTracking);
                $problem->setCorrectionStatus($status);
                ++$updatedCount;
            }
        }

        $this->entityManager->flush();

        return $updatedCount;
    }

    /**
     * 批量分配责任人
     * 注意：此方法不考虑并发控制，仅适用于单用户操作场景.
     *
     * @param array<int, mixed> $problemIds
     */
    public function batchAssignResponsiblePerson(array $problemIds, string $responsiblePerson): int
    {
        $updatedCount = 0;

        foreach ($problemIds as $problemId) {
            $problem = $this->problemRepository->find($problemId);
            if (null !== $problem) {
                assert($problem instanceof ProblemTracking);
                $problem->setResponsiblePerson($responsiblePerson);
                ++$updatedCount;
            }
        }

        $this->entityManager->flush();

        return $updatedCount;
    }

    /**
     * 更新问题状态
     */
    public function updateProblemStatus(string $problemId, string $status): ProblemTracking
    {
        $problem = $this->problemRepository->find($problemId);
        if (null === $problem) {
            throw new InvalidProblemStatusException("问题不存在: {$problemId}");
        }

        assert($problem instanceof ProblemTracking);

        $problem->setCorrectionStatus($status);
        $this->entityManager->flush();

        return $problem;
    }

    /**
     * 按监督检查获取问题.
     *
     * @return array<int, ProblemTracking>
     */
    public function getProblemsByInspection(string $inspectionId): array
    {
        $result = $this->problemRepository->createQueryBuilder('p')
            ->leftJoin('p.inspection', 'i')
            ->where('i.id = :inspectionId')
            ->setParameter('inspectionId', $inspectionId)
            ->getQuery()
            ->getResult()
        ;

        assert(is_array($result));

        /** @var array<int, ProblemTracking> $result */
        return $result;
    }

    /**
     * 分配问题给负责人
     * 使用悲观锁和事务确保并发安全.
     */
    public function assignProblem(string $problemId, string $assignee): ProblemTracking
    {
        $this->entityManager->beginTransaction();

        try {
            // 使用悲观锁防止并发修改
            $problem = $this->entityManager->find(
                ProblemTracking::class,
                $problemId,
                LockMode::PESSIMISTIC_WRITE
            );

            if (null === $problem) {
                throw new InvalidProblemStatusException("问题不存在: {$problemId}");
            }

            // 验证问题状态是否允许重新分配
            $allowedStatuses = ['待整改', '整改中'];
            if (!in_array($problem->getCorrectionStatus(), $allowedStatuses, true)) {
                throw new InvalidProblemStatusException(sprintf("问题状态为'%s'，不允许重新分配负责人", $problem->getCorrectionStatus()));
            }

            // 检查是否重复分配给同一人
            if ($problem->getResponsiblePerson() === $assignee) {
                throw new InvalidProblemStatusException('问题已分配给该负责人，无需重复分配');
            }

            $problem->setResponsiblePerson($assignee);
            $this->entityManager->flush();
            $this->entityManager->commit();

            return $problem;
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * 导出问题数据.
     *
     * @param array<string, mixed> $filters
     *
     * @return array<string, string>
     */
    public function exportProblems(
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
        array $filters = [],
    ): array {
        if (null !== $startDate && null !== $endDate) {
            // 使用基础查询方法
            $qb = $this->problemRepository->createQueryBuilder('p')
                ->where('p.discoveryDate BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate)
            ;

            // 应用过滤条件
            foreach ($filters as $field => $value) {
                $qb->andWhere("p.{$field} = :{$field}")
                    ->setParameter($field, $value)
                ;
            }

            $problems = $qb->getQuery()->getResult();
            assert(is_array($problems));
            /** @var array<int, ProblemTracking> $problems */
        } else {
            $problems = $this->problemRepository->findBy($filters);
        }

        $csvContent = $this->generateCsvContent($problems);

        return [
            'content' => $csvContent,
            'mime_type' => 'text/csv; charset=utf-8',
        ];
    }

    /**
     * 生成CSV内容.
     *
     * @param array<int, ProblemTracking> $problems
     */
    private function generateCsvContent(array $problems): string
    {
        $output = "ID,检查ID,问题类型,问题描述,严重程度,整改期限,整改状态,负责人,整改日期,验证结果,验证日期,验证人,是否逾期,剩余天数,创建时间,更新时间\n";

        foreach ($problems as $problem) {
            assert($problem instanceof ProblemTracking);
            $output .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
                $problem->getId(),
                $problem->getInspection()->getId(),
                $problem->getProblemType(),
                $problem->getProblemDescription(),
                $problem->getProblemSeverity(),
                $problem->getCorrectionDeadline()->format('Y-m-d'),
                $problem->getCorrectionStatus(),
                $problem->getResponsiblePerson(),
                $problem->getCorrectionDate()?->format('Y-m-d') ?? '',
                $problem->getVerificationResult() ?? '',
                $problem->getVerificationDate()?->format('Y-m-d') ?? '',
                $problem->getVerifier() ?? '',
                $problem->isOverdue() ? '是' : '否',
                $problem->getRemainingDays(),
                $problem->getCreateTime()?->format('Y-m-d H:i:s') ?? '',
                $problem->getUpdateTime()?->format('Y-m-d H:i:s') ?? ''
            );
        }

        return $output;
    }
}
