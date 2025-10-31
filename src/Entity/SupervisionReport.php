<?php

namespace Tourze\TrainSupervisorBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\TrainSupervisorBundle\Repository\SupervisionReportRepository;

/**
 * 监督报告实体
 * 用于生成和管理各类监督报告.
 */
#[ORM\Entity(repositoryClass: SupervisionReportRepository::class)]
#[ORM\Table(name: 'train_supervision_report', options: ['comment' => '监督报告'])]
class SupervisionReport implements \Stringable
{
    use TimestampableAware;
    use SnowflakeKeyAware;

    #[Assert\NotBlank(message: '报告类型不能为空')]
    #[Assert\Length(max: 50, maxMessage: '报告类型不能超过50个字符')]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '报告类型'])]
    private string $reportType;

    #[Assert\NotBlank(message: '报告标题不能为空')]
    #[Assert\Length(max: 255, maxMessage: '报告标题不能超过255个字符')]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '报告标题'])]
    private string $reportTitle;

    #[Assert\NotNull(message: '报告期间开始日期不能为空')]
    #[Assert\Type(type: \DateTimeInterface::class)]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, options: ['comment' => '报告期间开始日期'])]
    private \DateTimeInterface $reportPeriodStart;

    #[Assert\NotNull(message: '报告期间结束日期不能为空')]
    #[Assert\Type(type: \DateTimeInterface::class)]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, options: ['comment' => '报告期间结束日期'])]
    private \DateTimeInterface $reportPeriodEnd;

    /**
     * @var array<string, mixed>
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, options: ['comment' => '监督数据'])]
    private array $supervisionData = [];

    /**
     * @var array<string, mixed>
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, options: ['comment' => '问题汇总'])]
    private array $problemSummary = [];

    /**
     * @var array<string, mixed>
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, options: ['comment' => '建议措施'])]
    private array $recommendations = [];

    /**
     * @var array<string, mixed>
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, options: ['comment' => '统计数据'])]
    private array $statisticsData = [];

    #[Assert\NotBlank(message: '报告状态不能为空')]
    #[Assert\Length(max: 50, maxMessage: '报告状态不能超过50个字符')]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '报告状态'])]
    private string $reportStatus = '草稿';

    #[Assert\NotBlank(message: '报告人不能为空')]
    #[Assert\Length(max: 100, maxMessage: '报告人不能超过100个字符')]
    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '报告人'])]
    private string $reporter;

    #[Assert\NotNull(message: '报告日期不能为空')]
    #[Assert\Type(type: \DateTimeInterface::class)]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, options: ['comment' => '报告日期'])]
    private \DateTimeInterface $reportDate;

    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535, maxMessage: '报告内容过长')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '报告内容'])]
    private ?string $reportContent = null;

    /**
     * @var array<string, mixed>|null
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '附件路径'])]
    private ?array $attachments = null;

    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535, maxMessage: '备注信息不能超过65535个字符')]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注信息'])]
    private ?string $remarks = null;

    public function getReportType(): string
    {
        return $this->reportType;
    }

    public function setReportType(string $reportType): void
    {
        $this->reportType = $reportType;
    }

    public function getReportTitle(): string
    {
        return $this->reportTitle;
    }

    public function setReportTitle(string $reportTitle): void
    {
        $this->reportTitle = $reportTitle;
    }

    public function getReportPeriodStart(): \DateTimeInterface
    {
        return $this->reportPeriodStart;
    }

    public function setReportPeriodStart(\DateTimeInterface $reportPeriodStart): void
    {
        $this->reportPeriodStart = $reportPeriodStart;
    }

    public function getReportPeriodEnd(): \DateTimeInterface
    {
        return $this->reportPeriodEnd;
    }

    public function setReportPeriodEnd(\DateTimeInterface $reportPeriodEnd): void
    {
        $this->reportPeriodEnd = $reportPeriodEnd;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSupervisionData(): array
    {
        return $this->supervisionData;
    }

    /**
     * @param array<string, mixed> $supervisionData
     */
    public function setSupervisionData(array $supervisionData): void
    {
        $this->supervisionData = $supervisionData;
    }

    /**
     * @return array<string, mixed>
     */
    public function getProblemSummary(): array
    {
        return $this->problemSummary;
    }

    /**
     * @param array<string, mixed> $problemSummary
     */
    public function setProblemSummary(array $problemSummary): void
    {
        $this->problemSummary = $problemSummary;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRecommendations(): array
    {
        return $this->recommendations;
    }

    /**
     * @param array<string, mixed> $recommendations
     */
    public function setRecommendations(array $recommendations): void
    {
        $this->recommendations = $recommendations;
    }

    /**
     * @return array<string, mixed>
     */
    public function getStatisticsData(): array
    {
        return $this->statisticsData;
    }

    /**
     * @param array<string, mixed> $statisticsData
     */
    public function setStatisticsData(array $statisticsData): void
    {
        $this->statisticsData = $statisticsData;
    }

    public function getReportStatus(): string
    {
        return $this->reportStatus;
    }

    public function setReportStatus(string $reportStatus): void
    {
        $this->reportStatus = $reportStatus;
    }

    public function getReporter(): string
    {
        return $this->reporter;
    }

    public function setReporter(string $reporter): void
    {
        $this->reporter = $reporter;
    }

    public function getReportDate(): \DateTimeInterface
    {
        return $this->reportDate;
    }

    public function setReportDate(\DateTimeInterface $reportDate): void
    {
        $this->reportDate = $reportDate;
    }

    public function getReportContent(): ?string
    {
        return $this->reportContent;
    }

    public function setReportContent(?string $reportContent): void
    {
        $this->reportContent = $reportContent;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getAttachments(): ?array
    {
        return $this->attachments;
    }

    /**
     * @param array<string, mixed>|null $attachments
     */
    public function setAttachments(?array $attachments): void
    {
        $this->attachments = $attachments;
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
     * 检查报告是否已发布.
     */
    public function isPublished(): bool
    {
        return '已发布' === $this->reportStatus;
    }

    /**
     * 检查报告是否为草稿
     */
    public function isDraft(): bool
    {
        return '草稿' === $this->reportStatus;
    }

    /**
     * 获取报告期间天数.
     */
    public function getPeriodDays(): int
    {
        $days = $this->reportPeriodStart->diff($this->reportPeriodEnd)->days;

        return (false === $days ? 0 : $days) + 1;
    }

    /**
     * 获取问题总数.
     */
    public function getTotalProblems(): int
    {
        if (isset($this->problemSummary['items']) && is_array($this->problemSummary['items'])) {
            return count($this->problemSummary['items']);
        }

        return 0;
    }

    /**
     * 获取建议措施数量.
     */
    public function getRecommendationCount(): int
    {
        if (isset($this->recommendations['items']) && is_array($this->recommendations['items'])) {
            return count($this->recommendations['items']);
        }

        return 0;
    }

    /**
     * 检查是否有附件.
     */
    public function hasAttachments(): bool
    {
        return null !== $this->attachments && [] !== $this->attachments;
    }

    /**
     * 获取附件数量.
     */
    public function getAttachmentCount(): int
    {
        if (null !== $this->attachments && isset($this->attachments['files']) && is_array($this->attachments['files'])) {
            return count($this->attachments['files']);
        }

        return 0;
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->reportType, $this->reportTitle);
    }
}
