<?php

namespace Tourze\TrainSupervisorBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\TrainSupervisorBundle\Entity\QualityAssessment;
use Tourze\TrainSupervisorBundle\Exception\QualityAssessmentNotFoundException;
use Tourze\TrainSupervisorBundle\Repository\QualityAssessmentRepository;

/**
 * 质量评估服务
 * 负责培训机构和课程的质量评估管理.
 */
#[Autoconfigure(public: true)]
class QualityAssessmentService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly QualityAssessmentRepository $assessmentRepository,
    ) {
    }

    /**
     * 评估培训机构.
     *
     * @param array<string, mixed> $criteria
     */
    public function assessInstitution(string $institutionId, array $criteria): QualityAssessment
    {
        $assessment = new QualityAssessment();
        $assessment->setAssessmentType('机构评估');
        $assessment->setTargetId($institutionId);

        $targetName = $criteria['targetName'];
        assert(is_string($targetName));
        $assessment->setTargetName($targetName);

        $criteriaValue = $criteria['criteria'];
        assert(is_string($criteriaValue));
        $assessment->setAssessmentCriteria($criteriaValue);

        $items = $criteria['items'] ?? [];
        assert(is_array($items));
        /** @var array<string, mixed> $validatedItems */
        $validatedItems = $this->validateArrayWithStringKeys($items);
        $assessment->setAssessmentItems($validatedItems);

        $scores = $criteria['scores'] ?? [];
        assert(is_array($scores));
        /** @var array<string, mixed> $validatedScores */
        $validatedScores = $this->validateArrayWithStringKeys($scores);
        $assessment->setAssessmentScores($validatedScores);

        $totalScore = $this->calculateTotalScore($validatedScores);
        $assessment->setTotalScore($totalScore);
        $assessment->setAssessmentLevel($this->calculateAssessmentLevel($totalScore));

        $comments = $criteria['comments'] ?? [];
        assert(is_array($comments));
        /** @var array<string, mixed> $validatedComments */
        $validatedComments = $this->validateArrayWithStringKeys($comments);
        $assessment->setAssessmentComments($validatedComments);

        $assessor = $criteria['assessor'];
        assert(is_string($assessor));
        $assessment->setAssessor($assessor);

        $assessmentDate = $criteria['assessmentDate'] ?? new \DateTimeImmutable();
        if ($assessmentDate instanceof \DateTime) {
            $assessmentDate = \DateTimeImmutable::createFromMutable($assessmentDate);
        }
        assert($assessmentDate instanceof \DateTimeInterface);
        $assessment->setAssessmentDate($assessmentDate);

        $status = $criteria['status'] ?? '进行中';
        assert(is_string($status));
        $assessment->setAssessmentStatus($status);

        $remarks = $criteria['remarks'] ?? null;
        assert(is_string($remarks) || null === $remarks);
        $assessment->setRemarks($remarks);

        $this->entityManager->persist($assessment);
        $this->entityManager->flush();

        return $assessment;
    }

    /**
     * 评估培训课程.
     *
     * @param array<string, mixed> $criteria
     */
    public function assessCourse(string $courseId, array $criteria): QualityAssessment
    {
        $assessment = new QualityAssessment();
        $assessment->setAssessmentType('课程评估');
        $assessment->setTargetId($courseId);

        $targetName = $criteria['targetName'];
        assert(is_string($targetName));
        $assessment->setTargetName($targetName);

        $criteriaValue = $criteria['criteria'];
        assert(is_string($criteriaValue));
        $assessment->setAssessmentCriteria($criteriaValue);

        $items = $criteria['items'] ?? [];
        assert(is_array($items));
        /** @var array<string, mixed> $validatedItems */
        $validatedItems = $this->validateArrayWithStringKeys($items);
        $assessment->setAssessmentItems($validatedItems);

        $scores = $criteria['scores'] ?? [];
        assert(is_array($scores));
        /** @var array<string, mixed> $validatedScores */
        $validatedScores = $this->validateArrayWithStringKeys($scores);
        $assessment->setAssessmentScores($validatedScores);

        $totalScore = $this->calculateTotalScore($validatedScores);
        $assessment->setTotalScore($totalScore);
        $assessment->setAssessmentLevel($this->calculateAssessmentLevel($totalScore));

        $comments = $criteria['comments'] ?? [];
        assert(is_array($comments));
        /** @var array<string, mixed> $validatedComments */
        $validatedComments = $this->validateArrayWithStringKeys($comments);
        $assessment->setAssessmentComments($validatedComments);

        $assessor = $criteria['assessor'];
        assert(is_string($assessor));
        $assessment->setAssessor($assessor);

        $assessmentDate = $criteria['assessmentDate'] ?? new \DateTimeImmutable();
        if ($assessmentDate instanceof \DateTime) {
            $assessmentDate = \DateTimeImmutable::createFromMutable($assessmentDate);
        }
        assert($assessmentDate instanceof \DateTimeInterface);
        $assessment->setAssessmentDate($assessmentDate);

        $status = $criteria['status'] ?? '进行中';
        assert(is_string($status));
        $assessment->setAssessmentStatus($status);

        $remarks = $criteria['remarks'] ?? null;
        assert(is_string($remarks) || null === $remarks);
        $assessment->setRemarks($remarks);

        $this->entityManager->persist($assessment);
        $this->entityManager->flush();

        return $assessment;
    }

    /**
     * 计算评估等级.
     */
    public function calculateAssessmentLevel(float $score): string
    {
        if ($score >= 90) {
            return '优秀';
        }
        if ($score >= 80) {
            return '良好';
        }
        if ($score >= 70) {
            return '合格';
        }

        return '不合格';
    }

    /**
     * 生成评估报告.
     *
     * @return array<string, mixed>
     */
    public function generateAssessmentReport(string $assessmentId): array
    {
        $assessment = $this->assessmentRepository->find($assessmentId);
        if (null === $assessment) {
            throw new QualityAssessmentNotFoundException("质量评估不存在: {$assessmentId}");
        }

        assert($assessment instanceof QualityAssessment);

        $assessmentDate = $assessment->getAssessmentDate();

        return [
            'assessmentInfo' => [
                'id' => $assessment->getId(),
                'type' => $assessment->getAssessmentType(),
                'targetName' => $assessment->getTargetName(),
                'criteria' => $assessment->getAssessmentCriteria(),
                'totalScore' => $assessment->getTotalScore(),
                'level' => $assessment->getAssessmentLevel(),
                'assessor' => $assessment->getAssessor(),
                'assessmentDate' => $assessmentDate->format('Y-m-d'),
                'status' => $assessment->getAssessmentStatus(),
                'isCompleted' => $assessment->isCompleted(),
                'isPassed' => $assessment->isPassed(),
                'itemCount' => $assessment->getItemCount(),
                'averageScore' => $assessment->getAverageScore(),
            ],
            'assessmentItems' => $assessment->getAssessmentItems(),
            'assessmentScores' => $assessment->getAssessmentScores(),
            'assessmentComments' => $assessment->getAssessmentComments(),
            'remarks' => $assessment->getRemarks(),
            'generatedAt' => new \DateTimeImmutable(),
        ];
    }

    /**
     * 计算总分.
     *
     * @param array<string, mixed> $scores
     */
    private function calculateTotalScore(array $scores): float
    {
        if ([] === $scores) {
            return 0.0;
        }

        $totalScore = 0.0;
        $totalWeight = 0.0;

        foreach ($scores as $score) {
            if (is_array($score)) {
                $value = $score['value'] ?? 0;
                $weight = $score['weight'] ?? 1;
                assert(is_numeric($value) && is_numeric($weight));
                $totalScore += (float) $value * (float) $weight;
                $totalWeight += (float) $weight;
            }
        }

        return $totalWeight > 0 ? $totalScore / $totalWeight : 0.0;
    }

    /**
     * 完成评估.
     */
    public function completeAssessment(string $assessmentId): QualityAssessment
    {
        $assessment = $this->assessmentRepository->find($assessmentId);
        if (null === $assessment) {
            throw new QualityAssessmentNotFoundException("质量评估不存在: {$assessmentId}");
        }

        assert($assessment instanceof QualityAssessment);

        $assessment->setAssessmentStatus('已完成');
        $this->entityManager->flush();

        return $assessment;
    }

    /**
     * 获取已完成的评估.
     *
     * @return QualityAssessment[]
     */
    public function getCompletedAssessments(): array
    {
        return $this->assessmentRepository->findCompletedAssessments();
    }

    /**
     * 按类型获取评估.
     *
     * @return QualityAssessment[]
     */
    public function getAssessmentsByType(string $type): array
    {
        return $this->assessmentRepository->findByType($type);
    }

    /**
     * 获取指定对象的评估记录.
     *
     * @return QualityAssessment[]
     */
    public function getAssessmentsByTarget(string $targetId): array
    {
        return $this->assessmentRepository->findByTarget($targetId);
    }

    /**
     * 按等级统计评估.
     *
     * @return array<string, int>
     */
    public function getStatisticsByLevel(): array
    {
        $rows = $this->assessmentRepository->countByLevel();
        $statistics = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $level = $row['assessmentLevel'] ?? null;
            $countRaw = $row['count'] ?? 0;
            if (!is_string($level)) {
                continue;
            }
            $statistics[$level] = is_numeric($countRaw) ? (int) $countRaw : 0;
        }

        return $statistics;
    }

    /**
     * 获取不合格的评估.
     *
     * @return QualityAssessment[]
     */
    public function getFailedAssessments(): array
    {
        return $this->assessmentRepository->findFailedAssessments();
    }

    /**
     * 获取平均分.
     */
    public function getAverageScore(): float
    {
        return $this->assessmentRepository->getAverageScore();
    }

    /**
     * 获取评估统计信息.
     *
     * @return array<string, mixed>
     */
    public function getAssessmentStatistics(?\DateTime $startDate = null, ?\DateTime $endDate = null, ?string $institutionId = null): array
    {
        $assessments = $this->getFilteredAssessments($startDate, $endDate, $institutionId);

        if ([] === $assessments) {
            return $this->getEmptyStatistics();
        }

        $scores = $this->extractScores($assessments);
        if ([] === $scores) {
            return $this->getEmptyStatistics();
        }

        $gradeStatistics = $this->calculateGradeStatistics($scores);
        $typeStatistics = $this->calculateTypeStatistics($assessments);
        $institutionStatistics = $this->calculateInstitutionStatistics($assessments);

        return [
            'total_assessments' => count($assessments),
            'average_score' => array_sum($scores) / count($scores),
            'max_score' => max($scores),
            'min_score' => min($scores),
            ...$gradeStatistics,
            'by_type' => $typeStatistics,
            'by_institution' => $institutionStatistics,
            'trends' => [],
        ];
    }

    /**
     * @return QualityAssessment[]
     */
    private function getFilteredAssessments(?\DateTime $startDate, ?\DateTime $endDate, ?string $institutionId): array
    {
        $assessments = $this->assessmentRepository->findByDateRange(
            $startDate ?? new \DateTimeImmutable('-1 year'),
            $endDate ?? new \DateTimeImmutable()
        );

        if (null !== $institutionId) {
            $assessments = array_filter(
                $assessments,
                fn ($assessment) => $assessment->getTargetId() === $institutionId
            );
        }

        return $assessments;
    }

    /**
     * @return array<string, mixed>
     */
    private function getEmptyStatistics(): array
    {
        return [
            'total_assessments' => 0,
            'average_score' => 0,
            'max_score' => 0,
            'min_score' => 0,
            'excellent_rate' => 0,
            'good_rate' => 0,
            'pass_rate' => 0,
            'by_type' => [],
            'by_institution' => [],
            'trends' => [],
        ];
    }

    /**
     * @param QualityAssessment[] $assessments
     * @return float[]
     */
    private function extractScores(array $assessments): array
    {
        return array_map(fn ($assessment) => $assessment->getTotalScore(), $assessments);
    }

    /**
     * @param float[] $scores
     * @return array<string, float>
     */
    private function calculateGradeStatistics(array $scores): array
    {
        $totalCount = count($scores);
        $excellentCount = count(array_filter($scores, fn ($score) => $score >= 90));
        $goodCount = count(array_filter($scores, fn ($score) => $score >= 80 && $score < 90));
        $passCount = count(array_filter($scores, fn ($score) => $score >= 70));

        return [
            'excellent_rate' => ($excellentCount / $totalCount) * 100,
            'good_rate' => ($goodCount / $totalCount) * 100,
            'pass_rate' => ($passCount / $totalCount) * 100,
        ];
    }

    /**
     * @param QualityAssessment[] $assessments
     * @return array<string, array<string, mixed>>
     */
    private function calculateTypeStatistics(array $assessments): array
    {
        return $this->calculateGroupedStatistics(
            $assessments,
            fn ($assessment) => $assessment->getAssessmentType()
        );
    }

    /**
     * @param QualityAssessment[] $assessments
     * @return array<string, array<string, mixed>>
     */
    private function calculateInstitutionStatistics(array $assessments): array
    {
        $statistics = $this->calculateGroupedStatistics(
            $assessments,
            fn ($assessment) => $assessment->getTargetName()
        );

        arsort($statistics);

        return array_slice($statistics, 0, 10, true);
    }

    /**
     * @param QualityAssessment[] $assessments
     * @param callable(QualityAssessment): string $groupKeyExtractor
     * @return array<string, array<string, mixed>>
     */
    private function calculateGroupedStatistics(array $assessments, callable $groupKeyExtractor): array
    {
        $grouped = [];

        foreach ($assessments as $assessment) {
            $groupKey = $groupKeyExtractor($assessment);

            if (!isset($grouped[$groupKey])) {
                $grouped[$groupKey] = ['count' => 0, 'total_score' => 0, 'pass_count' => 0];
            }

            $grouped[$groupKey] = $this->updateGroupStatistics($grouped[$groupKey], $assessment);
        }

        return $this->finalizeGroupedStatistics($grouped);
    }

    /**
     * @param array<string, mixed> $groupData
     * @return array<string, mixed>
     */
    private function updateGroupStatistics(array $groupData, QualityAssessment $assessment): array
    {
        $count = $groupData['count'];
        assert(is_int($count));
        $groupData['count'] = $count + 1;

        $totalScore = $groupData['total_score'];
        assert(is_numeric($totalScore));
        $groupData['total_score'] = (float) $totalScore + $assessment->getTotalScore();

        if ($assessment->getTotalScore() >= 70) {
            $passCount = $groupData['pass_count'];
            assert(is_int($passCount));
            $groupData['pass_count'] = $passCount + 1;
        }

        return $groupData;
    }

    /**
     * @param array<string, array<string, mixed>> $grouped
     * @return array<string, array<string, mixed>>
     */
    private function finalizeGroupedStatistics(array $grouped): array
    {
        foreach ($grouped as $key => $data) {
            assert(is_array($data));
            $totalScore = $data['total_score'];
            $count = $data['count'];
            $passCount = $data['pass_count'];
            assert(is_numeric($totalScore) && is_numeric($count) && is_numeric($passCount));

            $grouped[$key]['average_score'] = (float) $count > 0 ? (float) $totalScore / (float) $count : 0;
            $grouped[$key]['pass_rate'] = (float) $count > 0 ? ((float) $passCount / (float) $count) * 100 : 0;
        }

        return $grouped;
    }

    /**
     * 创建新的质量评估.
     *
     * @param array<string, mixed> $assessmentData
     */
    public function createAssessment(array $assessmentData): QualityAssessment
    {
        $assessment = new QualityAssessment();

        $assessmentType = $assessmentData['assessmentType'] ?? '默认评估';
        assert(is_string($assessmentType));
        $assessment->setAssessmentType($assessmentType);

        $targetId = $assessmentData['institutionId'] ?? '';
        assert(is_string($targetId));
        $assessment->setTargetId($targetId);

        $targetName = $assessmentData['targetName'] ?? '';
        assert(is_string($targetName));
        $assessment->setTargetName($targetName);

        $criteria = $assessmentData['criteria'] ?? '默认标准';
        assert(is_string($criteria));
        $assessment->setAssessmentCriteria($criteria);

        $items = $assessmentData['items'] ?? [];
        assert(is_array($items));
        /** @var array<string, mixed> $validatedItems */
        $validatedItems = $this->validateArrayWithStringKeys($items);
        $assessment->setAssessmentItems($validatedItems);

        $scores = $assessmentData['assessmentScores'] ?? $assessmentData['scores'] ?? [];
        assert(is_array($scores));
        /** @var array<string, mixed> $validatedScores */
        $validatedScores = $this->validateArrayWithStringKeys($scores);
        $assessment->setAssessmentScores($validatedScores);

        $totalScore = $assessmentData['totalScore'] ?? 0.0;
        assert(is_numeric($totalScore));
        $assessment->setTotalScore((float) $totalScore);

        $assessment->setAssessmentLevel($this->calculateAssessmentLevel($assessment->getTotalScore()));

        $comments = $assessmentData['comments'] ?? [];
        assert(is_array($comments));
        /** @var array<string, mixed> $validatedComments */
        $validatedComments = $this->validateArrayWithStringKeys($comments);
        $assessment->setAssessmentComments($validatedComments);

        $assessor = $assessmentData['assessor'] ?? '';
        assert(is_string($assessor));
        $assessment->setAssessor($assessor);

        $assessmentDate = $assessmentData['assessmentDate'] ?? new \DateTimeImmutable();
        if ($assessmentDate instanceof \DateTime) {
            $assessmentDate = \DateTimeImmutable::createFromMutable($assessmentDate);
        }
        assert($assessmentDate instanceof \DateTimeInterface);
        $assessment->setAssessmentDate($assessmentDate);

        $status = $assessmentData['status'] ?? '进行中';
        assert(is_string($status));
        $assessment->setAssessmentStatus($status);

        $remarks = $assessmentData['remarks'] ?? null;
        assert(is_string($remarks) || null === $remarks);
        $assessment->setRemarks($remarks);

        $this->entityManager->persist($assessment);
        $this->entityManager->flush();

        return $assessment;
    }

    /**
     * 更新质量评估.
     *
     * @param array<string, mixed> $updateData
     */
    public function updateAssessment(string $assessmentId, array $updateData): QualityAssessment
    {
        $assessment = $this->assessmentRepository->find($assessmentId);
        if (null === $assessment) {
            throw new QualityAssessmentNotFoundException("质量评估不存在: {$assessmentId}");
        }

        assert($assessment instanceof QualityAssessment);

        if (isset($updateData['assessmentStatus'])) {
            $status = $updateData['assessmentStatus'];
            assert(is_string($status));
            $assessment->setAssessmentStatus($status);
        }
        if (isset($updateData['score'])) {
            $score = $updateData['score'];
            assert(is_numeric($score));
            $scoreFloat = (float) $score;
            $assessment->setTotalScore($scoreFloat);
            $assessment->setAssessmentLevel($this->calculateAssessmentLevel($scoreFloat));
        }
        if (isset($updateData['assessmentItems'])) {
            $items = $updateData['assessmentItems'];
            assert(is_array($items));
            /** @var array<string, mixed> $validatedItems */
            $validatedItems = $this->validateArrayWithStringKeys($items);
            $assessment->setAssessmentItems($validatedItems);
        }
        if (isset($updateData['assessmentScores'])) {
            $scores = $updateData['assessmentScores'];
            assert(is_array($scores));
            /** @var array<string, mixed> $validatedScores */
            $validatedScores = $this->validateArrayWithStringKeys($scores);
            $assessment->setAssessmentScores($validatedScores);
        }
        if (isset($updateData['assessmentComments'])) {
            $comments = $updateData['assessmentComments'];
            assert(is_array($comments));
            /** @var array<string, mixed> $validatedComments */
            $validatedComments = $this->validateArrayWithStringKeys($comments);
            $assessment->setAssessmentComments($validatedComments);
        }
        if (isset($updateData['remarks'])) {
            $remarks = $updateData['remarks'];
            assert(is_string($remarks));
            $assessment->setRemarks($remarks);
        }

        $this->entityManager->flush();

        return $assessment;
    }

    /**
     * 计算评估分数.
     */
    public function calculateAssessmentScore(string $assessmentId): float
    {
        $assessment = $this->assessmentRepository->find($assessmentId);
        if (null === $assessment) {
            throw new QualityAssessmentNotFoundException("质量评估不存在: {$assessmentId}");
        }

        assert($assessment instanceof QualityAssessment);

        $scores = $assessment->getAssessmentScores();
        $totalScore = $this->calculateTotalScore($scores);

        $assessment->setTotalScore($totalScore);
        $assessment->setAssessmentLevel($this->calculateAssessmentLevel($totalScore));
        $this->entityManager->flush();

        return $totalScore;
    }

    /**
     * 按机构获取评估记录.
     *
     * @return QualityAssessment[]
     */
    public function getAssessmentsByInstitution(string $institutionId): array
    {
        $result = $this->assessmentRepository->createQueryBuilder('a')
            ->where('a.targetId = :institutionId')
            ->setParameter('institutionId', $institutionId)
            ->orderBy('a.assessmentDate', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        assert(is_array($result));

        /** @var QualityAssessment[] */
        return array_filter($result, fn ($item) => $item instanceof QualityAssessment);
    }

    /**
     * 验证并转换数组为string-keyed数组.
     *
     * @param array<mixed, mixed> $input
     * @return array<string, mixed>
     */
    private function validateArrayWithStringKeys(array $input): array
    {
        $result = [];
        foreach ($input as $key => $value) {
            $stringKey = is_string($key) ? $key : (string) $key;
            $result[$stringKey] = $value;
        }

        return $result;
    }

    /**
     * 导出评估数据.
     *
     * @return array<int, array<string, mixed>>
     */
    public function exportAssessments(?\DateTime $startDate = null, ?\DateTime $endDate = null, ?string $institutionId = null): array
    {
        $assessments = $this->assessmentRepository->findByDateRange($startDate ?? new \DateTimeImmutable('-1 year'), $endDate ?? new \DateTimeImmutable());

        // 如果指定了机构ID，进行过滤
        if (null !== $institutionId) {
            $assessments = array_filter($assessments, fn ($assessment) => $assessment->getTargetId() === $institutionId);
        }

        $exportData = [];
        foreach ($assessments as $assessment) {
            $assessmentDate = $assessment->getAssessmentDate();

            $exportData[] = [
                'id' => $assessment->getId(),
                'type' => $assessment->getAssessmentType(),
                'target_id' => $assessment->getTargetId(),
                'target_name' => $assessment->getTargetName(),
                'criteria' => $assessment->getAssessmentCriteria(),
                'total_score' => $assessment->getTotalScore(),
                'level' => $assessment->getAssessmentLevel(),
                'assessor' => $assessment->getAssessor(),
                'assessment_date' => $assessmentDate->format('Y-m-d'),
                'status' => $assessment->getAssessmentStatus(),
                'is_completed' => $assessment->isCompleted(),
                'is_passed' => $assessment->isPassed(),
                'remarks' => $assessment->getRemarks(),
            ];
        }

        return $exportData;
    }
}
