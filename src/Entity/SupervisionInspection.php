<?php

namespace Tourze\TrainSupervisorBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\TrainSupervisorBundle\Repository\SupervisionInspectionRepository;

/**
 * 监督检查实体
 * 用于记录培训机构的监督检查过程和结果.
 */
#[ORM\Entity(repositoryClass: SupervisionInspectionRepository::class)]
#[ORM\Table(name: 'job_training_supervision_inspection', options: ['comment' => '监督检查'])]
class SupervisionInspection implements \Stringable
{
    use TimestampableAware;
    use SnowflakeKeyAware;

    #[ORM\ManyToOne(targetEntity: SupervisionPlan::class)]
    #[ORM\JoinColumn(nullable: false)]
    private SupervisionPlan $plan;

    #[Assert\NotBlank(message: '机构名称不能为空')]
    #[Assert\Length(max: 255, maxMessage: '机构名称不能超过255个字符')]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '机构名称'])]
    private string $institutionName;

    #[Assert\NotBlank(message: '检查类型不能为空')]
    #[Assert\Length(max: 50, maxMessage: '检查类型不能超过50个字符')]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '检查类型：现场检查、在线检查、专项检查'])]
    private string $inspectionType;

    #[Assert\NotNull(message: '检查日期不能为空')]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, options: ['comment' => '检查日期'])]
    private \DateTimeInterface $inspectionDate;

    #[Assert\NotBlank(message: '检查人不能为空')]
    #[Assert\Length(max: 100, maxMessage: '检查人不能超过100个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '检查人'])]
    private string $inspector;

    /**
     * @var array<int, string>
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, options: ['comment' => '检查项目'])]
    private array $inspectionItems = [];

    /**
     * @var array<string, mixed>
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, options: ['comment' => '检查结果'])]
    private array $inspectionResults = [];

    /**
     * @var array<string, mixed>
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, options: ['comment' => '发现问题'])]
    private array $foundProblems = [];

    #[Assert\NotBlank(message: '检查状态不能为空')]
    #[Assert\Length(max: 50, maxMessage: '检查状态不能超过50个字符')]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '检查状态：计划中、进行中、已完成、已取消'])]
    private string $inspectionStatus = 'planned';

    #[Assert\Type(type: 'float')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: '总体评分必须在 {{ min }} 到 {{ max }} 之间')]
    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true, options: ['comment' => '总体评分'])]
    private ?float $overallScore = null;

    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535, maxMessage: '检查报告过长')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '检查报告'])]
    private ?string $inspectionReport = null;

    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535, maxMessage: '备注过长')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注'])]
    private ?string $remarks = null;

    #[Assert\Type(type: 'integer')]
    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['comment' => '供应商ID'])]
    private ?int $supplierId = null;

    public function getPlan(): SupervisionPlan
    {
        return $this->plan;
    }

    public function setPlan(SupervisionPlan $plan): void
    {
        $this->plan = $plan;
    }

    public function getInstitutionName(): string
    {
        return $this->institutionName;
    }

    public function setInstitutionName(string $institutionName): void
    {
        $this->institutionName = $institutionName;
    }

    public function getInspectionType(): string
    {
        return $this->inspectionType;
    }

    public function setInspectionType(string $inspectionType): void
    {
        $this->inspectionType = $inspectionType;
    }

    public function getInspectionDate(): \DateTimeInterface
    {
        return $this->inspectionDate;
    }

    public function setInspectionDate(\DateTimeInterface $inspectionDate): void
    {
        $this->inspectionDate = $inspectionDate;
    }

    public function getInspector(): string
    {
        return $this->inspector;
    }

    public function setInspector(string $inspector): void
    {
        $this->inspector = $inspector;
    }

    /**
     * @return array<int, string>
     */
    public function getInspectionItems(): array
    {
        return $this->inspectionItems;
    }

    /**
     * @param array<int, string> $inspectionItems
     */
    public function setInspectionItems(array $inspectionItems): void
    {
        $this->inspectionItems = $inspectionItems;
    }

    /**
     * @return array<string, mixed>
     */
    public function getInspectionResults(): array
    {
        return $this->inspectionResults;
    }

    /**
     * @param array<string, mixed> $inspectionResults
     */
    public function setInspectionResults(array $inspectionResults): void
    {
        $this->inspectionResults = $inspectionResults;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFoundProblems(): array
    {
        return $this->foundProblems;
    }

    /**
     * @param array<string, mixed> $foundProblems
     */
    public function setFoundProblems(array $foundProblems): void
    {
        $this->foundProblems = $foundProblems;
    }

    public function getInspectionStatus(): string
    {
        return $this->inspectionStatus;
    }

    public function setInspectionStatus(string $inspectionStatus): void
    {
        $this->inspectionStatus = $inspectionStatus;
    }

    public function getOverallScore(): ?float
    {
        return $this->overallScore;
    }

    public function setOverallScore(?float $overallScore): void
    {
        $this->overallScore = $overallScore;
    }

    public function getInspectionReport(): ?string
    {
        return $this->inspectionReport;
    }

    public function setInspectionReport(?string $inspectionReport): void
    {
        $this->inspectionReport = $inspectionReport;
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
     * 检查是否已完成.
     */
    public function isCompleted(): bool
    {
        return 'completed' === $this->inspectionStatus;
    }

    /**
     * 是否有问题.
     */
    public function hasProblems(): bool
    {
        return [] !== $this->foundProblems;
    }

    /**
     * 获取问题数量.
     */
    public function getProblemCount(): int
    {
        return count($this->foundProblems);
    }

    public function getSupplierId(): ?int
    {
        return $this->supplierId;
    }

    public function setSupplierId(?int $supplierId): void
    {
        $this->supplierId = $supplierId;
    }

    /**
     * 获取评分等级.
     */
    public function getScoreLevel(): string
    {
        if (null === $this->overallScore) {
            return '未评分';
        }

        if ($this->overallScore >= 90) {
            return '优秀';
        }
        if ($this->overallScore >= 80) {
            return '良好';
        }
        if ($this->overallScore >= 70) {
            return '合格';
        }

        return '不合格';
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->institutionName, $this->inspectionDate->format('Y-m-d'));
    }
}
