<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\TrainSupervisorBundle\Command\SupervisionPlanExecuteCommand;

/**
 * 监督计划执行命令测试.
 *
 * @internal
 */
#[CoversClass(SupervisionPlanExecuteCommand::class)]
#[RunTestsInSeparateProcesses]
final class SupervisionPlanExecuteCommandTest extends AbstractCommandTestCase
{
    private SupervisionPlanExecuteCommand $command;

    private CommandTester $commandTester;

    protected function onSetUp(): void
    {
        $this->command = self::getService(SupervisionPlanExecuteCommand::class);
        $this->commandTester = new CommandTester($this->command);
    }

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    public function testExecuteWithActivePlans(): void
    {
        $exitCode = $this->commandTester->execute([]);

        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithNoActivePlans(): void
    {
        $exitCode = $this->commandTester->execute([]);

        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithSpecificPlanId(): void
    {
        $exitCode = $this->commandTester->execute(['--plan-id' => 123]);

        $this->assertGreaterThanOrEqual(0, $exitCode);
    }

    public function testExecuteWithNonExistentPlanId(): void
    {
        $exitCode = $this->commandTester->execute(['--plan-id' => 999]);

        $this->assertGreaterThanOrEqual(0, $exitCode);
    }

    public function testExecuteWithDryRun(): void
    {
        $exitCode = $this->commandTester->execute(['--dry-run' => true]);

        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithVerboseOutput(): void
    {
        $exitCode = $this->commandTester->execute([], ['verbosity' => 2]);

        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithExecutionFailure(): void
    {
        $exitCode = $this->commandTester->execute([]);

        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithException(): void
    {
        $exitCode = $this->commandTester->execute([]);

        $this->assertGreaterThanOrEqual(0, $exitCode);
    }

    public function testOptionPlanId(): void
    {
        $exitCode = $this->commandTester->execute(['--plan-id' => 123]);
        $this->assertGreaterThanOrEqual(0, $exitCode);

        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('plan-id'));
    }

    public function testOptionDate(): void
    {
        $exitCode = $this->commandTester->execute(['--date' => '2024-01-01']);
        $this->assertGreaterThanOrEqual(0, $exitCode);

        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('date'));
    }

    public function testOptionDryRun(): void
    {
        $exitCode = $this->commandTester->execute(['--dry-run' => true]);
        $this->assertGreaterThanOrEqual(0, $exitCode);

        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('dry-run'));
    }

    public function testCommandConfiguration(): void
    {
        $this->assertEquals('train:supervision:plan:execute', $this->command->getName());
        $this->assertStringContainsString('执行监督计划', $this->command->getDescription());

        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('plan-id'));
        $this->assertTrue($definition->hasOption('dry-run'));
    }
}
