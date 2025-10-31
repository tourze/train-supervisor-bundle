<?php

namespace Tourze\TrainSupervisorBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\TrainSupervisorBundle\Repository\ProblemTrackingRepository;

/**
 * 问题跟踪实体
 * 用于跟踪监督检查中发现的问题及其整改情况.
 */
#[ORM\Entity(repositoryClass: ProblemTrackingRepository::class)]
#[ORM\Table(name: 'job_training_problem_tracking', options: ['comment' => '问题跟踪'])]
class ProblemTracking implements \Stringable
{
    use TimestampableAware;
    use SnowflakeKeyAware;

    #[ORM\ManyToOne(targetEntity: SupervisionInspection::class)]
    #[ORM\JoinColumn(nullable: false)]
    private SupervisionInspection $inspection;

    #[Assert\NotBlank(message: '问题标题不能为空')]
    #[Assert\Length(max: 255, maxMessage: '问题标题不能超过255个字符')]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '问题标题'])]
    private string $problemTitle;

    #[Assert\NotBlank(message: '问题类型不能为空')]
    #[Assert\Length(max: 50, maxMessage: '问题类型不能超过50个字符')]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '问题类型'])]
    private string $problemType;

    #[Assert\NotBlank(message: '问题描述不能为空')]
    #[Assert\Length(max: 65535, maxMessage: '问题描述过长')]
    #[ORM\Column(type: Types::TEXT, options: ['comment' => '问题描述'])]
    private string $problemDescription;

    #[Assert\NotBlank(message: '问题严重程度不能为空')]
    #[Assert\Length(max: 50, maxMessage: '问题严重程度不能超过50个字符')]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '问题严重程度'])]
    private string $problemSeverity;

    #[Assert\NotBlank(message: '问题状态不能为空')]
    #[Assert\Length(max: 50, maxMessage: '问题状态不能超过50个字符')]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '问题状态'], nullable: false)]
    private string $problemStatus = '待处理';

    #[Assert\NotNull(message: '发现日期不能为空')]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, options: ['comment' => '发现日期'])]
    private \DateTimeInterface $discoveryDate;

    #[Assert\Type(type: \DateTimeInterface::class)]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true, options: ['comment' => '预期解决日期'])]
    private ?\DateTimeInterface $expectedResolutionDate = null;

    #[Assert\Type(type: \DateTimeInterface::class)]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true, options: ['comment' => '实际解决日期'])]
    private ?\DateTimeInterface $actualResolutionDate = null;

    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535, maxMessage: '根因分析过长')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '根因分析'])]
    private ?string $rootCauseAnalysis = null;

    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535, maxMessage: '预防措施过长')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '预防措施'])]
    private ?string $preventiveMeasures = null;

    /**
     * @var array<int, string>
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, options: ['comment' => '整改措施'])]
    private array $correctionMeasures = [];

    #[Assert\NotNull(message: '整改期限不能为空')]
    #[Assert\Type(type: \DateTimeInterface::class)]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, options: ['comment' => '整改期限'])]
    private \DateTimeInterface $correctionDeadline;

    #[Assert\NotBlank(message: '整改状态不能为空')]
    #[Assert\Length(max: 50, maxMessage: '整改状态不能超过50个字符')]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '整改状态'], nullable: false)]
    private string $correctionStatus = '待整改';

    /**
     * @var array<string, mixed>|null
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '整改证据'])]
    private ?array $correctionEvidence = null;

    #[Assert\Type(type: \DateTimeInterface::class)]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true, options: ['comment' => '整改日期'])]
    private ?\DateTimeInterface $correctionDate = null;

    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 50, maxMessage: '验证结果不能超过50个字符')]
    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '验证结果：通过、不通过、部分通过'])]
    private ?string $verificationResult = null;

    #[Assert\Type(type: \DateTimeInterface::class)]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true, options: ['comment' => '验证日期'])]
    private ?\DateTimeInterface $verificationDate = null;

    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 100, maxMessage: '验证人不能超过100个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '验证人'])]
    private ?string $verifier = null;

    #[Assert\NotBlank(message: '责任人不能为空')]
    #[Assert\Length(max: 100, maxMessage: '责任人不能超过100个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '责任人'])]
    private string $responsiblePerson;

    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535, maxMessage: '备注信息过长')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注信息'])]
    private ?string $remarks = null;

    public function getInspection(): SupervisionInspection
    {
        return $this->inspection;
    }

    public function setInspection(SupervisionInspection $inspection): void
    {
        $this->inspection = $inspection;
    }

    // Alias for EasyAdmin compatibility
    public function getSupervisionInspection(): SupervisionInspection
    {
        return $this->inspection;
    }

    public function setSupervisionInspection(SupervisionInspection $inspection): void
    {
        $this->inspection = $inspection;
    }

    public function getProblemTitle(): string
    {
        return $this->problemTitle;
    }

    public function setProblemTitle(string $problemTitle): void
    {
        $this->problemTitle = $problemTitle;
    }

    public function getProblemType(): string
    {
        return $this->problemType;
    }

    public function setProblemType(string $problemType): void
    {
        $this->problemType = $problemType;
    }

    public function getProblemDescription(): string
    {
        return $this->problemDescription;
    }

    public function setProblemDescription(string $problemDescription): void
    {
        $this->problemDescription = $problemDescription;
    }

    public function getProblemSeverity(): string
    {
        return $this->problemSeverity;
    }

    public function setProblemSeverity(string $problemSeverity): void
    {
        $this->problemSeverity = $problemSeverity;
    }

    public function getProblemStatus(): string
    {
        return $this->problemStatus;
    }

    public function setProblemStatus(string $problemStatus): void
    {
        $this->problemStatus = $problemStatus;
    }

    public function getDiscoveryDate(): \DateTimeInterface
    {
        return $this->discoveryDate;
    }

    public function setDiscoveryDate(\DateTimeInterface $discoveryDate): void
    {
        $this->discoveryDate = $discoveryDate;
    }

    public function getExpectedResolutionDate(): ?\DateTimeInterface
    {
        return $this->expectedResolutionDate;
    }

    public function setExpectedResolutionDate(?\DateTimeInterface $expectedResolutionDate): void
    {
        $this->expectedResolutionDate = $expectedResolutionDate;
    }

    public function getActualResolutionDate(): ?\DateTimeInterface
    {
        return $this->actualResolutionDate;
    }

    public function setActualResolutionDate(?\DateTimeInterface $actualResolutionDate): void
    {
        $this->actualResolutionDate = $actualResolutionDate;
    }

    public function getRootCauseAnalysis(): ?string
    {
        return $this->rootCauseAnalysis;
    }

    public function setRootCauseAnalysis(?string $rootCauseAnalysis): void
    {
        $this->rootCauseAnalysis = $rootCauseAnalysis;
    }

    public function getPreventiveMeasures(): ?string
    {
        return $this->preventiveMeasures;
    }

    public function setPreventiveMeasures(?string $preventiveMeasures): void
    {
        $this->preventiveMeasures = $preventiveMeasures;
    }

    /**
     * @return array<int, string>
     */
    public function getCorrectionMeasures(): array
    {
        return $this->correctionMeasures;
    }

    /**
     * @param array<int, string> $correctionMeasures
     */
    public function setCorrectionMeasures(array $correctionMeasures): void
    {
        $this->correctionMeasures = $correctionMeasures;
    }

    public function getCorrectionDeadline(): \DateTimeInterface
    {
        return $this->correctionDeadline;
    }

    public function setCorrectionDeadline(\DateTimeInterface $correctionDeadline): void
    {
        $this->correctionDeadline = $correctionDeadline;
    }

    public function getCorrectionStatus(): string
    {
        return $this->correctionStatus;
    }

    public function setCorrectionStatus(string $correctionStatus): void
    {
        $this->correctionStatus = $correctionStatus;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getCorrectionEvidence(): ?array
    {
        return $this->correctionEvidence;
    }

    /**
     * @param array<string, mixed>|null $correctionEvidence
     */
    public function setCorrectionEvidence(?array $correctionEvidence): void
    {
        $this->correctionEvidence = $correctionEvidence;
    }

    public function getCorrectionDate(): ?\DateTimeInterface
    {
        return $this->correctionDate;
    }

    public function setCorrectionDate(?\DateTimeInterface $correctionDate): void
    {
        $this->correctionDate = $correctionDate;
    }

    public function getVerificationResult(): ?string
    {
        return $this->verificationResult;
    }

    public function setVerificationResult(?string $verificationResult): void
    {
        $this->verificationResult = $verificationResult;
    }

    public function getVerificationDate(): ?\DateTimeInterface
    {
        return $this->verificationDate;
    }

    public function setVerificationDate(?\DateTimeInterface $verificationDate): void
    {
        $this->verificationDate = $verificationDate;
    }

    public function getVerifier(): ?string
    {
        return $this->verifier;
    }

    public function setVerifier(?string $verifier): void
    {
        $this->verifier = $verifier;
    }

    public function getResponsiblePerson(): string
    {
        return $this->responsiblePerson;
    }

    public function setResponsiblePerson(string $responsiblePerson): void
    {
        $this->responsiblePerson = $responsiblePerson;
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(?string $remarks): void
    {
        $this->remarks = $remarks;
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
     * 检查问题是否已整改.
     */
    public function isCorrected(): bool
    {
        return in_array($this->correctionStatus, ['已整改', '已验证', '已关闭'], true);
    }

    /**
     * 检查问题是否已验证
     */
    public function isVerified(): bool
    {
        return in_array($this->correctionStatus, ['已验证', '已关闭'], true);
    }

    /**
     * 检查问题是否已关闭.
     */
    public function isClosed(): bool
    {
        return '已关闭' === $this->correctionStatus;
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
     * 获取剩余天数.
     */
    public function getRemainingDays(): int
    {
        if ($this->isCorrected()) {
            return 0;
        }
        $now = new \DateTime();
        $diff = $now->diff($this->correctionDeadline);

        $days = $diff->days;
        if (false === $days) {
            return 0;
        }

        return (1 === $diff->invert) ? -$days : $days;
    }

    /**
     * 检查验证是否通过.
     */
    public function isVerificationPassed(): bool
    {
        return '通过' === $this->verificationResult;
    }

    /**
     * 获取整改措施数量.
     */
    public function getMeasureCount(): int
    {
        return count($this->correctionMeasures);
    }

    /**
     * 检查是否有整改证据.
     */
    public function hasEvidence(): bool
    {
        return null !== $this->correctionEvidence && [] !== $this->correctionEvidence;
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->problemType, substr($this->problemDescription, 0, 50));
    }
}
