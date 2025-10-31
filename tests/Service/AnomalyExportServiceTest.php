<?php

namespace Tourze\TrainSupervisorBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainSupervisorBundle\Service\AnomalyExportService;

/**
 * @internal
 */
#[CoversClass(AnomalyExportService::class)]
#[RunTestsInSeparateProcesses]
final class AnomalyExportServiceTest extends AbstractIntegrationTestCase
{
    private AnomalyExportService $anomalyExportService;

    protected function onSetUp(): void
    {
        $this->anomalyExportService = self::getService(AnomalyExportService::class);
    }

    public function testExportAnomalies(): void
    {
        $anomalies = [
            [
                'type' => 'cheat',
                'severity' => '严重',
                'supplier_name' => 'Test Supplier',
                'date' => '2024-01-01',
                'value' => 10.5,
                'threshold' => 5.0,
                'description' => 'Test anomaly',
            ],
        ];
        $exportFile = sys_get_temp_dir() . '/test_anomalies.csv';
        $io = $this->createMock(SymfonyStyle::class);

        $io->expects($this->once())
            ->method('section')
            ->with('导出异常报告')
        ;

        $io->expects($this->once())
            ->method('success')
        ;

        $io->expects($this->once())
            ->method('text')
        ;

        $this->anomalyExportService->exportAnomalies($anomalies, $exportFile, $io);

        $this->assertFileExists($exportFile);
        unlink($exportFile);
    }

    public function testExportAnomaliesToJson(): void
    {
        $anomalies = [
            [
                'type' => 'cheat',
                'severity' => '严重',
                'supplier_name' => 'Test Supplier',
                'date' => '2024-01-01',
                'value' => 10.5,
                'threshold' => 5.0,
                'description' => 'Test anomaly',
            ],
        ];
        $exportFile = sys_get_temp_dir() . '/test_anomalies.json';
        $io = $this->createMock(SymfonyStyle::class);

        $io->expects($this->once())
            ->method('section')
        ;

        $io->expects($this->once())
            ->method('success')
        ;

        $this->anomalyExportService->exportAnomalies($anomalies, $exportFile, $io);

        $this->assertFileExists($exportFile);
        $content = file_get_contents($exportFile);
        $this->assertNotFalse($content);
        $this->assertJson($content);
        unlink($exportFile);
    }

    public function testExportAnomaliesWithUnsupportedFormat(): void
    {
        $anomalies = [
            [
                'type' => 'cheat',
                'severity' => '严重',
                'supplier_name' => 'Test Supplier',
                'date' => '2024-01-01',
                'value' => 10.5,
                'threshold' => 5.0,
                'description' => 'Test anomaly',
            ],
        ];
        $exportFile = sys_get_temp_dir() . '/test_anomalies.txt';
        $io = $this->createMock(SymfonyStyle::class);

        $io->expects($this->once())
            ->method('section')
        ;

        $io->expects($this->once())
            ->method('error')
        ;

        $this->anomalyExportService->exportAnomalies($anomalies, $exportFile, $io);
    }
}
