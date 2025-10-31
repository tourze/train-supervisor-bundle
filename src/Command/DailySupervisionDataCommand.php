<?php

namespace Tourze\TrainSupervisorBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\TrainSupervisorBundle\Exception\UnsupportedFormatException;
use Tourze\TrainSupervisorBundle\Service\ReportService;
use Tourze\TrainSupervisorBundle\Service\SupervisorService;

/**
 * 日常监督数据处理命令
 * 用于收集、处理和分析日常监督数据.
 */
#[AsCommand(name: self::NAME, description: '收集和处理日常监督数据', help: <<<'TXT'
    此命令用于收集和处理日常监督数据，可以生成报告和检查异常。
    TXT)]
#[Autoconfigure(public: true)]
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
            ->addOption('auto-publish', null, InputOption::VALUE_NONE, '自动发布报告，跳过确认')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dateStr = is_string($input->getOption('date')) ? $input->getOption('date') : date('Y-m-d');
        $generateReport = (bool) $input->getOption('generate-report');
        $checkAnomaly = (bool) $input->getOption('check-anomaly');
        $exportFileOption = $input->getOption('export');
        $exportFile = is_string($exportFileOption) ? $exportFileOption : null;
        $autoPublish = (bool) $input->getOption('auto-publish');

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
            if ($checkAnomaly) {
                $this->checkAnomalyData($date, $io);
            }

            // 生成日报
            if ($generateReport) {
                $this->generateDailyReport($date, $io, $autoPublish);
            }

            // 导出数据
            if (null !== $exportFile) {
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
            ['总记录数' => $statistics['total_records'] ?? 0],
            ['涉及机构数' => $statistics['total_suppliers'] ?? 0],
            ['总开班数' => $statistics['total_classrooms']],
            ['新开班数' => $statistics['total_new_classrooms']],
            ['总登录人数' => $statistics['total_logins']],
            ['总学习人数' => $statistics['total_learners']],
            ['总作弊次数' => $statistics['total_cheats']],
            ['作弊率' => sprintf('%.2f%%', $this->safeFloatCast($statistics['cheat_rate'] ?? 0))],
            ['人脸识别成功率' => sprintf('%.2f%%', $this->safeFloatCast($statistics['face_detect_success_rate'] ?? 0))]
        );

        // 显示按机构统计
        $bySupplier = $statistics['by_supplier'] ?? [];
        if (is_array($bySupplier) && [] !== $bySupplier) {
            $io->section('按机构统计');
            $tableData = [];
            foreach ($bySupplier as $supplierData) {
                if (!is_array($supplierData)) {
                    continue;
                }
                $tableData[] = [
                    $supplierData['supplier_name'] ?? 'Unknown',
                    $supplierData['total_classrooms'] ?? 0,
                    $supplierData['total_logins'] ?? 0,
                    $supplierData['total_learners'] ?? 0,
                    $supplierData['total_cheats'] ?? 0,
                    $this->calculateCheatRate($supplierData['total_cheats'] ?? 0, $supplierData['total_learners'] ?? 0),
                ];
            }

            $io->table(
                ['机构名称', '开班数', '登录人数', '学习人数', '作弊次数', '作弊率'],
                $tableData
            );
        }
    }

    /**
     * 检查异常数据.
     */
    private function checkAnomalyData(\DateTime $date, SymfonyStyle $io): void
    {
        $io->section('检查异常数据');

        $startDate = (clone $date)->setTime(0, 0, 0);
        $endDate = (clone $date)->setTime(23, 59, 59);

        $anomalies = $this->supervisorService->getAnomalySupervisorData($startDate, $endDate);

        if ([] === $anomalies) {
            $io->success('未发现异常数据');

            return;
        }

        $io->warning(sprintf('发现 %d 条异常数据', count($anomalies)));

        $tableData = [];
        foreach ($anomalies as $anomaly) {
            $tableData[] = [
                $anomaly['supplier_name'],
                $anomaly['date'],
                implode('; ', $anomaly['anomaly_reasons']),
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
            '4. 记录异常处理过程',
        ]);
    }

    /**
     * 生成日报.
     */
    private function generateDailyReport(\DateTime $date, SymfonyStyle $io, bool $autoPublish = false): void
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

        $inspectionStats = is_array($statisticsData['inspection_stats'] ?? null) ? $statisticsData['inspection_stats'] : [];

        $io->definitionList(
            ['检查次数' => $supervisionData['inspection_count'] ?? 0],
            ['评估次数' => $supervisionData['assessment_count'] ?? 0],
            ['发现问题' => $problemSummary['total_problems'] ?? 0],
            ['平均分数' => sprintf('%.2f', $this->safeFloatCast($inspectionStats['average_score'] ?? 0))],
            ['通过率' => sprintf('%.2f%%', $this->safeFloatCast($inspectionStats['pass_rate'] ?? 0))]
        );

        // 询问是否发布报告
        if ($autoPublish || $io->confirm('是否立即发布此报告？', false)) {
            $this->reportService->publishReport($report);
            $io->success('报告已发布！');
        }
    }

    /**
     * 导出数据.
     */
    private function exportData(\DateTime $date, string $exportFile, SymfonyStyle $io): void
    {
        $io->section('导出数据');

        $startDate = (clone $date)->setTime(0, 0, 0);
        $endDate = (clone $date)->setTime(23, 59, 59);

        // 导出监督数据
        $data = $this->supervisorService->exportSupervisorData($startDate, $endDate);

        if ([] === $data) {
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
                    throw new UnsupportedFormatException('不支持的文件格式，请使用 .json 或 .csv');
            }

            $io->success(sprintf('数据已导出到: %s', $exportFile));
            $io->text(sprintf('导出记录数: %d', count($data)));
        } catch (\Throwable $e) {
            $io->error(sprintf('导出失败: %s', $e->getMessage()));
        }
    }

    /**
     * 导出为JSON格式.
     *
     * @param array<int, array<string, mixed>> $data
     */
    private function exportToJson(array $data, string $filename): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($filename, $json);
    }

    /**
     * 导出为CSV格式.
     *
     * @param array<int, array<string, mixed>> $data
     */
    private function exportToCsv(array $data, string $filename): void
    {
        $handle = fopen($filename, 'w');
        if (false === $handle) {
            throw new UnsupportedFormatException(sprintf('无法创建文件: %s', $filename));
        }

        $this->writeCsvContent($handle, $data);
        fclose($handle);
    }

    /**
     * 写入CSV内容.
     *
     * @param resource $handle
     * @param array<int, array<string, mixed>> $data
     */
    private function writeCsvContent($handle, array $data): void
    {
        // 写入BOM以支持中文
        fwrite($handle, "\xEF\xBB\xBF");

        if ([] === $data || !isset($data[0])) {
            return;
        }

        $this->writeCsvHeader($handle, $data[0]);
        $this->writeCsvRows($handle, $data);
    }

    /**
     * 写入CSV表头.
     *
     * @param resource $handle
     * @param array<string, mixed> $firstRow
     */
    private function writeCsvHeader($handle, array $firstRow): void
    {
        fputcsv($handle, array_keys($firstRow));
    }

    /**
     * 写入CSV数据行.
     *
     * @param resource $handle
     * @param array<int, array<string, mixed>> $data
     */
    private function writeCsvRows($handle, array $data): void
    {
        foreach ($data as $row) {
            $csvRow = $this->prepareCsvRow($row);
            fputcsv($handle, $csvRow);
        }
    }

    /**
     * 准备CSV行数据.
     *
     * @param array<string, mixed> $row
     * @return array<int, string|null>
     */
    private function prepareCsvRow(array $row): array
    {
        $csvRow = [];
        foreach ($row as $value) {
            if (is_scalar($value) || null === $value) {
                $csvRow[] = is_scalar($value) ? (string) $value : null;
            } else {
                $csvRow[] = '';
            }
        }

        return $csvRow;
    }

    /**
     * 安全地将mixed类型转换为float
     */
    private function safeFloatCast(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        return 0.0;
    }

    /**
     * 计算作弊率
     */
    private function calculateCheatRate(mixed $cheats, mixed $learners): string
    {
        $cheatCount = $this->safeFloatCast($cheats);
        $learnerCount = $this->safeFloatCast($learners);

        if ($learnerCount > 0) {
            $rate = ($cheatCount / $learnerCount) * 100;

            return sprintf('%.2f%%', $rate);
        }

        return '0.00%';
    }
}
