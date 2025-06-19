<?php

namespace Tourze\TrainSupervisorBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainSupervisorBundle\Service\ReportService;
use Tourze\TrainSupervisorBundle\Service\SupervisorService;

/**
 * 日常监督数据处理命令
 * 用于收集、处理和分析日常监督数据
 */
#[AsCommand(
    name: self::NAME,
    description: '收集和处理日常监督数据'
)]
class DailySupervisionDataCommand extends Command
{
    public const NAME = 'train:supervision:daily-data';
public function __construct(
        private readonly SupervisorService $supervisorService,
        private readonly ReportService $reportService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('date', null, InputOption::VALUE_OPTIONAL, '指定处理日期 (Y-m-d)', date('Y-m-d'))
            ->addOption('generate-report', null, InputOption::VALUE_NONE, '生成日报')
            ->addOption('check-anomaly', null, InputOption::VALUE_NONE, '检查异常数据')
            ->addOption('export', null, InputOption::VALUE_OPTIONAL, '导出数据到文件')
            ->setHelp('此命令用于收集和处理日常监督数据，可以生成报告和检查异常。');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $dateStr = $input->getOption('date');
        $generateReport = $input->getOption('generate-report');
        $checkAnomaly = $input->getOption('check-anomaly');
        $exportFile = $input->getOption('export');

        try {
            $date = new \DateTime($dateStr);
        } catch (\Throwable $e) {
            $io->error(sprintf('无效的日期格式: %s', $dateStr));
            return Command::FAILURE;
        }

        $io->title('日常监督数据处理');
        $io->text(sprintf('处理日期: %s', $date->format('Y-m-d')));

        try {
            // 收集监督数据统计
            $this->collectSupervisionStatistics($date, $io);

            // 检查异常数据
            if ((bool) $checkAnomaly) {
                $this->checkAnomalyData($date, $io);
            }

            // 生成日报
            if ((bool) $generateReport) {
                $this->generateDailyReport($date, $io);
            }

            // 导出数据
            if ((bool) $exportFile) {
                $this->exportData($date, $exportFile, $io);
            }

            $io->success('日常监督数据处理完成！');
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $io->error(sprintf('处理过程中发生错误: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    /**
     * 收集监督数据统计
     */
    private function collectSupervisionStatistics(\DateTime $date, SymfonyStyle $io): void
    {
        $io->section('收集监督数据统计');

        $startDate = (clone $date)->setTime(0, 0, 0);
        $endDate = (clone $date)->setTime(23, 59, 59);

        // 获取当日监督统计
        $statistics = $this->supervisorService->generateSupervisorStatistics($startDate, $endDate);

        $io->definitionList(
            ['总记录数' => $statistics['total_records']],
            ['涉及机构数' => $statistics['total_suppliers']],
            ['总开班数' => $statistics['total_classrooms']],
            ['新开班数' => $statistics['total_new_classrooms']],
            ['总登录人数' => $statistics['total_logins']],
            ['总学习人数' => $statistics['total_learners']],
            ['总作弊次数' => $statistics['total_cheats']],
            ['作弊率' => sprintf('%.2f%%', $statistics['cheat_rate'])],
            ['人脸识别成功率' => sprintf('%.2f%%', $statistics['face_detect_success_rate'])]
        );

        // 显示按机构统计
        if (!empty($statistics['by_supplier'])) {
            $io->section('按机构统计');
            $tableData = [];
            foreach ($statistics['by_supplier'] as $supplierData) {
                $tableData[] = [
                    $supplierData['supplier_name'],
                    $supplierData['total_classrooms'],
                    $supplierData['total_logins'],
                    $supplierData['total_learners'],
                    $supplierData['total_cheats'],
                    sprintf('%.2f%%', $supplierData['total_learners'] > 0 ? 
                        ($supplierData['total_cheats'] / $supplierData['total_learners']) * 100 : 0)
                ];
            }
            
            $io->table(
                ['机构名称', '开班数', '登录人数', '学习人数', '作弊次数', '作弊率'],
                $tableData
            );
        }
    }

    /**
     * 检查异常数据
     */
    private function checkAnomalyData(\DateTime $date, SymfonyStyle $io): void
    {
        $io->section('检查异常数据');

        $startDate = (clone $date)->setTime(0, 0, 0);
        $endDate = (clone $date)->setTime(23, 59, 59);

        $anomalies = $this->supervisorService->getAnomalySupervisorData($startDate, $endDate);

        if ((bool) empty($anomalies)) {
            $io->success('未发现异常数据');
            return;
        }

        $io->warning(sprintf('发现 %d 条异常数据', count($anomalies)));

        $tableData = [];
        foreach ($anomalies as $anomaly) {
            $tableData[] = [
                $anomaly['supplier_name'],
                $anomaly['date'],
                implode('; ', $anomaly['anomaly_reasons'])
            ];
        }

        $io->table(
            ['机构名称', '日期', '异常原因'],
            $tableData
        );

        // 提供处理建议
        $io->note([
            '异常数据处理建议：',
            '1. 联系相关机构核实数据准确性',
            '2. 检查系统是否存在技术问题',
            '3. 必要时进行现场检查',
            '4. 记录异常处理过程'
        ]);
    }

    /**
     * 生成日报
     */
    private function generateDailyReport(\DateTime $date, SymfonyStyle $io): void
    {
        $io->section('生成日报');

        $reporter = 'system'; // 系统自动生成
        $report = $this->reportService->generateDailyReport($date, $reporter);

        $io->success(sprintf('日报生成成功！报告ID: %s', $report->getId()));
        $io->text(sprintf('报告标题: %s', $report->getReportTitle()));
        $io->text(sprintf('报告状态: %s', $report->getReportStatus()));

        // 显示报告摘要
        $supervisionData = $report->getSupervisionData();
        $problemSummary = $report->getProblemSummary();
        $statisticsData = $report->getStatisticsData();

        $io->definitionList(
            ['检查次数' => $supervisionData['inspection_count'] ?? 0],
            ['评估次数' => $supervisionData['assessment_count'] ?? 0],
            ['发现问题' => $problemSummary['total_problems'] ?? 0],
            ['平均分数' => sprintf('%.2f', $statisticsData['inspection_stats']['average_score'] ?? 0)],
            ['通过率' => sprintf('%.2f%%', $statisticsData['inspection_stats']['pass_rate'] ?? 0)]
        );

        // 询问是否发布报告
        if ($io->confirm('是否立即发布此报告？', false)) {
            $this->reportService->publishReport($report);
            $io->success('报告已发布！');
        }
    }

    /**
     * 导出数据
     */
    private function exportData(\DateTime $date, string $exportFile, SymfonyStyle $io): void
    {
        $io->section('导出数据');

        $startDate = (clone $date)->setTime(0, 0, 0);
        $endDate = (clone $date)->setTime(23, 59, 59);

        // 导出监督数据
        $data = $this->supervisorService->exportSupervisorData($startDate, $endDate);

        if ((bool) empty($data)) {
            $io->warning('没有数据可导出');
            return;
        }

        // 确定文件格式
        $extension = pathinfo($exportFile, PATHINFO_EXTENSION);
        
        try {
            switch (strtolower($extension)) {
                case 'json':
                    $this->exportToJson($data, $exportFile);
                    break;
                case 'csv':
                    $this->exportToCsv($data, $exportFile);
                    break;
                default:
                    throw new \InvalidArgumentException('不支持的文件格式，请使用 .json 或 .csv');
            }

            $io->success(sprintf('数据已导出到: %s', $exportFile));
            $io->text(sprintf('导出记录数: %d', count($data)));

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
     * 导出为CSV格式
     */
    private function exportToCsv(array $data, string $filename): void
    {
        $handle = fopen($filename, 'w');
        
        // 写入BOM以支持中文
        fwrite($handle, "\xEF\xBB\xBF");
        
        // 写入表头
        if (!empty($data)) {
            fputcsv($handle, array_keys($data[0]));
            
            // 写入数据
            foreach ($data as $row) {
                fputcsv($handle, $row);
            }
        }
        
        fclose($handle);
    }
} 