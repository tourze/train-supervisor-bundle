<?php

namespace Tourze\TrainSupervisorBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainSupervisorBundle\Service\ReportService;

/**
 * 监督报告生成命令
 * 用于生成各类监督报告（日报、周报、月报、专项报告）
 */
#[AsCommand(
    name: 'train:supervision:report',
    description: '生成监督报告'
)]
class SupervisionReportCommand extends Command
{
    
    public const NAME = 'train:supervision:report';
public function __construct(
        private readonly ReportService $reportService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('type', InputArgument::REQUIRED, '报告类型 (daily|weekly|monthly|special)')
            ->addOption('date', null, InputOption::VALUE_OPTIONAL, '指定日期 (Y-m-d)', date('Y-m-d'))
            ->addOption('start-date', null, InputOption::VALUE_OPTIONAL, '开始日期 (Y-m-d)')
            ->addOption('end-date', null, InputOption::VALUE_OPTIONAL, '结束日期 (Y-m-d)')
            ->addOption('title', null, InputOption::VALUE_OPTIONAL, '专项报告标题')
            ->addOption('reporter', null, InputOption::VALUE_OPTIONAL, '报告人', 'system')
            ->addOption('auto-publish', null, InputOption::VALUE_NONE, '自动发布报告')
            ->addOption('export', null, InputOption::VALUE_OPTIONAL, '导出报告到文件')
            ->setHelp('此命令用于生成各类监督报告。支持日报、周报、月报和专项报告。');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $type = $input->getArgument('type');
        $dateStr = $input->getOption('date');
        $startDateStr = $input->getOption('start-date');
        $endDateStr = $input->getOption('end-date');
        $title = $input->getOption('title');
        $reporter = $input->getOption('reporter');
        $autoPublish = $input->getOption('auto-publish');
        $exportFile = $input->getOption('export');

        // 验证报告类型
        if (!in_array($type, ['daily', 'weekly', 'monthly', 'special'])) {
            $io->error('无效的报告类型。支持的类型: daily, weekly, monthly, special');
            return Command::FAILURE;
        }

        $io->title(sprintf('生成%s报告', $this->getReportTypeName($type)));

        try {
            $report = null;

            switch ($type) {
                case 'daily':
                    $report = $this->generateDailyReport($dateStr, $reporter, $io);
                    break;
                case 'weekly':
                    $report = $this->generateWeeklyReport($dateStr, $reporter, $io);
                    break;
                case 'monthly':
                    $report = $this->generateMonthlyReport($dateStr, $reporter, $io);
                    break;
                case 'special':
                    $report = $this->generateSpecialReport($startDateStr, $endDateStr, $title, $reporter, $io);
                    break;
            }

            if (!$report) {
                $io->error('报告生成失败');
                return Command::FAILURE;
            }

            $io->success(sprintf('报告生成成功！报告ID: %s', $report->getId()));
            $this->displayReportSummary($report, $io);

            // 自动发布
            if ((bool) $autoPublish) {
                $this->reportService->publishReport($report);
                $io->success('报告已自动发布！');
            }

            // 导出报告
            if ((bool) $exportFile) {
                $this->exportReport($report, $exportFile, $io);
            }

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $io->error(sprintf('生成报告时发生错误: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    /**
     * 生成日报
     */
    private function generateDailyReport(string $dateStr, string $reporter, SymfonyStyle $io)
    {
        try {
            $date = new \DateTime($dateStr);
        } catch (\Throwable $e) {
            $io->error(sprintf('无效的日期格式: %s', $dateStr));
            return null;
        }

        $io->text(sprintf('生成日期: %s', $date->format('Y-m-d')));
        
        return $this->reportService->generateDailyReport($date, $reporter);
    }

    /**
     * 生成周报
     */
    private function generateWeeklyReport(string $dateStr, string $reporter, SymfonyStyle $io)
    {
        try {
            $date = new \DateTime($dateStr);
            // 获取周一作为周开始日期
            $weekStart = (clone $date)->modify('monday this week');
        } catch (\Throwable $e) {
            $io->error(sprintf('无效的日期格式: %s', $dateStr));
            return null;
        }

        $weekEnd = (clone $weekStart)->modify('+6 days');
        $io->text(sprintf('周报期间: %s 至 %s', 
            $weekStart->format('Y-m-d'), 
            $weekEnd->format('Y-m-d')
        ));
        
        return $this->reportService->generateWeeklyReport($weekStart, $reporter);
    }

    /**
     * 生成月报
     */
    private function generateMonthlyReport(string $dateStr, string $reporter, SymfonyStyle $io)
    {
        try {
            $date = new \DateTime($dateStr);
            $year = (int)$date->format('Y');
            $month = (int)$date->format('m');
        } catch (\Throwable $e) {
            $io->error(sprintf('无效的日期格式: %s', $dateStr));
            return null;
        }

        $io->text(sprintf('月报期间: %d年%d月', $year, $month));
        
        return $this->reportService->generateMonthlyReport($year, $month, $reporter);
    }

    /**
     * 生成专项报告
     */
    private function generateSpecialReport(?string $startDateStr, ?string $endDateStr, ?string $title, string $reporter, SymfonyStyle $io)
    {
        if (!$startDateStr || !$endDateStr) {
            $io->error('专项报告需要指定开始日期和结束日期');
            return null;
        }

        if (!$title) {
            $io->error('专项报告需要指定标题');
            return null;
        }

        try {
            $startDate = new \DateTime($startDateStr);
            $endDate = new \DateTime($endDateStr);
        } catch (\Throwable $e) {
            $io->error('无效的日期格式');
            return null;
        }

        if ($startDate > $endDate) {
            $io->error('开始日期不能晚于结束日期');
            return null;
        }

        $io->text(sprintf('专项报告期间: %s 至 %s', 
            $startDate->format('Y-m-d'), 
            $endDate->format('Y-m-d')
        ));
        $io->text(sprintf('报告标题: %s', $title));
        
        return $this->reportService->generateSpecialReport($title, $startDate, $endDate, $reporter);
    }

    /**
     * 显示报告摘要
     */
    private function displayReportSummary($report, SymfonyStyle $io): void
    {
        $io->section('报告摘要');

        $supervisionData = $report->getSupervisionData();
        $problemSummary = $report->getProblemSummary();
        $statisticsData = $report->getStatisticsData();

        $io->definitionList(
            ['报告标题' => $report->getReportTitle()],
            ['报告类型' => $report->getReportType()],
            ['报告期间' => sprintf('%s 至 %s', 
                $report->getReportPeriodStart()->format('Y-m-d'),
                $report->getReportPeriodEnd()->format('Y-m-d')
            )],
            ['报告人' => $report->getReporter()],
            ['报告状态' => $report->getReportStatus()],
            ['检查次数' => $supervisionData['inspection_count'] ?? 0],
            ['评估次数' => $supervisionData['assessment_count'] ?? 0],
            ['发现问题' => $problemSummary['total_problems'] ?? 0],
            ['平均分数' => sprintf('%.2f', $statisticsData['inspection_stats']['average_score'] ?? 0)],
            ['通过率' => sprintf('%.2f%%', $statisticsData['inspection_stats']['pass_rate'] ?? 0)]
        );

        // 显示问题分布
        if (!empty($problemSummary['by_severity'])) {
            $io->section('问题严重程度分布');
            $tableData = [];
            foreach ($problemSummary['by_severity'] as $severity => $count) {
                $tableData[] = [$severity, $count];
            }
            $io->table(['严重程度', '数量'], $tableData);
        }

        // 显示建议措施
        $recommendations = $report->getRecommendations();
        if (!empty($recommendations)) {
            $io->section('建议措施');
            foreach ($recommendations as $index => $recommendation) {
                $io->text(sprintf('%d. %s', $index + 1, $recommendation));
            }
        }
    }

    /**
     * 导出报告
     */
    private function exportReport($report, string $exportFile, SymfonyStyle $io): void
    {
        $io->section('导出报告');

        $extension = pathinfo($exportFile, PATHINFO_EXTENSION);
        
        try {
            $reportData = [
                'id' => $report->getId(),
                'title' => $report->getReportTitle(),
                'type' => $report->getReportType(),
                'period_start' => $report->getReportPeriodStart()->format('Y-m-d'),
                'period_end' => $report->getReportPeriodEnd()->format('Y-m-d'),
                'reporter' => $report->getReporter(),
                'report_date' => $report->getReportDate()->format('Y-m-d'),
                'status' => $report->getReportStatus(),
                'supervision_data' => $report->getSupervisionData(),
                'problem_summary' => $report->getProblemSummary(),
                'statistics_data' => $report->getStatisticsData(),
                'recommendations' => $report->getRecommendations(),
                'content' => $report->getReportContent(),
            ];

            switch (strtolower($extension)) {
                case 'json':
                    $this->exportToJson($reportData, $exportFile);
                    break;
                case 'txt':
                case 'md':
                    $this->exportToText($report, $exportFile);
                    break;
                default:
                    throw new \InvalidArgumentException('不支持的文件格式，请使用 .json, .txt 或 .md');
            }

            $io->success(sprintf('报告已导出到: %s', $exportFile));

        } catch (\Throwable $e) {
            $io->error(sprintf('导出失败: %s', $e->getMessage()));
        }
    }

    /**
     * 导出为JSON格式
     */
    private function exportToJson(array $data, string $filename): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($filename, $json);
    }

    /**
     * 导出为文本格式
     */
    private function exportToText($report, string $filename): void
    {
        $content = $report->getReportContent();
        if (!$content) {
            $content = sprintf("# %s\n\n报告期间：%s 至 %s\n报告人：%s\n\n暂无详细内容。",
                $report->getReportTitle(),
                $report->getReportPeriodStart()->format('Y-m-d'),
                $report->getReportPeriodEnd()->format('Y-m-d'),
                $report->getReporter()
            );
        }
        
        file_put_contents($filename, $content);
    }

    /**
     * 获取报告类型名称
     */
    private function getReportTypeName(string $type): string
    {
        return match($type) {
            'daily' => '日报',
            'weekly' => '周报',
            'monthly' => '月报',
            'special' => '专项报告',
            default => $type,
        };
    }
} 