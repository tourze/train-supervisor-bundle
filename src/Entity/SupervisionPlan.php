<?php

namespace Tourze\TrainSupervisorBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\TrainSupervisorBundle\Repository\SupervisionPlanRepository;

/**
 * 监督计划实体
 * 用于管理培训监督计划的制定、执行和跟踪.
 */
#[ORM\Entity(repositoryClass: SupervisionPlanRepository::class)]
#[ORM\Table(name: 'job_training_supervision_plan', options: ['comment' => '监督计划'])]
class SupervisionPlan implements \Stringable
{
    use TimestampableAware;
    use SnowflakeKeyAware;

    #[Assert\NotBlank(message: '计划名称不能为空')]
    #[Assert\Length(max: 255, maxMessage: '计划名称不能超过255个字符')]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '计划名称'])]
    private string $planName;

    #[Assert\NotBlank(message: '计划类型不能为空')]
    #[Assert\Length(max: 50, maxMessage: '计划类型不能超过50个字符')]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '计划类型：定期、专项、随机'])]
    private string $planType;

    #[Assert\NotNull(message: '计划开始日期不能为空')]
    #[Assert\Type(type: \DateTimeInterface::class)]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, options: ['comment' => '计划开始日期'])]
    private \DateTimeInterface $planStartDate;

    #[Assert\NotNull(message: '计划结束日期不能为空')]
    #[Assert\Type(type: \DateTimeInterface::class)]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, options: ['comment' => '计划结束日期'])]
    private \DateTimeInterface $planEndDate;

    /**
     * @var array<int, string>
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, options: ['comment' => '监督范围'])]
    private array $supervisionScope = [];

    /**
     * @var array<int, string>
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, options: ['comment' => '监督项目'])]
    private array $supervisionItems = [];

    #[Assert\NotBlank(message: '监督人不能为空')]
    #[Assert\Length(max: 100, maxMessage: '监督人不能超过100个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '监督人'])]
    private string $supervisor;

    #[Assert\NotBlank(message: '计划状态不能为空')]
    #[Assert\Length(max: 50, maxMessage: '计划状态不能超过50个字符')]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '计划状态'])]
    private string $planStatus = '待执行';

    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535, maxMessage: '备注信息过长')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注信息'])]
    private ?string $remarks = null;

    public function getPlanName(): string
    {
        return $this->planName;
    }

    public function setPlanName(string $planName): void
    {
        $this->planName = $planName;
    }

    public function getPlanType(): string
    {
        return $this->planType;
    }

    public function setPlanType(string $planType): void
    {
        $this->planType = $planType;
    }

    public function getPlanStartDate(): \DateTimeInterface
    {
        return $this->planStartDate;
    }

    public function setPlanStartDate(\DateTimeInterface $planStartDate): void
    {
        $this->planStartDate = $planStartDate;
    }

    public function getPlanEndDate(): \DateTimeInterface
    {
        return $this->planEndDate;
    }

    public function setPlanEndDate(\DateTimeInterface $planEndDate): void
    {
        $this->planEndDate = $planEndDate;
    }

    /**
     * @return array<int, string>
     */
    public function getSupervisionScope(): array
    {
        return $this->supervisionScope;
    }

    /**
     * @param array<int, string> $supervisionScope
     */
    public function setSupervisionScope(array $supervisionScope): void
    {
        $this->supervisionScope = $supervisionScope;
    }

    /**
     * @return array<int, string>
     */
    public function getSupervisionItems(): array
    {
        return $this->supervisionItems;
    }

    /**
     * @param array<int, string> $supervisionItems
     */
    public function setSupervisionItems(array $supervisionItems): void
    {
        $this->supervisionItems = $supervisionItems;
    }

    public function getSupervisor(): string
    {
        return $this->supervisor;
    }

    public function setSupervisor(string $supervisor): void
    {
        $this->supervisor = $supervisor;
    }

    public function getPlanStatus(): string
    {
        return $this->planStatus;
    }

    public function setPlanStatus(string $planStatus): void
    {
        $this->planStatus = $planStatus;
    }

    // Alias for backward compatibility
    public function getStatus(): string
    {
        return $this->planStatus;
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
     * 检查计划是否处于活跃状态
     */
    public function isActive(): bool
    {
        return in_array($this->planStatus, ['待执行', '执行中', '激活'], true);
    }

    /**
     * 检查计划是否已过期
     */
    public function isExpired(): bool
    {
        return $this->planEndDate < new \DateTime();
    }

    /**
     * 获取计划持续天数.
     */
    public function getDurationDays(): int
    {
        $days = $this->planStartDate->diff($this->planEndDate)->days;

        return false === $days ? 0 : $days;
    }

    public function __toString(): string
    {
        return $this->planName;
    }
}
