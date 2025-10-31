<?php

namespace Tourze\TrainSupervisorBundle\Tests\Service\AnomalyDetector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainSupervisorBundle\Service\AnomalyDetector\ProblemAnomalyDetector;

/**
 * @internal
 */
#[CoversClass(ProblemAnomalyDetector::class)]
#[RunTestsInSeparateProcesses]
final class ProblemAnomalyDetectorTest extends AbstractIntegrationTestCase
{
    private ProblemAnomalyDetector $detector;

    protected function onSetUp(): void
    {
        // 直接使用容器中的服务实例，依赖真实的ProblemTrackingService
        $this->detector = self::getService(ProblemAnomalyDetector::class);
    }

    public function testDetectorCanBeInstantiated(): void
    {
        $this->assertInstanceOf(ProblemAnomalyDetector::class, $this->detector);
    }

    public function testGetType(): void
    {
        $this->assertEquals('problem', $this->detector->getType());
    }

    public function testDetectWithNoData(): void
    {
        // 使用真实的ProblemTrackingService进行测试
        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-01-01');
        $thresholds = ['problem_overdue_days' => 3];

        $result = $this->detector->detect($startDate, $endDate, $thresholds);

        // 验证返回的是数组
        $this->assertIsArray($result);
    }
}
