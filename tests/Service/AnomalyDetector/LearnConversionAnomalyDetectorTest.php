<?php

namespace Tourze\TrainSupervisorBundle\Tests\Service\AnomalyDetector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainSupervisorBundle\Service\AnomalyDetector\LearnConversionAnomalyDetector;

/**
 * @internal
 */
#[CoversClass(LearnConversionAnomalyDetector::class)]
#[RunTestsInSeparateProcesses]
final class LearnConversionAnomalyDetectorTest extends AbstractIntegrationTestCase
{
    private LearnConversionAnomalyDetector $detector;

    protected function onSetUp(): void
    {
        // 直接使用容器中的服务实例，依赖真实的SupervisorService
        $this->detector = self::getService(LearnConversionAnomalyDetector::class);
    }

    public function testDetectorCanBeInstantiated(): void
    {
        $this->assertInstanceOf(LearnConversionAnomalyDetector::class, $this->detector);
    }

    public function testGetType(): void
    {
        $this->assertEquals('learn', $this->detector->getType());
    }

    public function testDetectWithNoData(): void
    {
        // 使用真实的SupervisorService进行测试
        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-01-01');
        $thresholds = ['learn_conversion_rate' => 50.0];

        $result = $this->detector->detect($startDate, $endDate, $thresholds);

        // 验证返回的是数组
        $this->assertIsArray($result);
    }
}
