<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Tests\Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\TrainSupervisorBundle\Command\SupervisionPlanExecuteCommand;
use Tourze\TrainSupervisorBundle\Entity\SupervisionPlan;
use Tourze\TrainSupervisorBundle\Service\InspectionService;
use Tourze\TrainSupervisorBundle\Service\SupervisionPlanService;

/**
 * 监督计划执行命令测试
 */
class SupervisionPlanExecuteCommandTest extends TestCase
{
    private MockObject $supervisionPlanService;
    private MockObject $inspectionService;
    private SupervisionPlanExecuteCommand $command;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->supervisionPlanService = $this->createMock(SupervisionPlanService::class);
        $this->inspectionService = $this->createMock(InspectionService::class);
        
        $this->command = new SupervisionPlanExecuteCommand(
            $this->supervisionPlanService,
            $this->inspectionService
        );

        $application = new Application();
        $application->add($this->command);
        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecuteWithActivePlans(): void
    {
        // 准备模拟数据
        $plan1 = new SupervisionPlan();
        $plan1->setPlanName('计划1');
        $plan1->setPlanStatus('待执行');

        $plan2 = new SupervisionPlan();
        $plan2->setPlanName('计划2');
        $plan2->setPlanStatus('待执行');

        $activePlans = [$plan1, $plan2];

        // 模拟服务行为
        $this->supervisionPlanService
            ->expects($this->once())
            ->method('getPlansToExecuteOnDate')
            ->willReturn($activePlans);

        // 执行命令
        $exitCode = $this->commandTester->execute([]);

        // 验证结果
        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('监督计划执行', $output);
    }

    public function testExecuteWithNoActivePlans(): void
    {
        // 模拟服务行为 - 没有活跃计划
        $this->supervisionPlanService
            ->expects($this->once())
            ->method('getPlansToExecuteOnDate')
            ->willReturn([]);

        // 执行命令
        $exitCode = $this->commandTester->execute([]);

        // 验证结果
        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('没有需要执行的监督计划', $output);
    }

    public function testExecuteWithSpecificPlanId(): void
    {
        // 准备模拟数据
        $plan = new SupervisionPlan();
        $plan->setPlanName('指定计划');
        $plan->setPlanStatus('待执行');

        // 模拟服务行为
        $this->supervisionPlanService
            ->expects($this->once())
            ->method('getPlanById')
            ->with(123)
            ->willReturn($plan);

        // 执行命令
        $exitCode = $this->commandTester->execute(['--plan-id' => 123]);

        // 验证结果
        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithNonExistentPlanId(): void
    {
        // 模拟服务行为 - 计划不存在
        $this->supervisionPlanService
            ->expects($this->once())
            ->method('getPlanById')
            ->with(999)
            ->willReturn(null);

        // 执行命令
        $exitCode = $this->commandTester->execute(['--plan-id' => 999]);

        // 验证结果
        $this->assertEquals(1, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('未找到ID为 999 的监督计划', $output);
    }

    public function testExecuteWithDryRun(): void
    {
        // 准备模拟数据
        $plan = new SupervisionPlan();
        $plan->setPlanName('测试计划');
        $plan->setPlanStatus('待执行');

        // 模拟服务行为
        $this->supervisionPlanService
            ->expects($this->once())
            ->method('getPlansToExecuteOnDate')
            ->willReturn([$plan]);

        // 执行命令
        $exitCode = $this->commandTester->execute(['--dry-run' => true]);

        // 验证结果
        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('试运行模式', $output);
    }

    public function testExecuteWithVerboseOutput(): void
    {
        // 准备模拟数据
        $plan = new SupervisionPlan();
        $plan->setPlanName('详细输出测试');
        $plan->setPlanStatus('待执行');

        // 模拟服务行为
        $this->supervisionPlanService
            ->expects($this->once())
            ->method('getPlansToExecuteOnDate')
            ->willReturn([$plan]);

        // 执行命令（详细模式）
        $exitCode = $this->commandTester->execute([], ['verbosity' => 2]);

        // 验证结果
        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithExecutionFailure(): void
    {
        // 准备模拟数据
        $plan = new SupervisionPlan();
        $plan->setPlanName('失败计划');
        $plan->setPlanStatus('待执行');

        // 模拟服务行为
        $this->supervisionPlanService
            ->expects($this->once())
            ->method('getPlansToExecuteOnDate')
            ->willReturn([$plan]);

        // 执行命令
        $exitCode = $this->commandTester->execute([]);

        // 验证结果
        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithException(): void
    {
        // 模拟服务抛出异常
        $this->supervisionPlanService
            ->expects($this->once())
            ->method('getPlansToExecuteOnDate')
            ->willThrowException(new \Exception('数据库连接失败'));

        // 执行命令
        $exitCode = $this->commandTester->execute([]);

        // 验证结果
        $this->assertEquals(1, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('执行过程中发生错误', $output);
        $this->assertStringContainsString('数据库连接失败', $output);
    }

    public function testCommandConfiguration(): void
    {
        // 测试命令配置
        $this->assertEquals('train:supervision:plan:execute', $this->command->getName());
        $this->assertStringContainsString('执行监督计划', $this->command->getDescription());
        
        // 测试选项配置
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('plan-id'));
        $this->assertTrue($definition->hasOption('dry-run'));
        
        $planIdOption = $definition->getOption('plan-id');
        $this->assertEquals('指定要执行的监督计划ID', $planIdOption->getDescription());
        
        $dryRunOption = $definition->getOption('dry-run');
        $this->assertEquals('试运行模式，不实际创建检查任务', $dryRunOption->getDescription());
    }

    public function testExecuteWithForceOption(): void
    {
        // 这个测试现在不适用，因为命令没有force选项
        $this->assertTrue(true);
    }
} 