<?php

namespace Tourze\TrainSupervisorBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainSupervisorBundle\Service\AnomalyDisplayService;

/**
 * @internal
 */
#[CoversClass(AnomalyDisplayService::class)]
#[RunTestsInSeparateProcesses]
final class AnomalyDisplayServiceTest extends AbstractIntegrationTestCase
{
    private AnomalyDisplayService $anomalyDisplayService;

    protected function onSetUp(): void
    {
        $this->anomalyDisplayService = self::getService(AnomalyDisplayService::class);
    }

    public function testDisplayAnomalies(): void
    {
        $anomalies = [
            [
                'type' => 'cheat',
                'description' => 'Test anomaly',
                'severity' => '严重',
                'supplier_name' => 'Test Supplier',
                'date' => '2024-01-01',
                'value' => 10.5,
            ],
        ];
        $verbose = false;
        $io = $this->createMock(SymfonyStyle::class);

        $io->expects($this->atLeastOnce())
            ->method('section')
        ;

        $io->expects($this->atLeastOnce())
            ->method('table')
        ;

        $this->anomalyDisplayService->displayAnomalies($anomalies, $verbose, $io);
    }

    public function testDisplayAnomaliesWithVerboseMode(): void
    {
        $anomalies = [
            [
                'type' => 'cheat',
                'description' => 'Test anomaly',
                'severity' => '严重',
                'supplier_name' => 'Test Supplier',
                'date' => '2024-01-01',
                'value' => 10.5,
                'details' => ['key' => 'value'],
            ],
        ];
        $verbose = true;
        $io = $this->createMock(SymfonyStyle::class);

        $io->expects($this->atLeastOnce())
            ->method('section')
        ;

        $io->expects($this->atLeastOnce())
            ->method('text')
        ;

        $this->anomalyDisplayService->displayAnomalies($anomalies, $verbose, $io);
    }

    public function testDisplayAnomaliesWithEmptyArray(): void
    {
        $anomalies = [];
        $verbose = false;
        $io = $this->createMock(SymfonyStyle::class);

        $io->expects($this->never())
            ->method('section')
        ;

        $this->anomalyDisplayService->displayAnomalies($anomalies, $verbose, $io);
    }
}
