<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Tests\Helper;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainSupervisorBundle\Helper\StatisticsDisplayHelper;

/**
 * 统计数据显示助手测试类.
 * @internal
 *  */
#[CoversClass(StatisticsDisplayHelper::class)]
class StatisticsDisplayHelperTest extends TestCase
{
    private StatisticsDisplayHelper $helper;

    protected function setUp(): void
    {
        $this->helper = new StatisticsDisplayHelper();
    }

    public function testDisplayAssessmentStatisticsOutputsSections(): void
    {
        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        $io = new SymfonyStyle($input, $output);

        $statistics = [
            'total_assessments' => 100,
            'average_score' => 86.5,
            'max_score' => 98.3,
            'min_score' => 60.0,
            'excellent_rate' => 0.34,
            'good_rate' => 0.46,
            'pass_rate' => 0.90,
        ];

        $this->helper->displayAssessmentStatistics($statistics, $io);

        $text = $output->fetch();
        $this->assertIsString($text);
        $this->assertStringContainsString('评估统计概览', $text);
        $this->assertStringContainsString('总评估数', $text);
    }
}
