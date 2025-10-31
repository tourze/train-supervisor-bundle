<?php

namespace Tourze\TrainSupervisorBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainSupervisorBundle\Entity\SupervisionReport;
use Tourze\TrainSupervisorBundle\Service\ReportService;

/**
 * 监督报告服务测试.
 *
 * @internal
 */
#[CoversClass(ReportService::class)]
#[RunTestsInSeparateProcesses]
final class ReportServiceTest extends AbstractIntegrationTestCase
{
    private ReportService $reportService;

    protected function onSetUp(): void
    {
        $this->reportService = self::getService(ReportService::class);
    }

    public function testGenerateDailyReport(): void
    {
        $date = new \DateTimeImmutable('2024-06-15');
        $reporter = '张三';

        $result = $this->reportService->generateDailyReport($date, $reporter);

        $this->assertInstanceOf(SupervisionReport::class, $result);
        $this->assertEquals('日报', $result->getReportType());
        $this->assertEquals($reporter, $result->getReporter());
        $this->assertStringContainsString('2024年06月15日', $result->getReportTitle());
    }

    public function testGenerateWeeklyReport(): void
    {
        $weekStart = new \DateTimeImmutable('2024-06-10');
        $reporter = '李四';

        $result = $this->reportService->generateWeeklyReport($weekStart, $reporter);

        $this->assertInstanceOf(SupervisionReport::class, $result);
        $this->assertEquals('周报', $result->getReportType());
        $this->assertEquals($reporter, $result->getReporter());
        $this->assertStringContainsString('2024年06月10日至', $result->getReportTitle());
    }

    public function testGenerateMonthlyReport(): void
    {
        $year = 2024;
        $month = 6;
        $reporter = '王五';

        $result = $this->reportService->generateMonthlyReport($year, $month, $reporter);

        $this->assertInstanceOf(SupervisionReport::class, $result);
        $this->assertEquals('月报', $result->getReportType());
        $this->assertEquals($reporter, $result->getReporter());
        $this->assertEquals('2024年6月 培训监督月报', $result->getReportTitle());
    }

    public function testGenerateSpecialReport(): void
    {
        $reportTitle = '安全培训专项检查报告';
        $startDate = new \DateTimeImmutable('2024-06-01');
        $endDate = new \DateTimeImmutable('2024-06-30');
        $reporter = '赵六';
        $specialCriteria = ['category' => '安全培训'];

        $result = $this->reportService->generateSpecialReport($reportTitle, $startDate, $endDate, $reporter, $specialCriteria);

        $this->assertInstanceOf(SupervisionReport::class, $result);
        $this->assertEquals('专项报告', $result->getReportType());
        $this->assertEquals($reportTitle, $result->getReportTitle());
        $this->assertEquals($reporter, $result->getReporter());
    }

    public function testPublishReport(): void
    {
        $report = new SupervisionReport();
        $report->setReportStatus('草稿');

        $this->reportService->publishReport($report);

        $this->assertEquals('已发布', $report->getReportStatus());
    }

    public function testArchiveReport(): void
    {
        $report = new SupervisionReport();
        $report->setReportStatus('已发布');

        $this->reportService->archiveReport($report);

        $this->assertEquals('已归档', $report->getReportStatus());
    }
}
