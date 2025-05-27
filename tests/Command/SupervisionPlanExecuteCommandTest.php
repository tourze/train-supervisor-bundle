<?php

declare(strict_types=1);

namespace Aqacms\TrainSupervisorBundle\Tests\Command;

use Aqacms\TrainSupervisorBundle\Command\SupervisionPlanExecuteCommand;
use Aqacms\TrainSupervisorBundle\Entity\SupervisionPlan;
use Aqacms\TrainSupervisorBundle\Service\SupervisionPlanService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * 监督计划执行命令测试
 */
class SupervisionPlanExecuteCommandTest extends TestCase
{
    private MockObject $supervisionPlanService;
    private SupervisionPlanExecuteCommand $command;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->supervisionPlanService = $this->createMock(SupervisionPlanService::class);
        $this->command = new SupervisionPlanExecuteCommand($this->supervisionPlanService);

        $application = new Application();
        $application->add($this->command);
        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecuteWithActivePlans(): void
    {
        // 准备模拟数据
        $plan1 = new SupervisionPlan();
        $plan1->setTitle('计划1');
        $plan1->setStatus('active');

        $plan2 = new SupervisionPlan();
        $plan2->setTitle('计划2');
        $plan2->setStatus('active');

        $activePlans = [$plan1, $plan2];

        // 模拟服务行为
        $this->supervisionPlanService
            ->expects($this->once())
            ->method('getActivePlans')
            ->willReturn($activePlans);

        $this->supervisionPlanService
            ->expects($this->exactly(2))
            ->method('executePlan')
            ->withConsecutive([$plan1], [$plan2])
            ->willReturn(true);

        // 执行命令
        $exitCode = $this->commandTester->execute([]);

        // 验证结果
        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('开始执行监督计划', $output);
        $this->assertStringContainsString('找到 2 个活跃的监督计划', $output);
        $this->assertStringContainsString('执行计划: 计划1', $output);
        $this->assertStringContainsString('执行计划: 计划2', $output);
        $this->assertStringContainsString('监督计划执行完成', $output);
    }

    public function testExecuteWithNoActivePlans(): void
    {
        // 模拟服务行为 - 没有活跃计划
        $this->supervisionPlanService
            ->expects($this->once())
            ->method('getActivePlans')
            ->willReturn([]);

        // 执行命令
        $exitCode = $this->commandTester->execute([]);

        // 验证结果
        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('没有找到活跃的监督计划', $output);
    }

    public function testExecuteWithSpecificPlanId(): void
    {
        // 准备模拟数据
        $plan = new SupervisionPlan();
        $plan->setTitle('指定计划');
        $plan->setStatus('active');

        // 模拟服务行为
        $this->supervisionPlanService
            ->expects($this->once())
            ->method('findPlan')
            ->with(123)
            ->willReturn($plan);

        $this->supervisionPlanService
            ->expects($this->once())
            ->method('executePlan')
            ->with($plan)
            ->willReturn(true);

        // 执行命令
        $exitCode = $this->commandTester->execute(['--plan-id' => 123]);

        // 验证结果
        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('执行指定的监督计划', $output);
        $this->assertStringContainsString('执行计划: 指定计划', $output);
    }

    public function testExecuteWithNonExistentPlanId(): void
    {
        // 模拟服务行为 - 计划不存在
        $this->supervisionPlanService
            ->expects($this->once())
            ->method('findPlan')
            ->with(999)
            ->willReturn(null);

        // 执行命令
        $exitCode = $this->commandTester->execute(['--plan-id' => 999]);

        // 验证结果
        $this->assertEquals(1, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('监督计划不存在', $output);
    }

    public function testExecuteWithDryRun(): void
    {
        // 准备模拟数据
        $plan = new SupervisionPlan();
        $plan->setTitle('测试计划');
        $plan->setStatus('active');

        // 模拟服务行为
        $this->supervisionPlanService
            ->expects($this->once())
            ->method('getActivePlans')
            ->willReturn([$plan]);

        // 在dry-run模式下，不应该调用executePlan
        $this->supervisionPlanService
            ->expects($this->never())
            ->method('executePlan');

        // 执行命令
        $exitCode = $this->commandTester->execute(['--dry-run' => true]);

        // 验证结果
        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('DRY RUN 模式', $output);
        $this->assertStringContainsString('将执行计划: 测试计划', $output);
    }

    public function testExecuteWithVerboseOutput(): void
    {
        // 准备模拟数据
        $plan = new SupervisionPlan();
        $plan->setTitle('详细输出测试');
        $plan->setStatus('active');

        // 模拟服务行为
        $this->supervisionPlanService
            ->expects($this->once())
            ->method('getActivePlans')
            ->willReturn([$plan]);

        $this->supervisionPlanService
            ->expects($this->once())
            ->method('executePlan')
            ->with($plan)
            ->willReturn(true);

        // 执行命令（详细模式）
        $exitCode = $this->commandTester->execute([], ['verbosity' => 2]);

        // 验证结果
        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('详细输出测试', $output);
    }

    public function testExecuteWithExecutionFailure(): void
    {
        // 准备模拟数据
        $plan = new SupervisionPlan();
        $plan->setTitle('失败计划');
        $plan->setStatus('active');

        // 模拟服务行为 - 执行失败
        $this->supervisionPlanService
            ->expects($this->once())
            ->method('getActivePlans')
            ->willReturn([$plan]);

        $this->supervisionPlanService
            ->expects($this->once())
            ->method('executePlan')
            ->with($plan)
            ->willReturn(false);

        // 执行命令
        $exitCode = $this->commandTester->execute([]);

        // 验证结果
        $this->assertEquals(0, $exitCode); // 命令本身成功，但会记录失败
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('执行失败: 失败计划', $output);
    }

    public function testExecuteWithException(): void
    {
        // 模拟服务抛出异常
        $this->supervisionPlanService
            ->expects($this->once())
            ->method('getActivePlans')
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
        $this->assertEquals('train:supervision:execute-plans', $this->command->getName());
        $this->assertStringContainsString('执行监督计划', $this->command->getDescription());
        
        // 测试选项配置
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('plan-id'));
        $this->assertTrue($definition->hasOption('dry-run'));
        
        $planIdOption = $definition->getOption('plan-id');
        $this->assertEquals('指定要执行的监督计划ID', $planIdOption->getDescription());
        
        $dryRunOption = $definition->getOption('dry-run');
        $this->assertEquals('仅显示将要执行的操作，不实际执行', $dryRunOption->getDescription());
    }

    public function testExecuteWithForceOption(): void
    {
        // 准备模拟数据
        $plan = new SupervisionPlan();
        $plan->setTitle('强制执行计划');
        $plan->setStatus('completed'); // 已完成状态

        // 模拟服务行为
        $this->supervisionPlanService
            ->expects($this->once())
            ->method('findPlan')
            ->with(123)
            ->willReturn($plan);

        $this->supervisionPlanService
            ->expects($this->once())
            ->method('executePlan')
            ->with($plan, true) // 强制执行
            ->willReturn(true);

        // 执行命令
        $exitCode = $this->commandTester->execute([
            '--plan-id' => 123,
            '--force' => true
        ]);

        // 验证结果
        $this->assertEquals(0, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('强制执行模式', $output);
    }
} 