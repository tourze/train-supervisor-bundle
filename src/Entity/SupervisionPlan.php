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
use Tourze\TrainSupervisorBundle\Repository\SupervisionPlanRepository;

/**
 * 监督计划实体
 * 用于管理培训监督计划的制定、执行和跟踪
 */
#[AsPermission(title: '监督计划')]
#[Exportable]
#[ORM\Entity(repositoryClass: SupervisionPlanRepository::class)]
#[ORM\Table(name: 'job_training_supervision_plan', options: ['comment' => '监督计划'])]
class SupervisionPlan implements \Stringable
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
    #[ListColumn(title: '计划名称')]
    #[Filterable]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '计划名称'])]
    private string $planName;

    #[ExportColumn]
    #[ListColumn(title: '计划类型')]
    #[Filterable]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '计划类型：定期、专项、随机'])]
    private string $planType;

    #[ExportColumn]
    #[IndexColumn]
    #[ListColumn(title: '开始日期')]
    #[Filterable]
    #[ORM\Column(type: Types::DATE_MUTABLE, options: ['comment' => '计划开始日期'])]
    private \DateTimeInterface $planStartDate;

    #[ExportColumn]
    #[IndexColumn]
    #[ListColumn(title: '结束日期')]
    #[Filterable]
    #[ORM\Column(type: Types::DATE_MUTABLE, options: ['comment' => '计划结束日期'])]
    private \DateTimeInterface $planEndDate;

    #[ExportColumn]
    #[ListColumn(title: '监督范围')]
    #[ORM\Column(type: Types::JSON, options: ['comment' => '监督范围'])]
    private array $supervisionScope = [];

    #[ExportColumn]
    #[ListColumn(title: '监督项目')]
    #[ORM\Column(type: Types::JSON, options: ['comment' => '监督项目'])]
    private array $supervisionItems = [];

    #[ExportColumn]
    #[ListColumn(title: '监督人')]
    #[Filterable]
    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '监督人'])]
    private string $supervisor;

    #[ExportColumn]
    #[ListColumn(title: '计划状态')]
    #[Filterable]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '计划状态：待执行、执行中、已完成、已取消'])]
    private string $planStatus = '待执行';

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

    public function getPlanName(): string
    {
        return $this->planName;
    }

    public function setPlanName(string $planName): static
    {
        $this->planName = $planName;
        return $this;
    }

    public function getPlanType(): string
    {
        return $this->planType;
    }

    public function setPlanType(string $planType): static
    {
        $this->planType = $planType;
        return $this;
    }

    public function getPlanStartDate(): \DateTimeInterface
    {
        return $this->planStartDate;
    }

    public function setPlanStartDate(\DateTimeInterface $planStartDate): static
    {
        $this->planStartDate = $planStartDate;
        return $this;
    }

    public function getPlanEndDate(): \DateTimeInterface
    {
        return $this->planEndDate;
    }

    public function setPlanEndDate(\DateTimeInterface $planEndDate): static
    {
        $this->planEndDate = $planEndDate;
        return $this;
    }

    public function getSupervisionScope(): array
    {
        return $this->supervisionScope;
    }

    public function setSupervisionScope(array $supervisionScope): static
    {
        $this->supervisionScope = $supervisionScope;
        return $this;
    }

    public function getSupervisionItems(): array
    {
        return $this->supervisionItems;
    }

    public function setSupervisionItems(array $supervisionItems): static
    {
        $this->supervisionItems = $supervisionItems;
        return $this;
    }

    public function getSupervisor(): string
    {
        return $this->supervisor;
    }

    public function setSupervisor(string $supervisor): static
    {
        $this->supervisor = $supervisor;
        return $this;
    }

    public function getPlanStatus(): string
    {
        return $this->planStatus;
    }

    public function setPlanStatus(string $planStatus): static
    {
        $this->planStatus = $planStatus;
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
     * 检查计划是否处于活跃状态
     */
    public function isActive(): bool
    {
        return in_array($this->planStatus, ['待执行', '执行中']);
    }

    /**
     * 检查计划是否已过期
     */
    public function isExpired(): bool
    {
        return $this->planEndDate < new \DateTime();
    }

    /**
     * 获取计划持续天数
     */
    public function getDurationDays(): int
    {
        return $this->planStartDate->diff($this->planEndDate)->days;
    }

    public function __toString(): string
    {
        return $this->planName;
    }
} 