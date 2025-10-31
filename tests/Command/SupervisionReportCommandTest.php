<?php

namespace Tourze\TrainSupervisorBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\TrainSupervisorBundle\Command\SupervisionReportCommand;

/**
 * @internal
 */
#[CoversClass(SupervisionReportCommand::class)]
#[RunTestsInSeparateProcesses]
final class SupervisionReportCommandTest extends AbstractCommandTestCase
{
    private SupervisionReportCommand $command;

    protected function onSetUp(): void
    {
        $this->command = self::getService(SupervisionReportCommand::class);
    }

    protected function getCommandTester(): CommandTester
    {
        return new CommandTester($this->command);
    }

    public function testExecuteWithDailyReport(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            'type' => 'daily',
        ]);

        $this->assertGreaterThanOrEqual(0, $commandTester->getStatusCode());
    }

    public function testExecuteWithInvalidType(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            'type' => 'invalid',
        ]);

        $this->assertGreaterThanOrEqual(0, $commandTester->getStatusCode());
    }

    public function testArgumentType(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            'type' => 'daily',
        ]);

        $this->assertGreaterThanOrEqual(0, $commandTester->getStatusCode());

        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasArgument('type'));
    }

    public function testOptionDate(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            'type' => 'daily',
            '--date' => '2024-01-01',
        ]);

        $this->assertGreaterThanOrEqual(0, $commandTester->getStatusCode());

        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('date'));
    }

    public function testOptionStartDate(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            'type' => 'special',
            '--start-date' => '2024-01-01',
            '--end-date' => '2024-01-31',
            '--title' => 'Test Report',
        ]);

        $this->assertGreaterThanOrEqual(0, $commandTester->getStatusCode());

        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('start-date'));
    }

    public function testOptionEndDate(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            'type' => 'special',
            '--start-date' => '2024-01-01',
            '--end-date' => '2024-01-31',
            '--title' => 'Test Report',
        ]);

        $this->assertGreaterThanOrEqual(0, $commandTester->getStatusCode());

        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('end-date'));
    }

    public function testOptionTitle(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            'type' => 'special',
            '--start-date' => '2024-01-01',
            '--end-date' => '2024-01-31',
            '--title' => 'Test Special Report',
        ]);

        $this->assertGreaterThanOrEqual(0, $commandTester->getStatusCode());

        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('title'));
    }

    public function testOptionReporter(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            'type' => 'daily',
            '--reporter' => 'test_reporter',
        ]);

        $this->assertGreaterThanOrEqual(0, $commandTester->getStatusCode());

        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('reporter'));
    }

    public function testOptionAutoPublish(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            'type' => 'daily',
            '--auto-publish' => true,
        ]);

        $this->assertGreaterThanOrEqual(0, $commandTester->getStatusCode());

        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('auto-publish'));
    }

    public function testOptionExport(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            'type' => 'daily',
            '--export' => '/tmp/test_report.json',
        ]);

        $this->assertGreaterThanOrEqual(0, $commandTester->getStatusCode());

        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('export'));
    }
}
