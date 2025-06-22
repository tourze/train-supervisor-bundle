<?php

namespace Tourze\TrainSupervisorBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\TrainSupervisorBundle\Repository\SupervisionReportRepository;

/**
 * 监督报告实体
 * 用于生成和管理各类监督报告
 */
#[ORM\Entity(repositoryClass: SupervisionReportRepository::class)]
#[ORM\Table(name: 'job_training_supervision_report', options: ['comment' => '监督报告'])]
class SupervisionReport implements \Stringable
{
    use TimestampableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '报告类型'])]
    private string $reportType;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '报告标题'])]
    private string $reportTitle;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, options: ['comment' => '报告期间开始日期'])]
    private \DateTimeInterface $reportPeriodStart;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, options: ['comment' => '报告期间结束日期'])]
    private \DateTimeInterface $reportPeriodEnd;

    #[ORM\Column(type: Types::JSON, options: ['comment' => '监督数据'])]
    private array $supervisionData = [];

    #[ORM\Column(type: Types::JSON, options: ['comment' => '问题汇总'])]
    private array $problemSummary = [];

    #[ORM\Column(type: Types::JSON, options: ['comment' => '建议措施'])]
    private array $recommendations = [];

    #[ORM\Column(type: Types::JSON, options: ['comment' => '统计数据'])]
    private array $statisticsData = [];

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 50, options: ['comment' => '报告状态'])]
    private string $reportStatus = '草稿';

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '报告人'])]
    private string $reporter;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, options: ['comment' => '报告日期'])]
    private \DateTimeInterface $reportDate;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '报告内容'])]
    private ?string $reportContent = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '附件路径'])]
    private ?array $attachments = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注信息'])]
    private ?string $remarks = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getReportType(): string
    {
        return $this->reportType;
    }

    public function setReportType(string $reportType): static
    {
        $this->reportType = $reportType;
        return $this;
    }

    public function getReportTitle(): string
    {
        return $this->reportTitle;
    }

    public function setReportTitle(string $reportTitle): static
    {
        $this->reportTitle = $reportTitle;
        return $this;
    }

    public function getReportPeriodStart(): \DateTimeInterface
    {
        return $this->reportPeriodStart;
    }

    public function setReportPeriodStart(\DateTimeInterface $reportPeriodStart): static
    {
        $this->reportPeriodStart = $reportPeriodStart;
        return $this;
    }

    public function getReportPeriodEnd(): \DateTimeInterface
    {
        return $this->reportPeriodEnd;
    }

    public function setReportPeriodEnd(\DateTimeInterface $reportPeriodEnd): static
    {
        $this->reportPeriodEnd = $reportPeriodEnd;
        return $this;
    }

    public function getSupervisionData(): array
    {
        return $this->supervisionData;
    }

    public function setSupervisionData(array $supervisionData): static
    {
        $this->supervisionData = $supervisionData;
        return $this;
    }

    public function getProblemSummary(): array
    {
        return $this->problemSummary;
    }

    public function setProblemSummary(array $problemSummary): static
    {
        $this->problemSummary = $problemSummary;
        return $this;
    }

    public function getRecommendations(): array
    {
        return $this->recommendations;
    }

    public function setRecommendations(array $recommendations): static
    {
        $this->recommendations = $recommendations;
        return $this;
    }

    public function getStatisticsData(): array
    {
        return $this->statisticsData;
    }

    public function setStatisticsData(array $statisticsData): static
    {
        $this->statisticsData = $statisticsData;
        return $this;
    }

    public function getReportStatus(): string
    {
        return $this->reportStatus;
    }

    public function setReportStatus(string $reportStatus): static
    {
        $this->reportStatus = $reportStatus;
        return $this;
    }

    public function getReporter(): string
    {
        return $this->reporter;
    }

    public function setReporter(string $reporter): static
    {
        $this->reporter = $reporter;
        return $this;
    }

    public function getReportDate(): \DateTimeInterface
    {
        return $this->reportDate;
    }

    public function setReportDate(\DateTimeInterface $reportDate): static
    {
        $this->reportDate = $reportDate;
        return $this;
    }

    public function getReportContent(): ?string
    {
        return $this->reportContent;
    }

    public function setReportContent(?string $reportContent): static
    {
        $this->reportContent = $reportContent;
        return $this;
    }

    public function getAttachments(): ?array
    {
        return $this->attachments;
    }

    public function setAttachments(?array $attachments): static
    {
        $this->attachments = $attachments;
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
     * 检查报告是否已发布
     */
    public function isPublished(): bool
    {
        return $this->reportStatus === '已发布';
    }

    /**
     * 检查报告是否为草稿
     */
    public function isDraft(): bool
    {
        return $this->reportStatus === '草稿';
    }

    /**
     * 获取报告期间天数
     */
    public function getPeriodDays(): int
    {
        return $this->reportPeriodStart->diff($this->reportPeriodEnd)->days + 1;
    }

    /**
     * 获取问题总数
     */
    public function getTotalProblems(): int
    {
        return count($this->problemSummary);
    }

    /**
     * 获取建议措施数量
     */
    public function getRecommendationCount(): int
    {
        return count($this->recommendations);
    }

    /**
     * 检查是否有附件
     */
    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }

    /**
     * 获取附件数量
     */
    public function getAttachmentCount(): int
    {
        return ($this->attachments !== null) ? count($this->attachments) : 0;
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->reportType, $this->reportTitle);
    }
}
