<?php

namespace Tourze\TrainSupervisorBundle\Entity;

use AppBundle\Entity\Supplier;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\EasyAdmin\Attribute\Action\Exportable;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;
use Tourze\TrainSupervisorBundle\Repository\SupervisionInspectionRepository;

/**
 * 监督检查实体
 * 用于记录培训机构的监督检查过程和结果
 */
#[AsPermission(title: '监督检查')]
#[Exportable]
#[ORM\Entity(repositoryClass: SupervisionInspectionRepository::class)]
#[ORM\Table(name: 'job_training_supervision_inspection', options: ['comment' => '监督检查'])]
class SupervisionInspection implements \Stringable
{
    #[Filterable]
    #[IndexColumn]
    #[ListColumn(order: 98, sorter: true)]
    #[ExportColumn]
    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeInterface $createTime = null;

    #[UpdateTimeColumn]
    #[ListColumn(order: 99, sorter: true)]
    #[Filterable]
    #[ExportColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]
    private ?\DateTimeInterface $updateTime = null;

    #[ExportColumn]
    #[ListColumn(order: -1, sorter: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ExportColumn]
    #[ListColumn(title: '监督计划')]
    #[ORM\ManyToOne(targetEntity: SupervisionPlan::class)]
    #[ORM\JoinColumn(nullable: false)]
    private SupervisionPlan $plan;

    #[ExportColumn]
    #[ListColumn(title: '被检查机构')]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Supplier $institution;

    #[ExportColumn]
    #[ListColumn(title: '检查类型')]
    #[Filterable]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '检查类型：现场检查、在线检查、专项检查'])]
    private string $inspectionType;

    #[ExportColumn]
    #[IndexColumn]
    #[ListColumn(title: '检查日期')]
    #[Filterable]
    #[ORM\Column(type: Types::DATE_MUTABLE, options: ['comment' => '检查日期'])]
    private \DateTimeInterface $inspectionDate;

    #[ExportColumn]
    #[ListColumn(title: '检查人')]
    #[Filterable]
    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '检查人'])]
    private string $inspector;

    #[ExportColumn]
    #[ListColumn(title: '检查项目')]
    #[ORM\Column(type: Types::JSON, options: ['comment' => '检查项目'])]
    private array $inspectionItems = [];

    #[ExportColumn]
    #[ListColumn(title: '检查结果')]
    #[ORM\Column(type: Types::JSON, options: ['comment' => '检查结果'])]
    private array $inspectionResults = [];

    #[ExportColumn]
    #[ListColumn(title: '发现问题')]
    #[ORM\Column(type: Types::JSON, options: ['comment' => '发现问题'])]
    private array $foundProblems = [];

    #[ExportColumn]
    #[ListColumn(title: '检查状态')]
    #[Filterable]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '检查状态：进行中、已完成、已取消'])]
    private string $inspectionStatus = '进行中';

    #[ExportColumn]
    #[ListColumn(title: '总体评分', sorter: true)]
    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true, options: ['comment' => '总体评分'])]
    private ?float $overallScore = null;

    #[ExportColumn]
    #[ListColumn(title: '检查报告')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '检查报告'])]
    private ?string $inspectionReport = null;

    #[ExportColumn]
    #[ListColumn(title: '备注')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注信息'])]
    private ?string $remarks = null;

    public function setCreateTime(?\DateTimeInterface $createdAt): void
    {
        $this->createTime = $createdAt;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createTime;
    }

    public function setUpdateTime(?\DateTimeInterface $updateTime): void
    {
        $this->updateTime = $updateTime;
    }

    public function getUpdateTime(): ?\DateTimeInterface
    {
        return $this->updateTime;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getPlan(): SupervisionPlan
    {
        return $this->plan;
    }

    public function setPlan(SupervisionPlan $plan): static
    {
        $this->plan = $plan;
        return $this;
    }

    public function getInstitution(): Supplier
    {
        return $this->institution;
    }

    public function setInstitution(Supplier $institution): static
    {
        $this->institution = $institution;
        return $this;
    }

    public function getInspectionType(): string
    {
        return $this->inspectionType;
    }

    public function setInspectionType(string $inspectionType): static
    {
        $this->inspectionType = $inspectionType;
        return $this;
    }

    public function getInspectionDate(): \DateTimeInterface
    {
        return $this->inspectionDate;
    }

    public function setInspectionDate(\DateTimeInterface $inspectionDate): static
    {
        $this->inspectionDate = $inspectionDate;
        return $this;
    }

    public function getInspector(): string
    {
        return $this->inspector;
    }

    public function setInspector(string $inspector): static
    {
        $this->inspector = $inspector;
        return $this;
    }

    public function getInspectionItems(): array
    {
        return $this->inspectionItems;
    }

    public function setInspectionItems(array $inspectionItems): static
    {
        $this->inspectionItems = $inspectionItems;
        return $this;
    }

    public function getInspectionResults(): array
    {
        return $this->inspectionResults;
    }

    public function setInspectionResults(array $inspectionResults): static
    {
        $this->inspectionResults = $inspectionResults;
        return $this;
    }

    public function getFoundProblems(): array
    {
        return $this->foundProblems;
    }

    public function setFoundProblems(array $foundProblems): static
    {
        $this->foundProblems = $foundProblems;
        return $this;
    }

    public function getInspectionStatus(): string
    {
        return $this->inspectionStatus;
    }

    public function setInspectionStatus(string $inspectionStatus): static
    {
        $this->inspectionStatus = $inspectionStatus;
        return $this;
    }

    public function getOverallScore(): ?float
    {
        return $this->overallScore;
    }

    public function setOverallScore(?float $overallScore): static
    {
        $this->overallScore = $overallScore;
        return $this;
    }

    public function getInspectionReport(): ?string
    {
        return $this->inspectionReport;
    }

    public function setInspectionReport(?string $inspectionReport): static
    {
        $this->inspectionReport = $inspectionReport;
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
     * 检查是否已完成
     */
    public function isCompleted(): bool
    {
        return $this->inspectionStatus === '已完成';
    }

    /**
     * 检查是否发现问题
     */
    public function hasProblems(): bool
    {
        return !empty($this->foundProblems);
    }

    /**
     * 获取问题数量
     */
    public function getProblemCount(): int
    {
        return count($this->foundProblems);
    }

    /**
     * 获取评分等级
     */
    public function getScoreLevel(): string
    {
        if ($this->overallScore === null) {
            return '未评分';
        }

        if ($this->overallScore >= 90) {
            return '优秀';
        } elseif ($this->overallScore >= 80) {
            return '良好';
        } elseif ($this->overallScore >= 70) {
            return '合格';
        } else {
            return '不合格';
        }
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->institution->getName(), $this->inspectionDate->format('Y-m-d'));
    }
} 