<?php

namespace Tourze\TrainSupervisorBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainSupervisorBundle\Entity\SupervisionReport;
use Tourze\TrainSupervisorBundle\Repository\SupervisionReportRepository;

/**
 * @internal
 */
#[CoversClass(SupervisionReportRepository::class)]
#[RunTestsInSeparateProcesses]
final class SupervisionReportRepositoryTest extends AbstractRepositoryTestCase
{
    private SupervisionReportRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(SupervisionReportRepository::class);
    }

    public function testRepositoryIsService(): void
    {
        $this->assertInstanceOf(SupervisionReportRepository::class, $this->repository);
    }

    public function testSaveAndFindSupervisionReport(): void
    {
        $report = new SupervisionReport();
        $report->setReportType('月度报告');
        $report->setReportTitle('2024年1月监督报告');
        $report->setReportPeriodStart(new \DateTimeImmutable('2024-01-01'));
        $report->setReportPeriodEnd(new \DateTimeImmutable('2024-01-31'));
        $report->setReportDate(new \DateTimeImmutable('2024-02-01'));
        $report->setReportStatus('已发布');
        $report->setReporter('测试报告员');

        $this->repository->save($report);
        $this->assertNotNull($report->getId());

        $found = $this->repository->find($report->getId());
        $this->assertSame($report, $found);
        $this->assertEquals('月度报告', $found->getReportType());
        $this->assertEquals('2024年1月监督报告', $found->getReportTitle());
    }

    public function testFindPublishedReports(): void
    {
        $publishedReport = $this->createSupervisionReport('已发布');
        $draftReport = $this->createSupervisionReport('草稿');

        $this->repository->save($publishedReport, false);
        $this->repository->save($draftReport, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findPublishedReports();
        $this->assertContainsOnlyInstancesOf(SupervisionReport::class, $results);

        $statuses = array_map(fn ($report) => $report->getReportStatus(), $results);
        $this->assertContains('已发布', $statuses);
        $this->assertNotContains('草稿', $statuses);
    }

    public function testFindByType(): void
    {
        $monthlyReport = $this->createSupervisionReport('已发布', '月度报告');
        $quarterlyReport = $this->createSupervisionReport('已发布', '季度报告');

        $this->repository->save($monthlyReport, false);
        $this->repository->save($quarterlyReport, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findByType('月度报告');
        $this->assertContainsOnlyInstancesOf(SupervisionReport::class, $results);

        $types = array_map(fn ($report) => $report->getReportType(), $results);
        $this->assertContains('月度报告', $types);
        $this->assertNotContains('季度报告', $types);
    }

    public function testFindByPeriod(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-03-31');

        $report1 = $this->createSupervisionReport('已发布');
        $report1->setReportPeriodStart(new \DateTimeImmutable('2024-01-01'));
        $report1->setReportPeriodEnd(new \DateTimeImmutable('2024-01-31'));

        $report2 = $this->createSupervisionReport('已发布');
        $report2->setReportPeriodStart(new \DateTimeImmutable('2024-04-01'));
        $report2->setReportPeriodEnd(new \DateTimeImmutable('2024-04-30'));

        $this->repository->save($report1, false);
        $this->repository->save($report2, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findByPeriod($startDate, $endDate);
        $this->assertContainsOnlyInstancesOf(SupervisionReport::class, $results);

        $periodStarts = array_map(fn ($report) => $report->getReportPeriodStart(), $results);
        $this->assertContains($report1->getReportPeriodStart(), $periodStarts);
        $this->assertNotContains($report2->getReportPeriodStart(), $periodStarts);
    }

    public function testFindDraftReports(): void
    {
        $draftReport = $this->createSupervisionReport('草稿');
        $publishedReport = $this->createSupervisionReport('已发布');

        $this->repository->save($draftReport, false);
        $this->repository->save($publishedReport, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findDraftReports();
        $this->assertContainsOnlyInstancesOf(SupervisionReport::class, $results);

        $statuses = array_map(fn ($report) => $report->getReportStatus(), $results);
        $this->assertContains('草稿', $statuses);
        $this->assertNotContains('已发布', $statuses);
    }

    public function testCountByType(): void
    {
        $monthlyReport = $this->createSupervisionReport('已发布', '月度报告');
        $quarterlyReport = $this->createSupervisionReport('已发布', '季度报告');
        $annualReport = $this->createSupervisionReport('已发布', '年度报告');

        $this->repository->save($monthlyReport, false);
        $this->repository->save($quarterlyReport, false);
        $this->repository->save($annualReport, false);
        self::getEntityManager()->flush();

        $results = $this->repository->countByType();
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);

        // 验证返回的数据结构
        foreach ($results as $result) {
            $this->assertArrayHasKey('reportType', $result);
            $this->assertArrayHasKey('count', $result);
            $this->assertIsString($result['reportType']);
            $this->assertIsInt($result['count']);
        }

        $types = array_column($results, 'reportType');
        $this->assertContains('月度报告', $types);
        $this->assertContains('季度报告', $types);
        $this->assertContains('年度报告', $types);
    }

    public function testFindLatestReports(): void
    {
        $report1 = $this->createSupervisionReport('已发布');
        $report1->setReportDate(new \DateTimeImmutable('2024-01-01'));

        $report2 = $this->createSupervisionReport('已发布');
        $report2->setReportDate(new \DateTimeImmutable('2024-02-01'));

        $report3 = $this->createSupervisionReport('草稿');
        $report3->setReportDate(new \DateTimeImmutable('2024-03-01'));

        $this->repository->save($report1, false);
        $this->repository->save($report2, false);
        $this->repository->save($report3, false);
        self::getEntityManager()->flush();

        $results = $this->repository->findLatestReports(5);
        $this->assertContainsOnlyInstancesOf(SupervisionReport::class, $results);
        $this->assertLessThanOrEqual(5, count($results));

        // 验证只包含已发布的报告
        $statuses = array_map(fn ($report) => $report->getReportStatus(), $results);
        $this->assertContains('已发布', $statuses);
        $this->assertNotContains('草稿', $statuses);

        // 验证按日期降序排列
        if (count($results) >= 2) {
            $dates = array_map(fn ($report) => $report->getReportDate(), $results);
            $this->assertGreaterThanOrEqual($dates[1], $dates[0]);
        }
    }

    public function testRemoveSupervisionReport(): void
    {
        $report = $this->createSupervisionReport('草稿');
        $this->repository->save($report);
        $reportId = $report->getId();

        $this->repository->remove($report);

        $found = $this->repository->find($reportId);
        $this->assertNull($found);
    }

    protected function createNewEntity(): SupervisionReport
    {
        return $this->createSupervisionReport();
    }

    protected function getRepository(): SupervisionReportRepository
    {
        return $this->repository;
    }

    private function createSupervisionReport(
        string $status = '已发布',
        string $type = '月度报告',
    ): SupervisionReport {
        $report = new SupervisionReport();
        $report->setReportType($type);
        $report->setReportTitle('测试监督报告');
        $report->setReportPeriodStart(new \DateTimeImmutable('2024-01-01'));
        $report->setReportPeriodEnd(new \DateTimeImmutable('2024-01-31'));
        $report->setReportDate(new \DateTimeImmutable());
        $report->setReportStatus($status);
        $report->setReporter('测试报告员');

        return $report;
    }
}
