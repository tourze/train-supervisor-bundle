<?php

namespace Tourze\TrainSupervisorBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Entity\SupervisionReport;

class SupervisionReportTest extends TestCase
{
    private SupervisionReport $supervisionReport;

    protected function setUp(): void
    {
        $this->supervisionReport = new SupervisionReport();
    }

    public function testSetAndGetReportType(): void
    {
        $type = '月度监督报告';
        
        $result = $this->supervisionReport->setReportType($type);
        
        $this->assertSame($this->supervisionReport, $result);
        $this->assertSame($type, $this->supervisionReport->getReportType());
    }

    public function testSetAndGetReportTitle(): void
    {
        $title = '2024年1月安全生产培训监督报告';
        
        $result = $this->supervisionReport->setReportTitle($title);
        
        $this->assertSame($this->supervisionReport, $result);
        $this->assertSame($title, $this->supervisionReport->getReportTitle());
    }

    public function testSetAndGetReportPeriodStart(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        
        $result = $this->supervisionReport->setReportPeriodStart($startDate);
        
        $this->assertSame($this->supervisionReport, $result);
        $this->assertSame($startDate, $this->supervisionReport->getReportPeriodStart());
    }

    public function testSetAndGetReportPeriodEnd(): void
    {
        $endDate = new \DateTimeImmutable('2024-01-31');
        
        $result = $this->supervisionReport->setReportPeriodEnd($endDate);
        
        $this->assertSame($this->supervisionReport, $result);
        $this->assertSame($endDate, $this->supervisionReport->getReportPeriodEnd());
    }

    public function testSetAndGetSupervisionData(): void
    {
        $data = ['inspections' => 10, 'institutions' => 5];
        
        $result = $this->supervisionReport->setSupervisionData($data);
        
        $this->assertSame($this->supervisionReport, $result);
        $this->assertSame($data, $this->supervisionReport->getSupervisionData());
    }

    public function testSetAndGetProblemSummary(): void
    {
        $problems = ['教学质量问题', '师资不足', '设备老化'];
        
        $result = $this->supervisionReport->setProblemSummary($problems);
        
        $this->assertSame($this->supervisionReport, $result);
        $this->assertSame($problems, $this->supervisionReport->getProblemSummary());
    }

    public function testSetAndGetRecommendations(): void
    {
        $recommendations = ['加强师资培训', '更新教学设备', '完善质量管理体系'];
        
        $result = $this->supervisionReport->setRecommendations($recommendations);
        
        $this->assertSame($this->supervisionReport, $result);
        $this->assertSame($recommendations, $this->supervisionReport->getRecommendations());
    }

    public function testSetAndGetStatisticsData(): void
    {
        $statistics = ['total_score' => 85.5, 'pass_rate' => 92.3];
        
        $result = $this->supervisionReport->setStatisticsData($statistics);
        
        $this->assertSame($this->supervisionReport, $result);
        $this->assertSame($statistics, $this->supervisionReport->getStatisticsData());
    }

    public function testSetAndGetReportStatus(): void
    {
        $status = '已发布';
        
        $result = $this->supervisionReport->setReportStatus($status);
        
        $this->assertSame($this->supervisionReport, $result);
        $this->assertSame($status, $this->supervisionReport->getReportStatus());
    }

    public function testSetAndGetReporter(): void
    {
        $reporter = '李督导';
        
        $result = $this->supervisionReport->setReporter($reporter);
        
        $this->assertSame($this->supervisionReport, $result);
        $this->assertSame($reporter, $this->supervisionReport->getReporter());
    }

    public function testSetAndGetReportDate(): void
    {
        $date = new \DateTimeImmutable('2024-02-01');
        
        $result = $this->supervisionReport->setReportDate($date);
        
        $this->assertSame($this->supervisionReport, $result);
        $this->assertSame($date, $this->supervisionReport->getReportDate());
    }

    public function testSetAndGetReportContent(): void
    {
        $content = '本月共完成10次监督检查，发现3个主要问题...';
        
        $result = $this->supervisionReport->setReportContent($content);
        
        $this->assertSame($this->supervisionReport, $result);
        $this->assertSame($content, $this->supervisionReport->getReportContent());
    }

    public function testSetAndGetAttachments(): void
    {
        $attachments = ['report.pdf', 'photos.zip'];
        
        $result = $this->supervisionReport->setAttachments($attachments);
        
        $this->assertSame($this->supervisionReport, $result);
        $this->assertSame($attachments, $this->supervisionReport->getAttachments());
    }

    public function testSetAndGetRemarks(): void
    {
        $remarks = '需要重点关注A机构的整改情况';
        
        $result = $this->supervisionReport->setRemarks($remarks);
        
        $this->assertSame($this->supervisionReport, $result);
        $this->assertSame($remarks, $this->supervisionReport->getRemarks());
    }

    public function testIsPublishedWhenStatusIsPublished(): void
    {
        $this->supervisionReport->setReportStatus('已发布');
        
        $this->assertTrue($this->supervisionReport->isPublished());
    }

    public function testIsNotPublishedWhenStatusIsDraft(): void
    {
        $this->supervisionReport->setReportStatus('草稿');
        
        $this->assertFalse($this->supervisionReport->isPublished());
    }

    public function testIsDraftWhenStatusIsDraft(): void
    {
        $this->supervisionReport->setReportStatus('草稿');
        
        $this->assertTrue($this->supervisionReport->isDraft());
    }

    public function testIsNotDraftWhenStatusIsPublished(): void
    {
        $this->supervisionReport->setReportStatus('已发布');
        
        $this->assertFalse($this->supervisionReport->isDraft());
    }

    public function testGetPeriodDays(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');
        
        $this->supervisionReport
            ->setReportPeriodStart($startDate)
            ->setReportPeriodEnd($endDate);
        
        $this->assertSame(31, $this->supervisionReport->getPeriodDays());
    }

    public function testGetPeriodDaysForSingleDay(): void
    {
        $date = new \DateTimeImmutable('2024-01-01');
        
        $this->supervisionReport
            ->setReportPeriodStart($date)
            ->setReportPeriodEnd($date);
        
        $this->assertSame(1, $this->supervisionReport->getPeriodDays());
    }

    public function testGetTotalProblems(): void
    {
        $problems = ['问题1', '问题2', '问题3'];
        $this->supervisionReport->setProblemSummary($problems);
        
        $this->assertSame(3, $this->supervisionReport->getTotalProblems());
    }

    public function testGetTotalProblemsWhenEmpty(): void
    {
        $this->supervisionReport->setProblemSummary([]);
        
        $this->assertSame(0, $this->supervisionReport->getTotalProblems());
    }

    public function testGetRecommendationCount(): void
    {
        $recommendations = ['建议1', '建议2'];
        $this->supervisionReport->setRecommendations($recommendations);
        
        $this->assertSame(2, $this->supervisionReport->getRecommendationCount());
    }

    public function testGetRecommendationCountWhenEmpty(): void
    {
        $this->supervisionReport->setRecommendations([]);
        
        $this->assertSame(0, $this->supervisionReport->getRecommendationCount());
    }

    public function testHasAttachmentsWhenAttachmentsExist(): void
    {
        $attachments = ['file1.pdf', 'file2.doc'];
        $this->supervisionReport->setAttachments($attachments);
        
        $this->assertTrue($this->supervisionReport->hasAttachments());
    }

    public function testHasNoAttachmentsWhenAttachmentsIsEmpty(): void
    {
        $this->supervisionReport->setAttachments([]);
        
        $this->assertFalse($this->supervisionReport->hasAttachments());
    }

    public function testHasNoAttachmentsWhenAttachmentsIsNull(): void
    {
        $this->supervisionReport->setAttachments(null);
        
        $this->assertFalse($this->supervisionReport->hasAttachments());
    }

    public function testGetAttachmentCount(): void
    {
        $attachments = ['file1.pdf', 'file2.doc', 'file3.jpg'];
        $this->supervisionReport->setAttachments($attachments);
        
        $this->assertSame(3, $this->supervisionReport->getAttachmentCount());
    }

    public function testGetAttachmentCountWhenEmpty(): void
    {
        $this->supervisionReport->setAttachments([]);
        
        $this->assertSame(0, $this->supervisionReport->getAttachmentCount());
    }

    public function testGetAttachmentCountWhenNull(): void
    {
        $this->supervisionReport->setAttachments(null);
        
        $this->assertSame(0, $this->supervisionReport->getAttachmentCount());
    }

    public function testToString(): void
    {
        $this->supervisionReport->setReportType('月度报告');
        $this->supervisionReport->setReportTitle('2024年1月监督报告');
        
        $result = (string) $this->supervisionReport;
        
        $this->assertSame('月度报告 - 2024年1月监督报告', $result);
    }

    public function testDefaultValues(): void
    {
        $this->assertSame('草稿', $this->supervisionReport->getReportStatus());
        $this->assertSame([], $this->supervisionReport->getSupervisionData());
        $this->assertSame([], $this->supervisionReport->getProblemSummary());
        $this->assertSame([], $this->supervisionReport->getRecommendations());
        $this->assertSame([], $this->supervisionReport->getStatisticsData());
        $this->assertNull($this->supervisionReport->getReportContent());
        $this->assertNull($this->supervisionReport->getAttachments());
        $this->assertNull($this->supervisionReport->getRemarks());
    }
}