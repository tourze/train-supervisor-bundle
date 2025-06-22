<?php

namespace Tourze\TrainSupervisorBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\TrainSupervisorBundle\Repository\ProblemTrackingRepository;

/**
 * 问题跟踪实体
 * 用于跟踪监督检查中发现的问题及其整改情况
 */
#[ORM\Entity(repositoryClass: ProblemTrackingRepository::class)]
#[ORM\Table(name: 'job_training_problem_tracking', options: ['comment' => '问题跟踪'])]
class ProblemTracking implements \Stringable
{
    use TimestampableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: SupervisionInspection::class)]
    #[ORM\JoinColumn(nullable: false)]
    private SupervisionInspection $inspection;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '问题标题'])]
    private string $problemTitle;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '问题类型'])]
    private string $problemType;

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '问题描述'])]
    private string $problemDescription;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '问题严重程度'])]
    private string $problemSeverity;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '问题状态'], nullable: false)]
    private string $problemStatus = '待处理';

    #[ORM\Column(type: Types::DATE_IMMUTABLE, options: ['comment' => '发现日期'])]
    private \DateTimeInterface $discoveryDate;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true, options: ['comment' => '预期解决日期'])]
    private ?\DateTimeInterface $expectedResolutionDate = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true, options: ['comment' => '实际解决日期'])]
    private ?\DateTimeInterface $actualResolutionDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '根因分析'])]
    private ?string $rootCauseAnalysis = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '预防措施'])]
    private ?string $preventiveMeasures = null;

    #[ORM\Column(type: Types::JSON, options: ['comment' => '整改措施'])]
    private array $correctionMeasures = [];

    #[ORM\Column(type: Types::DATE_IMMUTABLE, options: ['comment' => '整改期限'])]
    private \DateTimeInterface $correctionDeadline;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '整改状态'], nullable: false)]
    private string $correctionStatus = '待整改';

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '整改证据'])]
    private ?array $correctionEvidence = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true, options: ['comment' => '整改日期'])]
    private ?\DateTimeInterface $correctionDate = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '验证结果：通过、不通过、部分通过'])]
    private ?string $verificationResult = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true, options: ['comment' => '验证日期'])]
    private ?\DateTimeInterface $verificationDate = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '验证人'])]
    private ?string $verifier = null;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '责任人'])]
    private string $responsiblePerson;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注信息'])]
    private ?string $remarks = null;

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

    // Alias for EasyAdmin compatibility
    public function getSupervisionInspection(): SupervisionInspection
    {
        return $this->inspection;
    }

    public function setSupervisionInspection(SupervisionInspection $inspection): static
    {
        $this->inspection = $inspection;
        return $this;
    }

    public function getProblemTitle(): string
    {
        return $this->problemTitle;
    }

    public function setProblemTitle(string $problemTitle): static
    {
        $this->problemTitle = $problemTitle;
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

    public function getProblemStatus(): string
    {
        return $this->problemStatus;
    }

    public function setProblemStatus(string $problemStatus): static
    {
        $this->problemStatus = $problemStatus;
        return $this;
    }

    public function getDiscoveryDate(): \DateTimeInterface
    {
        return $this->discoveryDate;
    }

    public function setDiscoveryDate(\DateTimeInterface $discoveryDate): static
    {
        $this->discoveryDate = $discoveryDate;
        return $this;
    }

    public function getExpectedResolutionDate(): ?\DateTimeInterface
    {
        return $this->expectedResolutionDate;
    }

    public function setExpectedResolutionDate(?\DateTimeInterface $expectedResolutionDate): static
    {
        $this->expectedResolutionDate = $expectedResolutionDate;
        return $this;
    }

    public function getActualResolutionDate(): ?\DateTimeInterface
    {
        return $this->actualResolutionDate;
    }

    public function setActualResolutionDate(?\DateTimeInterface $actualResolutionDate): static
    {
        $this->actualResolutionDate = $actualResolutionDate;
        return $this;
    }

    public function getRootCauseAnalysis(): ?string
    {
        return $this->rootCauseAnalysis;
    }

    public function setRootCauseAnalysis(?string $rootCauseAnalysis): static
    {
        $this->rootCauseAnalysis = $rootCauseAnalysis;
        return $this;
    }

    public function getPreventiveMeasures(): ?string
    {
        return $this->preventiveMeasures;
    }

    public function setPreventiveMeasures(?string $preventiveMeasures): static
    {
        $this->preventiveMeasures = $preventiveMeasures;
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

    // Alias methods for backward compatibility
    public function getFoundDate(): \DateTimeInterface
    {
        return $this->discoveryDate;
    }

    public function getDeadline(): \DateTimeInterface
    {
        return $this->correctionDeadline;
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
        return ($diff->invert === 1) ? -$diff->days : $diff->days;
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
