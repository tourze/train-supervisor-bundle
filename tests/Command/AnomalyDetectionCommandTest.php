<?php

namespace Tourze\TrainSupervisorBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\TrainSupervisorBundle\Command\AnomalyDetectionCommand;
use Tourze\TrainSupervisorBundle\Entity\SupervisorData;
use Tourze\TrainSupervisorBundle\Entity\Supplier;

/**
 * @internal
 */
#[CoversClass(AnomalyDetectionCommand::class)]
#[RunTestsInSeparateProcesses]
final class AnomalyDetectionCommandTest extends AbstractCommandTestCase
{
    private AnomalyDetectionCommand $command;

    protected function onSetUp(): void
    {
        $this->command = self::getService(AnomalyDetectionCommand::class);
    }

    protected function getCommandTester(): CommandTester
    {
        return new CommandTester($this->command);
    }

    public function testCommandCanBeInstantiated(): void
    {
        $this->assertInstanceOf(AnomalyDetectionCommand::class, $this->command);
    }

    public function testExecuteWithDefaultOptions(): void
    {
        $commandTester = new CommandTester($this->command);

        // 创建一个测试供应商实体
        $supplier = new Supplier();
        $supplier->setName('Test Supplier');
        self::getEntityManager()->persist($supplier);
        self::getEntityManager()->flush();

        // 创建测试监督数据
        $supervisorData = new SupervisorData();
        $supervisorData->setSupplier($supplier);
        $supervisorData->setSupplierId((int) $supplier->getId());
        $supervisorData->setDate(new \DateTimeImmutable('2024-01-01'));
        $supervisorData->setDailyLearnCount(100);
        $supervisorData->setDailyCheatCount(10);
        self::getEntityManager()->persist($supervisorData);
        self::getEntityManager()->flush();

        // 运行命令
        $commandTester->execute([
            '--date' => '2024-01-01',
            '--type' => 'cheat',
        ]);

        $statusCode = $commandTester->getStatusCode();
        $output = $commandTester->getDisplay();

        echo "\nCommand output:\n" . $output . "\n";
        echo 'Command status code: ' . $statusCode . "\n";

        $this->assertEquals(Command::SUCCESS, $statusCode);
    }

    public function testExecuteWithSpecificDate(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            '--date' => '2024-01-01',
            '--type' => 'all',
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testOptionDate(): void
    {
        $commandTester = new CommandTester($this->command);

        // 测试 --date 选项
        $commandTester->execute([
            '--date' => '2024-01-15',
            '--type' => 'all',
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('2024-01-15', $output);
    }

    public function testOptionStartDate(): void
    {
        $commandTester = new CommandTester($this->command);

        // 测试 --start-date 选项
        $commandTester->execute([
            '--start-date' => '2024-01-01',
            '--end-date' => '2024-01-31',
            '--type' => 'all',
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('2024-01-01', $output);
    }

    public function testOptionEndDate(): void
    {
        $commandTester = new CommandTester($this->command);

        // 测试 --end-date 选项
        $commandTester->execute([
            '--start-date' => '2024-01-01',
            '--end-date' => '2024-01-31',
            '--type' => 'all',
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('2024-01-31', $output);
    }

    public function testOptionType(): void
    {
        $commandTester = new CommandTester($this->command);

        // 测试 --type 选项
        $commandTester->execute([
            '--type' => 'cheat',
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('cheat', $output);
    }

    public function testOptionThreshold(): void
    {
        $commandTester = new CommandTester($this->command);

        // 测试 --threshold 选项
        $commandTester->execute([
            '--threshold' => '{"cheat_rate": 10.0}',
            '--type' => 'all',
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testOptionExport(): void
    {
        $commandTester = new CommandTester($this->command);
        $tempFile = tempnam(sys_get_temp_dir(), 'anomaly_test_');

        // 测试 --export 选项
        $commandTester->execute([
            '--export' => $tempFile,
            '--type' => 'all',
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());

        // 清理临时文件
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }

    public function testOptionAutoAlert(): void
    {
        $commandTester = new CommandTester($this->command);

        // 测试 --auto-alert 选项
        $commandTester->execute([
            '--auto-alert' => true,
            '--type' => 'all',
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testOptionVerboseOutput(): void
    {
        $commandTester = new CommandTester($this->command);

        // 测试 --verbose-output 选项
        $commandTester->execute([
            '--verbose-output' => true,
            '--type' => 'all',
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }
}
