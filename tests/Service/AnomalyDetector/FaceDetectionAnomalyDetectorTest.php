<?php

namespace Tourze\TrainSupervisorBundle\Tests\Service\AnomalyDetector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainSupervisorBundle\Service\AnomalyDetector\FaceDetectionAnomalyDetector;

/**
 * @internal
 */
#[CoversClass(FaceDetectionAnomalyDetector::class)]
#[RunTestsInSeparateProcesses]
final class FaceDetectionAnomalyDetectorTest extends AbstractIntegrationTestCase
{
    private FaceDetectionAnomalyDetector $detector;

    protected function onSetUp(): void
    {
        // 直接使用容器中的服务实例，依赖真实的SupervisorService
        $this->detector = self::getService(FaceDetectionAnomalyDetector::class);
    }

    public function testDetectorCanBeInstantiated(): void
    {
        $this->assertInstanceOf(FaceDetectionAnomalyDetector::class, $this->detector);
    }

    public function testGetType(): void
    {
        $this->assertEquals('face', $this->detector->getType());
    }

    public function testDetectWithNoData(): void
    {
        // 使用真实的SupervisorService进行测试
        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-01-01');
        $thresholds = ['face_fail_rate' => 20.0];

        $result = $this->detector->detect($startDate, $endDate, $thresholds);

        // 验证返回的是数组
        $this->assertIsArray($result);
    }
}
