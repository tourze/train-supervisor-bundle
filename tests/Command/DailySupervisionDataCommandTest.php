<?php

namespace Tourze\TrainSupervisorBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\TrainSupervisorBundle\Command\DailySupervisionDataCommand;

/**
 * @internal
 */
#[CoversClass(DailySupervisionDataCommand::class)]
#[RunTestsInSeparateProcesses]
final class DailySupervisionDataCommandTest extends AbstractCommandTestCase
{
    private DailySupervisionDataCommand $command;

    protected function onSetUp(): void
    {
        $this->command = self::getService(DailySupervisionDataCommand::class);
    }

    protected function getCommandTester(): CommandTester
    {
        return new CommandTester($this->command);
    }

    public function testExecuteWithDefaultOptions(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testExecuteWithSpecificDate(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            '--date' => '2024-01-01',
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testOptionDate(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            '--date' => '2024-01-15',
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('2024-01-15', $output);
    }

    public function testOptionGenerateReport(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            '--generate-report' => true,
            '--auto-publish' => true,
        ]);

        if (Command::SUCCESS !== $commandTester->getStatusCode()) {
            echo "\nCommand output:\n" . $commandTester->getDisplay();
        }

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testOptionCheckAnomaly(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            '--check-anomaly' => true,
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testOptionExport(): void
    {
        $commandTester = new CommandTester($this->command);
        $tempFile = tempnam(sys_get_temp_dir(), 'daily_data_test_');

        $commandTester->execute([
            '--export' => $tempFile,
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());

        // 清理临时文件
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }

    public function testOptionAutoPublish(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            '--auto-publish' => true,
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }
}
