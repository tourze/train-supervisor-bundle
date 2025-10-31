<?php

namespace Tourze\TrainSupervisorBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainSupervisorBundle\Service\AnomalyDetectionService;

/**
 * @internal
 */
#[CoversClass(AnomalyDetectionService::class)]
#[RunTestsInSeparateProcesses]
final class AnomalyDetectionServiceTest extends AbstractIntegrationTestCase
{
    private AnomalyDetectionService $anomalyDetectionService;

    protected function onSetUp(): void
    {
        $this->anomalyDetectionService = self::getService(AnomalyDetectionService::class);
    }

    public function testDetectAnomaliesWithAllType(): void
    {
        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-01-01');
        $thresholds = [
            'problem_overdue_days' => 30,
            'cheat_rate_threshold' => 0.1,
            'face_detection_threshold' => 0.8,
        ];

        $result = $this->anomalyDetectionService->detectAnomalies($startDate, $endDate, 'all', $thresholds);

        $this->assertIsArray($result);
    }

    public function testDetectAnomaliesWithSpecificType(): void
    {
        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-01-01');
        $thresholds = [
            'problem_overdue_days' => 30,
            'cheat_rate_threshold' => 0.1,
            'face_detection_threshold' => 0.8,
        ];

        $result = $this->anomalyDetectionService->detectAnomalies($startDate, $endDate, 'cheat', $thresholds);

        $this->assertIsArray($result);
    }

    public function testDetectAnomaliesWithNoMatchingDetectors(): void
    {
        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-01-01');
        $thresholds = [
            'problem_overdue_days' => 30,
            'cheat_rate_threshold' => 0.1,
            'face_detection_threshold' => 0.8,
        ];

        $result = $this->anomalyDetectionService->detectAnomalies($startDate, $endDate, 'unknown', $thresholds);

        $this->assertIsArray($result);
    }
}
