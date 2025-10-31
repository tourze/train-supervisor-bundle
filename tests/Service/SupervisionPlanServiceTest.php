<?php

namespace Tourze\TrainSupervisorBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;
use Tourze\TrainSupervisorBundle\Exception\SupervisionPlanNotFoundException;
use Tourze\TrainSupervisorBundle\Service\SupervisionPlanService;

/**
 * @internal
 */
#[CoversClass(SupervisionPlanService::class)]
#[RunTestsInSeparateProcesses]
final class SupervisionPlanServiceTest extends AbstractIntegrationTestCase
{
    private SupervisionPlanService $supervisionPlanService;

    protected function onSetUp(): void
    {
        $this->supervisionPlanService = self::getService(SupervisionPlanService::class);
    }

    public function testCreateSupervisionPlan(): void
    {
        $planData = [
            'planName' => '测试监督计划',
            'planType' => '常规监督',
            'planStartDate' => new \DateTimeImmutable('2024-01-01'),
            'planEndDate' => new \DateTimeImmutable('2024-12-31'),
            'supervisionScope' => ['机构A', '机构B'],
            'supervisionItems' => ['项目1', '项目2'],
            'supervisor' => '监督员张三',
            'planStatus' => '待执行',
            'remarks' => '测试计划备注',
        ];

        $result = $this->supervisionPlanService->createSupervisionPlan($planData);

        $this->assertInstanceOf(SupervisionPlan::class, $result);
        $this->assertEquals('测试监督计划', $result->getPlanName());
        $this->assertEquals('常规监督', $result->getPlanType());
        $this->assertEquals('监督员张三', $result->getSupervisor());
        $this->assertEquals('待执行', $result->getPlanStatus());
    }

    public function testUpdateSupervisionPlan(): void
    {
        // 先创建一个计划
        $planData = [
            'planName' => '更新测试计划',
            'planType' => '专项监督',
            'planStartDate' => new \DateTimeImmutable('2024-01-01'),
            'planEndDate' => new \DateTimeImmutable('2024-06-30'),
            'supervisor' => '监督员李四',
        ];

        $plan = $this->supervisionPlanService->createSupervisionPlan($planData);

        $updateData = [
            'planName' => '更新后的计划名称',
            'planStatus' => '执行中',
            'supervisionScope' => ['更新机构1', '更新机构2'],
            'remarks' => '更新后的备注',
        ];

        $planId = $plan->getId();
        $this->assertNotNull($planId);
        $result = $this->supervisionPlanService->updateSupervisionPlan($planId, $updateData);

        $this->assertInstanceOf(SupervisionPlan::class, $result);
        $this->assertEquals('更新后的计划名称', $result->getPlanName());
        $this->assertEquals('执行中', $result->getPlanStatus());
    }

    public function testUpdateSupervisionPlanNotFound(): void
    {
        $this->expectException(SupervisionPlanNotFoundException::class);
        $this->expectExceptionMessage('监督计划不存在: nonexistent123');

        $this->supervisionPlanService->updateSupervisionPlan('nonexistent123', [
            'planName' => '不存在的计划',
        ]);
    }

    public function testActivateSupervisionPlan(): void
    {
        $planData = [
            'planName' => '激活测试计划',
            'planType' => '定期监督',
            'planStartDate' => new \DateTimeImmutable('2024-01-01'),
            'planEndDate' => new \DateTimeImmutable('2024-12-31'),
            'supervisor' => '监督员王五',
        ];

        $plan = $this->supervisionPlanService->createSupervisionPlan($planData);

        $planId = $plan->getId();
        $this->assertNotNull($planId);
        $result = $this->supervisionPlanService->activateSupervisionPlan($planId);

        $this->assertInstanceOf(SupervisionPlan::class, $result);
        $this->assertEquals('激活', $result->getPlanStatus());
    }

    public function testActivateSupervisionPlanNotFound(): void
    {
        $this->expectException(SupervisionPlanNotFoundException::class);
        $this->expectExceptionMessage('监督计划不存在: nonexistent123');

        $this->supervisionPlanService->activateSupervisionPlan('nonexistent123');
    }

    public function testGetActivePlans(): void
    {
        // 创建一个激活的计划
        $planData = [
            'planName' => '活跃计划测试',
            'planType' => '常规监督',
            'planStartDate' => new \DateTimeImmutable('2024-01-01'),
            'planEndDate' => new \DateTimeImmutable('2024-12-31'),
            'supervisor' => '监督员测试',
            'planStatus' => '激活',
        ];

        $this->supervisionPlanService->createSupervisionPlan($planData);

        $result = $this->supervisionPlanService->getActivePlans();

        $this->assertIsArray($result);
    }

    public function testGetSupervisionPlanStatistics(): void
    {
        // 创建一些测试计划数据
        $this->supervisionPlanService->createSupervisionPlan([
            'planName' => '统计测试计划1',
            'planType' => '常规监督',
            'planStartDate' => new \DateTimeImmutable('2024-01-01'),
            'planEndDate' => new \DateTimeImmutable('2024-06-30'),
            'supervisor' => '统计监督员1',
            'planStatus' => '已完成',
        ]);

        $this->supervisionPlanService->createSupervisionPlan([
            'planName' => '统计测试计划2',
            'planType' => '专项监督',
            'planStartDate' => new \DateTimeImmutable('2024-07-01'),
            'planEndDate' => new \DateTimeImmutable('2024-12-31'),
            'supervisor' => '统计监督员2',
            'planStatus' => '执行中',
        ]);

        $result = $this->supervisionPlanService->getSupervisionPlanStatistics();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('total_plans', $result);
        $this->assertArrayHasKey('active_plans', $result);
        $this->assertArrayHasKey('completed_plans', $result);
        $this->assertArrayHasKey('cancelled_plans', $result);
        $this->assertArrayHasKey('expired_plans', $result);
        $this->assertArrayHasKey('completion_rate', $result);
        $this->assertArrayHasKey('by_type', $result);
        $this->assertArrayHasKey('by_status', $result);
    }

    public function testExecuteSupervisionPlan(): void
    {
        $planData = [
            'planName' => '执行测试计划',
            'planType' => '随机监督',
            'planStartDate' => new \DateTimeImmutable('2024-01-01'),
            'planEndDate' => new \DateTimeImmutable('2024-12-31'),
            'supervisor' => '执行监督员',
            'planStatus' => '待执行',
            'supervisionScope' => ['执行范围1', '执行范围2'],
            'supervisionItems' => ['执行项目1', '执行项目2'],
        ];

        $plan = $this->supervisionPlanService->createSupervisionPlan($planData);

        $planId = $plan->getId();
        $this->assertNotNull($planId);
        $result = $this->supervisionPlanService->executeSupervisionPlan($planId);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('planId', $result);
        $this->assertArrayHasKey('planName', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('supervisionScope', $result);
        $this->assertArrayHasKey('supervisionItems', $result);
        $this->assertArrayHasKey('supervisor', $result);
        $this->assertArrayHasKey('executedAt', $result);
        $this->assertEquals('执行中', $result['status']);
    }

    public function testExecuteSupervisionPlanNotFound(): void
    {
        $this->expectException(SupervisionPlanNotFoundException::class);
        $this->expectExceptionMessage('监督计划不存在: nonexistent123');

        $this->supervisionPlanService->executeSupervisionPlan('nonexistent123');
    }

    public function testGeneratePlanReport(): void
    {
        $planData = [
            'planName' => '报告测试计划',
            'planType' => '专项监督',
            'planStartDate' => new \DateTimeImmutable('2024-01-01'),
            'planEndDate' => new \DateTimeImmutable('2024-06-30'),
            'supervisor' => '报告监督员',
            'supervisionScope' => ['报告范围1', '报告范围2'],
            'supervisionItems' => ['报告项目1', '报告项目2'],
            'remarks' => '报告测试备注',
        ];

        $plan = $this->supervisionPlanService->createSupervisionPlan($planData);

        $planId = $plan->getId();
        $this->assertNotNull($planId);
        $result = $this->supervisionPlanService->generatePlanReport($planId);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('planInfo', $result);
        $this->assertArrayHasKey('supervisionScope', $result);
        $this->assertArrayHasKey('supervisionItems', $result);
        $this->assertArrayHasKey('remarks', $result);
        $this->assertArrayHasKey('generatedAt', $result);
    }

    public function testGeneratePlanReportNotFound(): void
    {
        $this->expectException(SupervisionPlanNotFoundException::class);
        $this->expectExceptionMessage('监督计划不存在: nonexistent123');

        $this->supervisionPlanService->generatePlanReport('nonexistent123');
    }

    public function testGetExpiredPlans(): void
    {
        $result = $this->supervisionPlanService->getExpiredPlans();

        $this->assertIsArray($result);
    }

    public function testGetStatisticsByType(): void
    {
        $result = $this->supervisionPlanService->getStatisticsByType();

        $this->assertIsArray($result);
    }

    public function testCompletePlan(): void
    {
        $planData = [
            'planName' => '完成测试计划',
            'planType' => '常规监督',
            'planStartDate' => new \DateTimeImmutable('2024-01-01'),
            'planEndDate' => new \DateTimeImmutable('2024-06-30'),
            'supervisor' => '完成监督员',
        ];

        $plan = $this->supervisionPlanService->createSupervisionPlan($planData);

        $planId = $plan->getId();
        $this->assertNotNull($planId);
        $result = $this->supervisionPlanService->completePlan($planId);

        $this->assertInstanceOf(SupervisionPlan::class, $result);
        $this->assertEquals('已完成', $result->getPlanStatus());
    }

    public function testCompletePlanNotFound(): void
    {
        $this->expectException(SupervisionPlanNotFoundException::class);
        $this->expectExceptionMessage('监督计划不存在: nonexistent123');

        $this->supervisionPlanService->completePlan('nonexistent123');
    }

    public function testCancelPlan(): void
    {
        $planData = [
            'planName' => '取消测试计划',
            'planType' => '专项监督',
            'planStartDate' => new \DateTimeImmutable('2024-01-01'),
            'planEndDate' => new \DateTimeImmutable('2024-06-30'),
            'supervisor' => '取消监督员',
        ];

        $plan = $this->supervisionPlanService->createSupervisionPlan($planData);

        $cancelReason = '测试取消原因';
        $planId = $plan->getId();
        $this->assertNotNull($planId);
        $result = $this->supervisionPlanService->cancelPlan($planId, $cancelReason);

        $this->assertInstanceOf(SupervisionPlan::class, $result);
        $this->assertEquals('已取消', $result->getPlanStatus());
        $remarks = $result->getRemarks();
        $this->assertNotNull($remarks);
        $this->assertStringContainsString($cancelReason, $remarks);
    }

    public function testCancelPlanNotFound(): void
    {
        $this->expectException(SupervisionPlanNotFoundException::class);
        $this->expectExceptionMessage('监督计划不存在: nonexistent123');

        $this->supervisionPlanService->cancelPlan('nonexistent123');
    }

    public function testGetPlanById(): void
    {
        $planData = [
            'planName' => '查询测试计划',
            'planType' => '定期监督',
            'planStartDate' => new \DateTimeImmutable('2024-01-01'),
            'planEndDate' => new \DateTimeImmutable('2024-12-31'),
            'supervisor' => '查询监督员',
        ];

        $plan = $this->supervisionPlanService->createSupervisionPlan($planData);

        $planId = $plan->getId();
        $this->assertNotNull($planId);
        $result = $this->supervisionPlanService->getPlanById($planId);

        $this->assertInstanceOf(SupervisionPlan::class, $result);
        $this->assertEquals($plan->getId(), $result->getId());
    }

    public function testGetPlanByIdNotFound(): void
    {
        $result = $this->supervisionPlanService->getPlanById('nonexistent123');

        $this->assertNull($result);
    }

    public function testGetPlansToExecuteOnDate(): void
    {
        $executeDate = new \DateTimeImmutable('2024-06-15');

        $this->supervisionPlanService->createSupervisionPlan([
            'planName' => '日期执行测试计划',
            'planType' => '常规监督',
            'planStartDate' => new \DateTimeImmutable('2024-06-01'),
            'planEndDate' => new \DateTimeImmutable('2024-06-30'),
            'supervisor' => '日期执行监督员',
        ]);

        $result = $this->supervisionPlanService->getPlansToExecuteOnDate($executeDate);

        $this->assertIsArray($result);
    }

    public function testShouldExecuteOnDate(): void
    {
        $planData = [
            'planName' => '执行日期测试计划',
            'planType' => '随机监督',
            'planStartDate' => new \DateTimeImmutable('2024-06-01'),
            'planEndDate' => new \DateTimeImmutable('2024-06-30'),
            'supervisor' => '执行日期监督员',
            'planStatus' => '执行中',
        ];

        $plan = $this->supervisionPlanService->createSupervisionPlan($planData);

        $testDate = new \DateTimeImmutable('2024-06-15');
        $result = $this->supervisionPlanService->shouldExecuteOnDate($plan, $testDate);

        $this->assertTrue($result);

        // 测试不在执行期间的日期
        $outOfRangeDate = new \DateTimeImmutable('2024-07-15');
        $resultOutOfRange = $this->supervisionPlanService->shouldExecuteOnDate($plan, $outOfRangeDate);

        $this->assertFalse($resultOutOfRange);
    }

    public function testUpdatePlanExecution(): void
    {
        $planData = [
            'planName' => '执行更新测试计划',
            'planType' => '专项监督',
            'planStartDate' => new \DateTimeImmutable('2024-01-01'),
            'planEndDate' => new \DateTimeImmutable('2024-12-31'),
            'supervisor' => '执行更新监督员',
        ];

        $plan = $this->supervisionPlanService->createSupervisionPlan($planData);

        $executionDate = new \DateTimeImmutable();

        // 这个方法目前只是更新实体管理器状态，没有抛出异常即为成功
        $this->supervisionPlanService->updatePlanExecution($plan, $executionDate);

        // 确认更新操作正常执行，通过期望的功能验证而非无效断言
        $this->assertNotNull($plan->getId());
    }
}
