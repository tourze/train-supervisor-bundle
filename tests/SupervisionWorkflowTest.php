<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;
use Tourze\TrainSupervisorBundle\Exception\InvalidPlanStatusException;
use Tourze\TrainSupervisorBundle\Exception\SupervisionPlanNotFoundException;
use Tourze\TrainSupervisorBundle\Service\SupervisionPlanService;

/**
 * 监督工作流程集成测试.
 *
 * @internal
 */
#[CoversClass(SupervisionPlanService::class)]
#[RunTestsInSeparateProcesses]
final class SupervisionWorkflowTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // No setup needed for this test class
    }

    /**
     * 测试取消监督计划.
     */
    public function testCancelPlan(): void
    {
        // 创建一个真实的测试实体
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        $plan->setPlanStatus('待执行');
        $plan->setRemarks('原有备注');

        $em = self::getEntityManager();
        $em->persist($plan);
        $em->flush();

        $service = self::getService(SupervisionPlanService::class);
        $planId = $plan->getId();
        $this->assertNotNull($planId, 'Plan ID should not be null after persisting');
        $result = $service->cancelPlan($planId, '测试取消原因');

        $this->assertEquals('已取消', $result->getPlanStatus());
        $this->assertStringContainsString('取消原因: 测试取消原因', $result->getRemarks() ?? '');
    }

    /**
     * 测试完成监督计划.
     */
    public function testCompletePlan(): void
    {
        // 创建一个真实的测试实体
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试监督计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        $plan->setPlanStatus('执行中');

        $em = self::getEntityManager();
        $em->persist($plan);
        $em->flush();

        $service = self::getService(SupervisionPlanService::class);
        $planId = $plan->getId();
        $this->assertNotNull($planId, 'Plan ID should not be null after persisting');
        $result = $service->completePlan($planId);

        $this->assertEquals('已完成', $result->getPlanStatus());
    }

    /**
     * 测试创建监督计划.
     */
    public function testCreateSupervisionPlan(): void
    {
        $service = self::getService(SupervisionPlanService::class);

        $planData = [
            'planName' => '2024年安全培训监督计划',
            'planType' => '定期',
            'planStartDate' => new \DateTimeImmutable('2024-01-01'),
            'planEndDate' => new \DateTimeImmutable('2024-12-31'),
            'supervisionScope' => ['华北地区', '华东地区'],
            'supervisionItems' => ['课程质量', '师资水平'],
            'supervisor' => '张三',
            'planStatus' => '待执行',
            'remarks' => '年度定期监督计划',
        ];

        $result = $service->createSupervisionPlan($planData);

        // 验证结果并从数据库重新获取确保持久化
        $em = self::getEntityManager();
        $persistedPlan = $em->find(SupervisionPlan::class, $result->getId());

        $this->assertInstanceOf(SupervisionPlan::class, $persistedPlan);
        $this->assertEquals($planData['planName'], $persistedPlan->getPlanName());
        $this->assertEquals($planData['planType'], $persistedPlan->getPlanType());
        $this->assertEquals($planData['supervisionScope'], $persistedPlan->getSupervisionScope());
        $this->assertEquals($planData['supervisionItems'], $persistedPlan->getSupervisionItems());
        $this->assertEquals($planData['supervisor'], $persistedPlan->getSupervisor());
    }

    /**
     * 测试执行监督计划.
     */
    public function testExecuteSupervisionPlan(): void
    {
        // 创建一个真实的测试实体
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        $plan->setPlanStatus('待执行');
        $plan->setSupervisionScope(['华北地区']);
        $plan->setSupervisionItems(['课程质量']);

        $em = self::getEntityManager();
        $em->persist($plan);
        $em->flush();

        $service = self::getService(SupervisionPlanService::class);
        $planId = $plan->getId();
        $this->assertNotNull($planId, 'Plan ID should not be null after persisting');

        $result = $service->executeSupervisionPlan($planId);

        // 重新从数据库获取验证状态变更
        $em->refresh($plan);

        $this->assertEquals('执行中', $plan->getPlanStatus());
        $this->assertArrayHasKey('planId', $result);
        $this->assertEquals($plan->getId(), $result['planId']);
        $this->assertEquals('测试计划', $result['planName']);
        $this->assertEquals('执行中', $result['status']);
        $this->assertEquals(['华北地区'], $result['supervisionScope']);
        $this->assertEquals(['课程质量'], $result['supervisionItems']);
        $this->assertEquals('测试监督员', $result['supervisor']);
        $this->assertInstanceOf(\DateTime::class, $result['executedAt']);
    }

    /**
     * 测试生成计划报告.
     */
    public function testGeneratePlanReport(): void
    {
        // 创建一个真实的测试实体
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        $plan->setPlanStatus('执行中');
        $plan->setSupervisionScope(['华北地区']);
        $plan->setSupervisionItems(['课程质量']);
        $plan->setRemarks('测试备注');

        $em = self::getEntityManager();
        $em->persist($plan);
        $em->flush();

        $service = self::getService(SupervisionPlanService::class);
        $planId = $plan->getId();
        $this->assertNotNull($planId, 'Plan ID should not be null after persisting');
        $result = $service->generatePlanReport($planId);

        $this->assertArrayHasKey('planInfo', $result);
        $this->assertArrayHasKey('supervisionScope', $result);
        $this->assertArrayHasKey('supervisionItems', $result);
        $this->assertArrayHasKey('remarks', $result);
        $this->assertArrayHasKey('generatedAt', $result);

        $this->assertIsArray($result['planInfo']);
        $planInfo = $result['planInfo'];
        $this->assertArrayHasKey('name', $planInfo);
        $this->assertArrayHasKey('type', $planInfo);
        $this->assertArrayHasKey('startDate', $planInfo);
        $this->assertArrayHasKey('endDate', $planInfo);
        $this->assertArrayHasKey('status', $planInfo);
        $this->assertArrayHasKey('supervisor', $planInfo);

        $this->assertEquals('测试计划', $planInfo['name']);
        $this->assertEquals('定期', $planInfo['type']);
        $this->assertEquals('2024-01-01', $planInfo['startDate']);
        $this->assertEquals('2024-12-31', $planInfo['endDate']);
        $this->assertEquals('执行中', $planInfo['status']);
        $this->assertEquals('测试监督员', $planInfo['supervisor']);

        $this->assertEquals(['华北地区'], $result['supervisionScope']);
        $this->assertEquals(['课程质量'], $result['supervisionItems']);
        $this->assertEquals('测试备注', $result['remarks']);
        $this->assertInstanceOf(\DateTime::class, $result['generatedAt']);
    }

    /**
     * 测试检查计划是否应该在指定日期执行.
     */
    public function testShouldExecuteOnDate(): void
    {
        $service = self::getService(SupervisionPlanService::class);

        $plan = new SupervisionPlan();
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setPlanStatus('执行中');

        // 测试在有效日期范围内的情况
        $validDate = new \DateTime('2024-06-15');
        $this->assertTrue($service->shouldExecuteOnDate($plan, $validDate));

        // 测试在开始日期之前的情况
        $earlyDate = new \DateTime('2023-12-31');
        $this->assertFalse($service->shouldExecuteOnDate($plan, $earlyDate));

        // 测试在结束日期之后的情况
        $lateDate = new \DateTime('2025-01-01');
        $this->assertFalse($service->shouldExecuteOnDate($plan, $lateDate));

        // 测试非执行中状态的情况
        $plan->setPlanStatus('待执行');
        $this->assertFalse($service->shouldExecuteOnDate($plan, $validDate));
    }

    /**
     * 测试更新监督计划.
     */
    public function testUpdateSupervisionPlan(): void
    {
        // 创建一个真实的测试实体
        $plan = new SupervisionPlan();
        $plan->setPlanName('原计划名称');
        $plan->setPlanType('原类型');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('原监督员');
        $plan->setPlanStatus('待执行');

        $em = self::getEntityManager();
        $em->persist($plan);
        $em->flush();

        $service = self::getService(SupervisionPlanService::class);

        $updateData = [
            'planName' => '更新后的计划名称',
            'planType' => '更新后的类型',
            'supervisor' => '更新后的监督员',
            'planStatus' => '执行中',
            'remarks' => '更新后的备注',
        ];

        $planId = $plan->getId();
        $this->assertNotNull($planId, 'Plan ID should not be null after persisting');
        $result = $service->updateSupervisionPlan($planId, $updateData);

        $this->assertEquals('更新后的计划名称', $result->getPlanName());
        $this->assertEquals('更新后的类型', $result->getPlanType());
        $this->assertEquals('更新后的监督员', $result->getSupervisor());
        $this->assertEquals('执行中', $result->getPlanStatus());
        $this->assertEquals('更新后的备注', $result->getRemarks());
    }

    /**
     * 测试更新计划执行状态
     */
    public function testUpdatePlanExecution(): void
    {
        $service = self::getService(SupervisionPlanService::class);

        $plan = new SupervisionPlan();
        $plan->setPlanName('测试计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        $plan->setPlanStatus('执行中');

        $em = self::getEntityManager();
        $em->persist($plan);
        $em->flush();

        $executionDate = new \DateTime('2024-06-15');

        // 此方法目前只是调用 flush，没有其他业务逻辑
        $service->updatePlanExecution($plan, $executionDate);

        // 验证数据库状态
        $em->refresh($plan);
        $this->assertEquals('执行中', $plan->getPlanStatus());
    }

    /**
     * 测试监督计划不存在的异常情况.
     */
    public function testExecuteSupervisionPlanWithNotFound(): void
    {
        $service = self::getService(SupervisionPlanService::class);

        $planId = 'non-existent-plan';

        $this->expectException(SupervisionPlanNotFoundException::class);
        $this->expectExceptionMessage('监督计划不存在: non-existent-plan');

        $service->executeSupervisionPlan($planId);
    }

    /**
     * 测试执行非活跃状态计划的异常情况.
     */
    public function testExecuteSupervisionPlanWithInvalidStatus(): void
    {
        // 创建一个真实的测试实体
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        $plan->setPlanStatus('已完成'); // 非活跃状态

        $em = self::getEntityManager();
        $em->persist($plan);
        $em->flush();

        $service = self::getService(SupervisionPlanService::class);

        $this->expectException(InvalidPlanStatusException::class);
        $this->expectExceptionMessage('监督计划状态不允许执行: 已完成');

        $planId = $plan->getId();
        $this->assertNotNull($planId, 'Plan ID should not be null after persisting');
        $service->executeSupervisionPlan($planId);
    }

    /**
     * 测试激活监督计划.
     */
    public function testActivateSupervisionPlan(): void
    {
        // 创建一个真实的测试实体
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试激活计划');
        $plan->setPlanType('定期');
        $plan->setPlanStartDate(new \DateTimeImmutable('2024-01-01'));
        $plan->setPlanEndDate(new \DateTimeImmutable('2024-12-31'));
        $plan->setSupervisor('测试监督员');
        $plan->setPlanStatus('待执行');

        $em = self::getEntityManager();
        $em->persist($plan);
        $em->flush();

        $service = self::getService(SupervisionPlanService::class);
        $planId = $plan->getId();
        $this->assertNotNull($planId, 'Plan ID should not be null after persisting');

        $result = $service->activateSupervisionPlan($planId);

        // 验证返回结果
        $this->assertInstanceOf(SupervisionPlan::class, $result);
        $this->assertEquals('激活', $result->getPlanStatus());
        $this->assertEquals('测试激活计划', $result->getPlanName());

        // 重新从数据库获取验证状态变更
        $em->refresh($plan);
        $this->assertEquals('激活', $plan->getPlanStatus());
    }

    /**
     * 测试激活不存在的监督计划.
     */
    public function testActivateSupervisionPlanWithNotFound(): void
    {
        $service = self::getService(SupervisionPlanService::class);

        $planId = 'non-existent-plan';

        $this->expectException(SupervisionPlanNotFoundException::class);
        $this->expectExceptionMessage('监督计划不存在: non-existent-plan');

        $service->activateSupervisionPlan($planId);
    }
}
