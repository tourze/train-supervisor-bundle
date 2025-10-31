<?php

namespace Tourze\TrainSupervisorBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\TrainSupervisorBundle\Repository\QualityAssessmentRepository;

/**
 * 质量评估实体
 * 用于记录培训机构和课程的质量评估结果.
 */
#[ORM\Entity(repositoryClass: QualityAssessmentRepository::class)]
#[ORM\Table(name: 'train_quality_assessment', options: ['comment' => '质量评估'])]
class QualityAssessment implements \Stringable
{
    use TimestampableAware;
    use SnowflakeKeyAware;

    #[Assert\NotBlank(message: '评估类型不能为空')]
    #[Assert\Length(max: 50, maxMessage: '评估类型不能超过50个字符')]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '评估类型'])]
    private string $assessmentType;

    #[Assert\NotBlank(message: '目标ID不能为空')]
    #[Assert\Length(max: 255, maxMessage: '目标ID不能超过255个字符')]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '目标ID'])]
    private string $targetId;

    #[Assert\NotBlank(message: '评估对象名称不能为空')]
    #[Assert\Length(max: 255, maxMessage: '评估对象名称不能超过255个字符')]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '评估对象名称'])]
    private string $targetName;

    #[Assert\NotBlank(message: '评估标准不能为空')]
    #[Assert\Length(max: 100, maxMessage: '评估标准不能超过100个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '评估标准'])]
    private string $assessmentCriteria;

    /**
     * @var array<string, mixed>
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, options: ['comment' => '评估项目'])]
    private array $assessmentItems = [];

    /**
     * @var array<string, mixed>
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, options: ['comment' => '评估分数'])]
    private array $assessmentScores = [];

    #[Assert\NotNull(message: '总分不能为空')]
    #[Assert\Type(type: 'float')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: '总分必须在 {{ min }} 到 {{ max }} 之间')]
    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, options: ['comment' => '总分'])]
    private float $totalScore = 0.0;

    #[Assert\NotBlank(message: '评估等级不能为空')]
    #[Assert\Length(max: 50, maxMessage: '评估等级不能超过50个字符')]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '评估等级'])]
    private string $assessmentLevel;

    /**
     * @var array<string, mixed>
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, options: ['comment' => '评估意见'])]
    private array $assessmentComments = [];

    #[Assert\NotBlank(message: '评估人不能为空')]
    #[Assert\Length(max: 100, maxMessage: '评估人不能超过100个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '评估人'])]
    private string $assessor;

    #[Assert\NotNull(message: '评估日期不能为空')]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, options: ['comment' => '评估日期'])]
    private \DateTimeInterface $assessmentDate;

    #[Assert\NotBlank(message: '评估状态不能为空')]
    #[Assert\Length(max: 50, maxMessage: '评估状态不能超过50个字符')]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '评估状态'])]
    private string $assessmentStatus = '进行中';

    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535, maxMessage: '备注信息过长')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注信息'])]
    private ?string $remarks = null;

    public function getAssessmentType(): string
    {
        return $this->assessmentType;
    }

    public function setAssessmentType(string $assessmentType): void
    {
        $this->assessmentType = $assessmentType;
    }

    public function getTargetId(): string
    {
        return $this->targetId;
    }

    public function setTargetId(string $targetId): void
    {
        $this->targetId = $targetId;
    }

    public function getTargetName(): string
    {
        return $this->targetName;
    }

    public function setTargetName(string $targetName): void
    {
        $this->targetName = $targetName;
    }

    public function getAssessmentCriteria(): string
    {
        return $this->assessmentCriteria;
    }

    public function setAssessmentCriteria(string $assessmentCriteria): void
    {
        $this->assessmentCriteria = $assessmentCriteria;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAssessmentItems(): array
    {
        return $this->assessmentItems;
    }

    /**
     * @param array<string, mixed> $assessmentItems
     */
    public function setAssessmentItems(array $assessmentItems): void
    {
        $this->assessmentItems = $assessmentItems;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAssessmentScores(): array
    {
        return $this->assessmentScores;
    }

    /**
     * @param array<string, mixed> $assessmentScores
     */
    public function setAssessmentScores(array $assessmentScores): void
    {
        $this->assessmentScores = $assessmentScores;
    }

    public function getTotalScore(): float
    {
        return $this->totalScore;
    }

    public function setTotalScore(float $totalScore): void
    {
        $this->totalScore = $totalScore;
    }

    public function getAssessmentLevel(): string
    {
        return $this->assessmentLevel;
    }

    public function setAssessmentLevel(string $assessmentLevel): void
    {
        $this->assessmentLevel = $assessmentLevel;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAssessmentComments(): array
    {
        return $this->assessmentComments;
    }

    /**
     * @param array<string, mixed> $assessmentComments
     */
    public function setAssessmentComments(array $assessmentComments): void
    {
        $this->assessmentComments = $assessmentComments;
    }

    public function getAssessor(): string
    {
        return $this->assessor;
    }

    public function setAssessor(string $assessor): void
    {
        $this->assessor = $assessor;
    }

    public function getAssessmentDate(): \DateTimeInterface
    {
        return $this->assessmentDate;
    }

    public function setAssessmentDate(\DateTimeInterface $assessmentDate): void
    {
        $this->assessmentDate = $assessmentDate;
    }

    public function getAssessmentStatus(): string
    {
        return $this->assessmentStatus;
    }

    public function setAssessmentStatus(string $assessmentStatus): void
    {
        $this->assessmentStatus = $assessmentStatus;
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(?string $remarks): void
    {
        $this->remarks = $remarks;
    }

    /**
     * 检查评估是否已完成.
     */
    public function isCompleted(): bool
    {
        return '已完成' === $this->assessmentStatus;
    }

    /**
     * 计算评估等级.
     */
    public function calculateLevel(): string
    {
        if ($this->totalScore >= 90) {
            return '优秀';
        }
        if ($this->totalScore >= 80) {
            return '良好';
        }
        if ($this->totalScore >= 70) {
            return '合格';
        }

        return '不合格';
    }

    /**
     * 检查是否通过评估.
     */
    public function isPassed(): bool
    {
        return $this->totalScore >= 70;
    }

    /**
     * 获取评估项目数量.
     */
    public function getItemCount(): int
    {
        return count($this->assessmentItems);
    }

    /**
     * 计算平均分.
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
