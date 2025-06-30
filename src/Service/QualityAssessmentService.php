<?php

namespace Tourze\TrainSupervisorBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\TrainSupervisorBundle\Entity\QualityAssessment;
use Tourze\TrainSupervisorBundle\Exception\QualityAssessmentNotFoundException;
use Tourze\TrainSupervisorBundle\Repository\QualityAssessmentRepository;

/**
 * 质量评估服务
 * 负责培训机构和课程的质量评估管理
 */
class QualityAssessmentService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly QualityAssessmentRepository $assessmentRepository
    ) {
    }

    /**
     * 评估培训机构
     */
    public function assessInstitution(string $institutionId, array $criteria): QualityAssessment
    {
        $assessment = new QualityAssessment();
        $assessment->setAssessmentType('机构评估');
        $assessment->setTargetId($institutionId);
        $assessment->setTargetName($criteria['targetName']);
        $assessment->setAssessmentCriteria($criteria['criteria']);
        $assessment->setAssessmentItems($criteria['items'] ?? []);
        $assessment->setAssessmentScores($criteria['scores'] ?? []);
        $assessment->setTotalScore($this->calculateTotalScore($criteria['scores'] ?? []));
        $assessment->setAssessmentLevel($this->calculateAssessmentLevel($assessment->getTotalScore()));
        $assessment->setAssessmentComments($criteria['comments'] ?? []);
        $assessment->setAssessor($criteria['assessor']);
        $assessment->setAssessmentDate($criteria['assessmentDate'] ?? new \DateTime());
        $assessment->setAssessmentStatus($criteria['status'] ?? '进行中');
        $assessment->setRemarks($criteria['remarks'] ?? null);

        $this->entityManager->persist($assessment);
        $this->entityManager->flush();

        return $assessment;
    }

    /**
     * 评估培训课程
     */
    public function assessCourse(string $courseId, array $criteria): QualityAssessment
    {
        $assessment = new QualityAssessment();
        $assessment->setAssessmentType('课程评估');
        $assessment->setTargetId($courseId);
        $assessment->setTargetName($criteria['targetName']);
        $assessment->setAssessmentCriteria($criteria['criteria']);
        $assessment->setAssessmentItems($criteria['items'] ?? []);
        $assessment->setAssessmentScores($criteria['scores'] ?? []);
        $assessment->setTotalScore($this->calculateTotalScore($criteria['scores'] ?? []));
        $assessment->setAssessmentLevel($this->calculateAssessmentLevel($assessment->getTotalScore()));
        $assessment->setAssessmentComments($criteria['comments'] ?? []);
        $assessment->setAssessor($criteria['assessor']);
        $assessment->setAssessmentDate($criteria['assessmentDate'] ?? new \DateTime());
        $assessment->setAssessmentStatus($criteria['status'] ?? '进行中');
        $assessment->setRemarks($criteria['remarks'] ?? null);

        $this->entityManager->persist($assessment);
        $this->entityManager->flush();

        return $assessment;
    }

    /**
     * 计算评估等级
     */
    public function calculateAssessmentLevel(float $score): string
    {
        if ($score >= 90) {
            return '优秀';
        } elseif ($score >= 80) {
            return '良好';
        } elseif ($score >= 70) {
            return '合格';
        } else {
            return '不合格';
        }
    }

    /**
     * 生成评估报告
     */
    public function generateAssessmentReport(string $assessmentId): array
    {
        $assessment = $this->assessmentRepository->find($assessmentId);
        if ($assessment === null) {
            throw new QualityAssessmentNotFoundException("质量评估不存在: {$assessmentId}");
        }

        return [
            'assessmentInfo' => [
                'id' => $assessment->getId(),
                'type' => $assessment->getAssessmentType(),
                'targetName' => $assessment->getTargetName(),
                'criteria' => $assessment->getAssessmentCriteria(),
                'totalScore' => $assessment->getTotalScore(),
                'level' => $assessment->getAssessmentLevel(),
                'assessor' => $assessment->getAssessor(),
                'assessmentDate' => $assessment->getAssessmentDate()->format('Y-m-d'),
                'status' => $assessment->getAssessmentStatus(),
                'isCompleted' => $assessment->isCompleted(),
                'isPassed' => $assessment->isPassed(),
                'itemCount' => $assessment->getItemCount(),
                'averageScore' => $assessment->getAverageScore()
            ],
            'assessmentItems' => $assessment->getAssessmentItems(),
            'assessmentScores' => $assessment->getAssessmentScores(),
            'assessmentComments' => $assessment->getAssessmentComments(),
            'remarks' => $assessment->getRemarks(),
            'generatedAt' => new \DateTime()
        ];
    }

    /**
     * 计算总分
     */
    private function calculateTotalScore(array $scores): float
    {
        if (empty($scores)) {
            return 0.0;
        }

        $totalScore = 0.0;
        $totalWeight = 0.0;

        foreach ($scores as $score) {
            $value = $score['value'] ?? 0;
            $weight = $score['weight'] ?? 1;
            $totalScore += $value * $weight;
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? $totalScore / $totalWeight : 0.0;
    }

    /**
     * 完成评估
     */
    public function completeAssessment(string $assessmentId): QualityAssessment
    {
        $assessment = $this->assessmentRepository->find($assessmentId);
        if ($assessment === null) {
            throw new QualityAssessmentNotFoundException("质量评估不存在: {$assessmentId}");
        }

        $assessment->setAssessmentStatus('已完成');
        $this->entityManager->flush();

        return $assessment;
    }

    /**
     * 获取已完成的评估
     */
    public function getCompletedAssessments(): array
    {
        return $this->assessmentRepository->findCompletedAssessments();
    }

    /**
     * 按类型获取评估
     */
    public function getAssessmentsByType(string $type): array
    {
        return $this->assessmentRepository->findByType($type);
    }

    /**
     * 获取指定对象的评估记录
     */
    public function getAssessmentsByTarget(string $targetId): array
    {
        return $this->assessmentRepository->findByTarget($targetId);
    }

    /**
     * 按等级统计评估
     */
    public function getStatisticsByLevel(): array
    {
        return $this->assessmentRepository->countByLevel();
    }

    /**
     * 获取不合格的评估
     */
    public function getFailedAssessments(): array
    {
        return $this->assessmentRepository->findFailedAssessments();
    }

    /**
     * 获取平均分
     */
    public function getAverageScore(): float
    {
        return $this->assessmentRepository->getAverageScore();
    }

    /**
     * 获取评估统计信息
     */
    public function getAssessmentStatistics(?\DateTime $startDate = null, ?\DateTime $endDate = null, ?string $institutionId = null): array
    {
        $assessments = $this->assessmentRepository->findByDateRange($startDate ?? new \DateTime('-1 year'), $endDate ?? new \DateTime());
        
        // 如果指定了机构ID，进行过滤
        if ($institutionId !== null) {
            $assessments = array_filter($assessments, fn($assessment) => $assessment->getTargetId() === $institutionId);
        }
        
        $totalCount = count($assessments);
        if ($totalCount === 0) {
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
                'trends' => []
            ];
        }
        
        $scores = array_map(fn($assessment) => $assessment->getTotalScore(), $assessments);
        $excellentCount = count(array_filter($scores, fn($score) => $score >= 90));
        $goodCount = count(array_filter($scores, fn($score) => $score >= 80 && $score < 90));
        $passCount = count(array_filter($scores, fn($score) => $score >= 70));
        
        // 按类型统计
        $byType = [];
        foreach ($assessments as $assessment) {
            $type = $assessment->getAssessmentType();
            if (!isset($byType[$type])) {
                $byType[$type] = ['count' => 0, 'total_score' => 0, 'pass_count' => 0];
            }
            $byType[$type]['count']++;
            $byType[$type]['total_score'] += $assessment->getTotalScore();
            if ($assessment->getTotalScore() >= 70) {
                $byType[$type]['pass_count']++;
            }
        }
        
        foreach ($byType as $type => &$data) {
            $data['average_score'] = $data['total_score'] / $data['count'];
            $data['pass_rate'] = ($data['pass_count'] / $data['count']) * 100;
        }
        
        // 按机构统计
        $byInstitution = [];
        foreach ($assessments as $assessment) {
            $institution = $assessment->getTargetName();
            if (!isset($byInstitution[$institution])) {
                $byInstitution[$institution] = ['count' => 0, 'total_score' => 0, 'pass_count' => 0];
            }
            $byInstitution[$institution]['count']++;
            $byInstitution[$institution]['total_score'] += $assessment->getTotalScore();
            if ($assessment->getTotalScore() >= 70) {
                $byInstitution[$institution]['pass_count']++;
            }
        }
        
        foreach ($byInstitution as $institution => &$data) {
            $data['average_score'] = $data['total_score'] / $data['count'];
            $data['pass_rate'] = ($data['pass_count'] / $data['count']) * 100;
        }
        
        // 排序并限制前10
        arsort($byInstitution);
        $byInstitution = array_slice($byInstitution, 0, 10, true);
        
        return [
            'total_assessments' => $totalCount,
            'average_score' => array_sum($scores) / $totalCount,
            'max_score' => max($scores),
            'min_score' => min($scores),
            'excellent_rate' => ($excellentCount / $totalCount) * 100,
            'good_rate' => ($goodCount / $totalCount) * 100,
            'pass_rate' => ($passCount / $totalCount) * 100,
            'by_type' => $byType,
            'by_institution' => $byInstitution,
            'trends' => []
        ];
    }

    /**
     * 导出评估数据
     */
    public function exportAssessments(?\DateTime $startDate = null, ?\DateTime $endDate = null, ?string $institutionId = null): array
    {
        $assessments = $this->assessmentRepository->findByDateRange($startDate ?? new \DateTime('-1 year'), $endDate ?? new \DateTime());
        
        // 如果指定了机构ID，进行过滤
        if ($institutionId !== null) {
            $assessments = array_filter($assessments, fn($assessment) => $assessment->getTargetId() === $institutionId);
        }
        
        $exportData = [];
        foreach ($assessments as $assessment) {
            $exportData[] = [
                'id' => $assessment->getId(),
                'type' => $assessment->getAssessmentType(),
                'target_id' => $assessment->getTargetId(),
                'target_name' => $assessment->getTargetName(),
                'criteria' => $assessment->getAssessmentCriteria(),
                'total_score' => $assessment->getTotalScore(),
                'level' => $assessment->getAssessmentLevel(),
                'assessor' => $assessment->getAssessor(),
                'assessment_date' => $assessment->getAssessmentDate()->format('Y-m-d'),
                'status' => $assessment->getAssessmentStatus(),
                'is_completed' => $assessment->isCompleted(),
                'is_passed' => $assessment->isPassed(),
                'remarks' => $assessment->getRemarks()
            ];
        }
        
        return $exportData;
    }
} 