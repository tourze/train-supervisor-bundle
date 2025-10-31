<?php

namespace Tourze\TrainSupervisorBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainSupervisorBundle\Service\LearningStatisticsService;

/**
 * @internal
 */
#[CoversClass(LearningStatisticsService::class)]
#[RunTestsInSeparateProcesses]
final class LearningStatisticsServiceTest extends AbstractIntegrationTestCase
{
    private LearningStatisticsService $learningStatisticsService;

    protected function onSetUp(): void
    {
        $this->learningStatisticsService = self::getService(LearningStatisticsService::class);
    }

    public function testGetLearningStatistics(): void
    {
        $filters = [
            'institution_id' => 'supplier123',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ];

        $result = $this->learningStatisticsService->getLearningStatistics($filters);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('enrollment', $result);
        $this->assertArrayHasKey('completion', $result);
        $this->assertArrayHasKey('online', $result);
        $this->assertArrayHasKey('summary', $result);
    }

    public function testGetLearningTrends(): void
    {
        $filters = [
            'institution_id' => 'supplier123',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ];
        $periodType = 'daily';

        $result = $this->learningStatisticsService->getLearningTrends($filters, $periodType);

        $this->assertIsArray($result);
    }

    public function testGetEnrollmentStatistics(): void
    {
        $filters = [
            'institution_id' => 'supplier123',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ];

        $result = $this->learningStatisticsService->getEnrollmentStatistics($filters);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('total_enrolled', $result);
        $this->assertArrayHasKey('by_period', $result);
        $this->assertArrayHasKey('by_institution', $result);
        $this->assertArrayHasKey('growth_rate', $result);
    }

    public function testGetCompletedLearningStatistics(): void
    {
        $filters = [
            'institution_id' => 'supplier123',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ];

        $result = $this->learningStatisticsService->getCompletedLearningStatistics($filters);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('total_completed', $result);
        $this->assertArrayHasKey('completion_rate', $result);
    }

    public function testGetOnlineLearningStatistics(): void
    {
        $filters = [
            'institution_id' => 'supplier123',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ];

        $result = $this->learningStatisticsService->getOnlineLearningStatistics($filters);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('current_online', $result);
        $this->assertArrayHasKey('peak_online', $result);
    }

    public function testGetStatisticsByInstitution(): void
    {
        $filters = [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ];

        $result = $this->learningStatisticsService->getStatisticsByInstitution($filters);

        $this->assertIsArray($result);
    }

    public function testGetLearningOverview(): void
    {
        $filters = [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ];

        $result = $this->learningStatisticsService->getLearningOverview($filters);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('enrollment', $result);
        $this->assertArrayHasKey('completion', $result);
        $this->assertArrayHasKey('online', $result);
        $this->assertArrayHasKey('institutions', $result);
    }

    public function testExportLearningStatistics(): void
    {
        $filters = [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ];
        $format = 'csv';

        $result = $this->learningStatisticsService->exportLearningStatistics($filters, $format);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('mime_type', $result);
    }

    public function testGetRealtimeStatistics(): void
    {
        $filters = [];

        $result = $this->learningStatisticsService->getRealtimeStatistics($filters);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('today', $result);
        $this->assertArrayHasKey('yesterday', $result);
        $this->assertArrayHasKey('comparison', $result);
        $this->assertArrayHasKey('timestamp', $result);
    }
}
