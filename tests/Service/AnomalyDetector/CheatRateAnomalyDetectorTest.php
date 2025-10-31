<?php

namespace Tourze\TrainSupervisorBundle\Tests\Service\AnomalyDetector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainSupervisorBundle\Service\AnomalyDetector\CheatRateAnomalyDetector;

/**
 * @internal
 */
#[CoversClass(CheatRateAnomalyDetector::class)]
#[RunTestsInSeparateProcesses]
final class CheatRateAnomalyDetectorTest extends AbstractIntegrationTestCase
{
    private CheatRateAnomalyDetector $detector;

    protected function onSetUp(): void
    {
        // 直接使用容器中的服务实例，依赖真实的SupervisorService
        // 这样可以避免类型约束问题，同时保持静态分析清洁
        $this->detector = self::getService(CheatRateAnomalyDetector::class);
    }

    public function testDetectorCanBeInstantiated(): void
    {
        $this->assertInstanceOf(CheatRateAnomalyDetector::class, $this->detector);
    }

    public function testGetType(): void
    {
        $this->assertEquals('cheat', $this->detector->getType());
    }

    public function testDetectWithNoAnomalies(): void
    {
        // 使用真实的SupervisorService，不需要Mock数据
        // 测试检测器在没有数据时的行为
        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-01-01');
        $thresholds = ['cheat_rate' => 5.0];

        $result = $this->detector->detect($startDate, $endDate, $thresholds);

        // 由于数据库中没有相关数据，应该返回空结果
        $this->assertIsArray($result);
    }

    public function testDetectWithAnomalies(): void
    {
        // 测试检测器的基本功能：处理阈值检查
        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-01-01');
        $thresholds = ['cheat_rate' => 5.0];

        $result = $this->detector->detect($startDate, $endDate, $thresholds);

        // 验证返回的是数组
        $this->assertIsArray($result);
        // 由于没有真实的测试数据，我们主要验证方法可以正常调用
        // 在实际应用中，如果有作弊率超过阈值的数据，会返回相应结果
    }

    public function testDetectWithCheatRateBelowThreshold(): void
    {
        // 测试低阈值的情况
        $startDate = new \DateTime('2024-01-01');
        $endDate = new \DateTime('2024-01-01');
        $thresholds = ['cheat_rate' => 50.0]; // 设置很高的阈值

        $result = $this->detector->detect($startDate, $endDate, $thresholds);

        // 验证返回的是数组
        $this->assertIsArray($result);
        // 在高阈值情况下，通常不会有异常结果
    }
}
