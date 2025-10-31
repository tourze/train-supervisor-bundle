<?php

namespace Tourze\TrainSupervisorBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\TrainSupervisorBundle\Command\QualityAssessmentCommand;

/**
 * @internal
 */
#[CoversClass(QualityAssessmentCommand::class)]
#[RunTestsInSeparateProcesses]
final class QualityAssessmentCommandTest extends AbstractCommandTestCase
{
    private QualityAssessmentCommand $command;

    protected function onSetUp(): void
    {
        $this->command = self::getService(QualityAssessmentCommand::class);
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

    public function testExecuteWithSpecificInstitution(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            '--institution-id' => '1',
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testArgumentAction(): void
    {
        $commandTester = new CommandTester($this->command);

        // 测试 action 参数
        $commandTester->execute([
            'action' => 'analyze',
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('分析', $output);
    }

    public function testOptionInstitutionId(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            '--institution-id' => '123',
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testOptionAssessmentType(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            '--assessment-type' => '机构评估',
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testOptionDate(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            '--date' => '2024-01-01',
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testOptionStartDate(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            '--start-date' => '2024-01-01',
            '--end-date' => '2024-01-31',
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testOptionEndDate(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            '--start-date' => '2024-01-01',
            '--end-date' => '2024-01-31',
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testOptionAssessor(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            '--assessor' => '测试评估员',
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testOptionExport(): void
    {
        $commandTester = new CommandTester($this->command);
        $tempFile = tempnam(sys_get_temp_dir(), 'assessment_test_');

        $commandTester->execute([
            '--export' => $tempFile,
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());

        // 清理临时文件
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }

    public function testOptionAutoScore(): void
    {
        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            '--auto-score' => true,
        ]);

        $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }
}
