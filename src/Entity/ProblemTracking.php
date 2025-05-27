<?php

namespace Tourze\TrainSupervisorBundle\Entity;

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
use Tourze\TrainSupervisorBundle\Repository\ProblemTrackingRepository;

/**
 * 问题跟踪实体
 * 用于跟踪监督检查中发现的问题及其整改情况
 */
#[AsPermission(title: '问题跟踪')]
#[Exportable]
#[ORM\Entity(repositoryClass: ProblemTrackingRepository::class)]
#[ORM\Table(name: 'job_training_problem_tracking', options: ['comment' => '问题跟踪'])]
class ProblemTracking implements \Stringable
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
    #[ListColumn(title: '监督检查')]
    #[ORM\ManyToOne(targetEntity: SupervisionInspection::class)]
    #[ORM\JoinColumn(nullable: false)]
    private SupervisionInspection $inspection;

    #[ExportColumn]
    #[ListColumn(title: '问题类型')]
    #[Filterable]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '问题类型：制度问题、管理问题、技术问题、安全问题'])]
    private string $problemType;

    #[ExportColumn]
    #[ListColumn(title: '问题描述')]
    #[ORM\Column(type: Types::TEXT, options: ['comment' => '问题描述'])]
    private string $problemDescription;

    #[ExportColumn]
    #[ListColumn(title: '问题严重程度')]
    #[Filterable]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '问题严重程度：轻微、一般、严重、重大'])]
    private string $problemSeverity;

    #[ExportColumn]
    #[ListColumn(title: '整改措施')]
    #[ORM\Column(type: Types::JSON, options: ['comment' => '整改措施'])]
    private array $correctionMeasures = [];

    #[ExportColumn]
    #[IndexColumn]
    #[ListColumn(title: '整改期限')]
    #[Filterable]
    #[ORM\Column(type: Types::DATE_MUTABLE, options: ['comment' => '整改期限'])]
    private \DateTimeInterface $correctionDeadline;

    #[ExportColumn]
    #[ListColumn(title: '整改状态')]
    #[Filterable]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '整改状态：待整改、整改中、已整改、已验证、已关闭'])]
    private string $correctionStatus = '待整改';

    #[ExportColumn]
    #[ListColumn(title: '整改证据')]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '整改证据'])]
    private ?array $correctionEvidence = null;

    #[ExportColumn]
    #[IndexColumn]
    #[ListColumn(title: '整改日期')]
    #[Filterable]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true, options: ['comment' => '整改日期'])]
    private ?\DateTimeInterface $correctionDate = null;

    #[ExportColumn]
    #[ListColumn(title: '验证结果')]
    #[Filterable]
    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '验证结果：通过、不通过、部分通过'])]
    private ?string $verificationResult = null;

    #[ExportColumn]
    #[IndexColumn]
    #[ListColumn(title: '验证日期')]
    #[Filterable]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true, options: ['comment' => '验证日期'])]
    private ?\DateTimeInterface $verificationDate = null;

    #[ExportColumn]
    #[ListColumn(title: '验证人')]
    #[Filterable]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '验证人'])]
    private ?string $verifier = null;

    #[ExportColumn]
    #[ListColumn(title: '责任人')]
    #[Filterable]
    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '责任人'])]
    private string $responsiblePerson;

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

    public function getInspection(): SupervisionInspection
    {
        return $this->inspection;
    }

    public function setInspection(SupervisionInspection $inspection): static
    {
        $this->inspection = $inspection;
        return $this;
    }

    public function getProblemType(): string
    {
        return $this->problemType;
    }

    public function setProblemType(string $problemType): static
    {
        $this->problemType = $problemType;
        return $this;
    }

    public function getProblemDescription(): string
    {
        return $this->problemDescription;
    }

    public function setProblemDescription(string $problemDescription): static
    {
        $this->problemDescription = $problemDescription;
        return $this;
    }

    public function getProblemSeverity(): string
    {
        return $this->problemSeverity;
    }

    public function setProblemSeverity(string $problemSeverity): static
    {
        $this->problemSeverity = $problemSeverity;
        return $this;
    }

    public function getCorrectionMeasures(): array
    {
        return $this->correctionMeasures;
    }

    public function setCorrectionMeasures(array $correctionMeasures): static
    {
        $this->correctionMeasures = $correctionMeasures;
        return $this;
    }

    public function getCorrectionDeadline(): \DateTimeInterface
    {
        return $this->correctionDeadline;
    }

    public function setCorrectionDeadline(\DateTimeInterface $correctionDeadline): static
    {
        $this->correctionDeadline = $correctionDeadline;
        return $this;
    }

    public function getCorrectionStatus(): string
    {
        return $this->correctionStatus;
    }

    public function setCorrectionStatus(string $correctionStatus): static
    {
        $this->correctionStatus = $correctionStatus;
        return $this;
    }

    public function getCorrectionEvidence(): ?array
    {
        return $this->correctionEvidence;
    }

    public function setCorrectionEvidence(?array $correctionEvidence): static
    {
        $this->correctionEvidence = $correctionEvidence;
        return $this;
    }

    public function getCorrectionDate(): ?\DateTimeInterface
    {
        return $this->correctionDate;
    }

    public function setCorrectionDate(?\DateTimeInterface $correctionDate): static
    {
        $this->correctionDate = $correctionDate;
        return $this;
    }

    public function getVerificationResult(): ?string
    {
        return $this->verificationResult;
    }

    public function setVerificationResult(?string $verificationResult): static
    {
        $this->verificationResult = $verificationResult;
        return $this;
    }

    public function getVerificationDate(): ?\DateTimeInterface
    {
        return $this->verificationDate;
    }

    public function setVerificationDate(?\DateTimeInterface $verificationDate): static
    {
        $this->verificationDate = $verificationDate;
        return $this;
    }

    public function getVerifier(): ?string
    {
        return $this->verifier;
    }

    public function setVerifier(?string $verifier): static
    {
        $this->verifier = $verifier;
        return $this;
    }

    public function getResponsiblePerson(): string
    {
        return $this->responsiblePerson;
    }

    public function setResponsiblePerson(string $responsiblePerson): static
    {
        $this->responsiblePerson = $responsiblePerson;
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
     * 检查问题是否已整改
     */
    public function isCorrected(): bool
    {
        return in_array($this->correctionStatus, ['已整改', '已验证', '已关闭']);
    }

    /**
     * 检查问题是否已验证
     */
    public function isVerified(): bool
    {
        return in_array($this->correctionStatus, ['已验证', '已关闭']);
    }

    /**
     * 检查问题是否已关闭
     */
    public function isClosed(): bool
    {
        return $this->correctionStatus === '已关闭';
    }

    /**
     * 检查是否已过期
     */
    public function isOverdue(): bool
    {
        if ($this->isCorrected()) {
            return false;
        }
        return $this->correctionDeadline < new \DateTime();
    }

    /**
     * 获取剩余天数
     */
    public function getRemainingDays(): int
    {
        if ($this->isCorrected()) {
            return 0;
        }
        $now = new \DateTime();
        $diff = $now->diff($this->correctionDeadline);
        return $diff->invert ? -$diff->days : $diff->days;
    }

    /**
     * 检查验证是否通过
     */
    public function isVerificationPassed(): bool
    {
        return $this->verificationResult === '通过';
    }

    /**
     * 获取整改措施数量
     */
    public function getMeasureCount(): int
    {
        return count($this->correctionMeasures);
    }

    /**
     * 检查是否有整改证据
     */
    public function hasEvidence(): bool
    {
        return !empty($this->correctionEvidence);
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->problemType, substr($this->problemDescription, 0, 50));
    }
} 