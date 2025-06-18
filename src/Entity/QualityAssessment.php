<?php

namespace Tourze\TrainSupervisorBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\EasyAdmin\Attribute\Action\Exportable;
use Tourze\TrainSupervisorBundle\Repository\QualityAssessmentRepository;

/**
 * 质量评估实体
 * 用于记录培训机构和课程的质量评估结果
 */
#[Exportable]
#[ORM\Entity(repositoryClass: QualityAssessmentRepository::class)]
#[ORM\Table(name: 'job_training_quality_assessment', options: ['comment' => '质量评估'])]
class QualityAssessment implements \Stringable
{
    use TimestampableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[IndexColumn]
    private string $assessmentType;

    #[IndexColumn]
    private string $targetId;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '评估对象名称'])]
    private string $targetName;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '评估标准'])]
    private string $assessmentCriteria;

    #[ORM\Column(type: Types::JSON, options: ['comment' => '评估项目'])]
    private array $assessmentItems = [];

    #[ORM\Column(type: Types::JSON, options: ['comment' => '评估分数'])]
    private array $assessmentScores = [];

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, options: ['comment' => '总分'])]
    private float $totalScore = 0.0;

    #[IndexColumn]
    private string $assessmentLevel;

    #[ORM\Column(type: Types::JSON, options: ['comment' => '评估意见'])]
    private array $assessmentComments = [];

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '评估人'])]
    private string $assessor;

    #[ORM\Column(type: Types::DATE_MUTABLE, options: ['comment' => '评估日期'])]
    private \DateTimeInterface $assessmentDate;

    #[IndexColumn]
    private string $assessmentStatus = '进行中';

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注信息'])]
    private ?string $remarks = null;public function getId(): ?string
    {
        return $this->id;
    }

    public function getAssessmentType(): string
    {
        return $this->assessmentType;
    }

    public function setAssessmentType(string $assessmentType): static
    {
        $this->assessmentType = $assessmentType;
        return $this;
    }

    public function getTargetId(): string
    {
        return $this->targetId;
    }

    public function setTargetId(string $targetId): static
    {
        $this->targetId = $targetId;
        return $this;
    }

    public function getTargetName(): string
    {
        return $this->targetName;
    }

    public function setTargetName(string $targetName): static
    {
        $this->targetName = $targetName;
        return $this;
    }

    public function getAssessmentCriteria(): string
    {
        return $this->assessmentCriteria;
    }

    public function setAssessmentCriteria(string $assessmentCriteria): static
    {
        $this->assessmentCriteria = $assessmentCriteria;
        return $this;
    }

    public function getAssessmentItems(): array
    {
        return $this->assessmentItems;
    }

    public function setAssessmentItems(array $assessmentItems): static
    {
        $this->assessmentItems = $assessmentItems;
        return $this;
    }

    public function getAssessmentScores(): array
    {
        return $this->assessmentScores;
    }

    public function setAssessmentScores(array $assessmentScores): static
    {
        $this->assessmentScores = $assessmentScores;
        return $this;
    }

    public function getTotalScore(): float
    {
        return $this->totalScore;
    }

    public function setTotalScore(float $totalScore): static
    {
        $this->totalScore = $totalScore;
        return $this;
    }

    public function getAssessmentLevel(): string
    {
        return $this->assessmentLevel;
    }

    public function setAssessmentLevel(string $assessmentLevel): static
    {
        $this->assessmentLevel = $assessmentLevel;
        return $this;
    }

    public function getAssessmentComments(): array
    {
        return $this->assessmentComments;
    }

    public function setAssessmentComments(array $assessmentComments): static
    {
        $this->assessmentComments = $assessmentComments;
        return $this;
    }

    public function getAssessor(): string
    {
        return $this->assessor;
    }

    public function setAssessor(string $assessor): static
    {
        $this->assessor = $assessor;
        return $this;
    }

    public function getAssessmentDate(): \DateTimeInterface
    {
        return $this->assessmentDate;
    }

    public function setAssessmentDate(\DateTimeInterface $assessmentDate): static
    {
        $this->assessmentDate = $assessmentDate;
        return $this;
    }

    public function getAssessmentStatus(): string
    {
        return $this->assessmentStatus;
    }

    public function setAssessmentStatus(string $assessmentStatus): static
    {
        $this->assessmentStatus = $assessmentStatus;
        return $this;
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(?string $remarks): static
    {
        $this->remarks = $remarks;
        return $this;
    }

    /**
     * 检查评估是否已完成
     */
    public function isCompleted(): bool
    {
        return $this->assessmentStatus === '已完成';
    }

    /**
     * 计算评估等级
     */
    public function calculateLevel(): string
    {
        if ($this->totalScore >= 90) {
            return '优秀';
        } elseif ($this->totalScore >= 80) {
            return '良好';
        } elseif ($this->totalScore >= 70) {
            return '合格';
        } else {
            return '不合格';
        }
    }

    /**
     * 检查是否通过评估
     */
    public function isPassed(): bool
    {
        return $this->totalScore >= 70;
    }

    /**
     * 获取评估项目数量
     */
    public function getItemCount(): int
    {
        return count($this->assessmentItems);
    }

    /**
     * 计算平均分
     */
    public function getAverageScore(): float
    {
        $itemCount = $this->getItemCount();
        return $itemCount > 0 ? $this->totalScore / $itemCount : 0.0;
    }

    public function __toString(): string
    {
        return sprintf('%s - %s (%s)', $this->assessmentType, $this->targetName, $this->assessmentLevel);
    }
} 