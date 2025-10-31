<?php

namespace Tourze\TrainSupervisorBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainSupervisorBundle\Service\AnomalyAlertService;

/**
 * @internal
 */
#[CoversClass(AnomalyAlertService::class)]
#[RunTestsInSeparateProcesses]
final class AnomalyAlertServiceTest extends AbstractIntegrationTestCase
{
    private AnomalyAlertService $anomalyAlertService;

    protected function onSetUp(): void
    {
        $this->anomalyAlertService = self::getService(AnomalyAlertService::class);
    }

    public function testSendAnomalyAlertsWithNoSevereAnomalies(): void
    {
        $anomalies = [
            [
                'severity' => '轻微',
                'description' => 'Minor anomaly',
            ],
            [
                'severity' => '一般',
                'description' => 'Normal anomaly',
            ],
        ];

        $io = $this->createMock(SymfonyStyle::class);
        $io->expects($this->once())->method('section');
        $io->expects($this->once())->method('info');

        $this->anomalyAlertService->sendAnomalyAlerts($anomalies, $io);
    }

    public function testSendAnomalyAlertsWithSevereAnomalies(): void
    {
        $anomalies = [
            [
                'severity' => '严重',
                'description' => 'Critical anomaly',
            ],
            [
                'severity' => '重要',
                'description' => 'Important anomaly',
            ],
            [
                'severity' => '轻微',
                'description' => 'Minor anomaly',
            ],
        ];

        $io = $this->createMock(SymfonyStyle::class);
        $io->expects($this->once())->method('section');
        $io->expects($this->exactly(3))->method('text');
        $io->expects($this->once())->method('success');

        $this->anomalyAlertService->sendAnomalyAlerts($anomalies, $io);
    }

    public function testSendAnomalyAlertsWithEmptyArray(): void
    {
        $io = $this->createMock(SymfonyStyle::class);
        $io->expects($this->once())->method('section');
        $io->expects($this->once())->method('info');

        $this->anomalyAlertService->sendAnomalyAlerts([], $io);
    }
}
