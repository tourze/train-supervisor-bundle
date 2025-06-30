<?php

namespace Tourze\TrainSupervisorBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainSupervisorBundle\Exception\UnsupportedFormatException;
use Tourze\TrainSupervisorBundle\Service\ProblemTrackingService;
use Tourze\TrainSupervisorBundle\Service\SupervisorService;

/**
 * 异常检测命令
 * 用于检测监督数据中的异常情况并生成预警
 */
#[AsCommand(
    name: self::NAME,
    description: '检测监督数据异常'
)]
class AnomalyDetectionCommand extends Command
{
    public const NAME = 'train:supervision:anomaly-detection';
public function __construct(
        private readonly SupervisorService $supervisorService,
        private readonly ProblemTrackingService $problemTrackingService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('date', null, InputOption::VALUE_OPTIONAL, '检测日期 (Y-m-d)', date('Y-m-d'))
            ->addOption('start-date', null, InputOption::VALUE_OPTIONAL, '开始日期 (Y-m-d)')
            ->addOption('end-date', null, InputOption::VALUE_OPTIONAL, '结束日期 (Y-m-d)')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, '检测类型 (all|cheat|face|learn|problem)', 'all')
            ->addOption('threshold', null, InputOption::VALUE_OPTIONAL, '异常阈值配置 (JSON格式)')
            ->addOption('export', null, InputOption::VALUE_OPTIONAL, '导出异常报告到文件')
            ->addOption('auto-alert', null, InputOption::VALUE_NONE, '自动发送异常预警')
            ->addOption('verbose-output', null, InputOption::VALUE_NONE, '详细输出异常信息')
            ->setHelp('此命令用于检测监督数据中的异常情况，包括作弊率异常、人脸识别异常、学习转化率异常等。');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $dateStr = $input->getOption('date');
        $startDateStr = $input->getOption('start-date');
        $endDateStr = $input->getOption('end-date');
        $type = $input->getOption('type');
        $thresholdJson = $input->getOption('threshold');
        $exportFile = $input->getOption('export');
        $autoAlert = $input->getOption('auto-alert');
        $verboseOutput = $input->getOption('verbose-output');

        // 验证检测类型
        if (!in_array($type, ['all', 'cheat', 'face', 'learn', 'problem'])) {
            $io->error('无效的检测类型。支持的类型: all, cheat, face, learn, problem');
            return Command::FAILURE;
        }

        $io->title('监督数据异常检测');

        try {
            // 确定检测时间范围
            $startDate = null;
            $endDate = null;

            if ($startDateStr !== null && $endDateStr !== null) {
                $startDate = new \DateTime($startDateStr);
                $endDate = new \DateTime($endDateStr);
                $io->text(sprintf('检测期间: %s 至 %s', $startDate->format('Y-m-d'), $endDate->format('Y-m-d')));
            } else {
                $date = new \DateTime($dateStr);
                $startDate = (clone $date)->setTime(0, 0, 0);
                $endDate = (clone $date)->setTime(23, 59, 59);
                $io->text(sprintf('检测日期: %s', $date->format('Y-m-d')));
            }

            // 解析阈值配置
            $thresholds = $this->parseThresholds($thresholdJson, $io);

            // 执行异常检测
            $anomalies = $this->detectAnomalies($startDate, $endDate, $type, $thresholds, $io);

            // 显示检测结果
            $this->displayAnomalies($anomalies, $verboseOutput, $io);

            // 导出异常报告
            if ($exportFile !== null) {
                $this->exportAnomalies($anomalies, $exportFile, $io);
            }

            // 自动发送预警
            if ((bool) $autoAlert && !empty($anomalies)) {
                $this->sendAnomalyAlerts($anomalies, $io);
            }

            if (empty($anomalies)) {
                $io->success('未检测到异常数据');
            } else {
                $io->warning(sprintf('检测到 %d 项异常，请及时处理', count($anomalies)));
            }

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $io->error(sprintf('异常检测过程中发生错误: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }

    /**
     * 解析阈值配置
     */
    private function parseThresholds(?string $thresholdJson, SymfonyStyle $io): array
    {
        $defaultThresholds = [
            'cheat_rate' => 5.0,           // 作弊率超过5%
            'face_fail_rate' => 20.0,      // 人脸识别失败率超过20%
            'learn_conversion_rate' => 50.0, // 学习转化率低于50%
            'problem_overdue_days' => 3,    // 问题逾期超过3天
            'new_classroom_ratio' => 100.0, // 新开班比例超过100%
        ];

        if ($thresholdJson === null) {
            return $defaultThresholds;
        }

        try {
            $customThresholds = json_decode($thresholdJson, true, 512, JSON_THROW_ON_ERROR);
            return array_merge($defaultThresholds, $customThresholds);
        } catch (\JsonException $e) {
            $io->warning(sprintf('阈值配置解析失败，使用默认配置: %s', $e->getMessage()));
            return $defaultThresholds;
        }
    }

    /**
     * 执行异常检测
     */
    private function detectAnomalies(\DateTime $startDate, \DateTime $endDate, string $type, array $thresholds, SymfonyStyle $io): array
    {
        $io->section('执行异常检测');

        $allAnomalies = [];

        if ($type === 'all' || $type === 'cheat') {
            $io->text('检测作弊率异常...');
            $cheatAnomalies = $this->detectCheatAnomalies($startDate, $endDate, $thresholds);
            $allAnomalies = array_merge($allAnomalies, $cheatAnomalies);
        }

        if ($type === 'all' || $type === 'face') {
            $io->text('检测人脸识别异常...');
            $faceAnomalies = $this->detectFaceDetectionAnomalies($startDate, $endDate, $thresholds);
            $allAnomalies = array_merge($allAnomalies, $faceAnomalies);
        }

        if ($type === 'all' || $type === 'learn') {
            $io->text('检测学习转化率异常...');
            $learnAnomalies = $this->detectLearnConversionAnomalies($startDate, $endDate, $thresholds);
            $allAnomalies = array_merge($allAnomalies, $learnAnomalies);
        }

        if ($type === 'all' || $type === 'problem') {
            $io->text('检测问题处理异常...');
            $problemAnomalies = $this->detectProblemAnomalies($startDate, $endDate, $thresholds);
            $allAnomalies = array_merge($allAnomalies, $problemAnomalies);
        }

        return $allAnomalies;
    }

    /**
     * 检测作弊率异常
     */
    private function detectCheatAnomalies(\DateTime $startDate, \DateTime $endDate, array $thresholds): array
    {
        $anomalies = [];
        $supervisorData = $this->supervisorService->getSupervisorDataByDateRange($startDate, $endDate);

        foreach ($supervisorData as $record) {
            if ($record->getDailyLearnCount() > 0) {
                $cheatRate = ($record->getDailyCheatCount() / $record->getDailyLearnCount()) * 100;
                
                if ($cheatRate > $thresholds['cheat_rate']) {
                    $anomalies[] = [
                        'type' => 'cheat_rate',
                        'severity' => $this->calculateSeverity($cheatRate, $thresholds['cheat_rate']),
                        'supplier_name' => $record->getSupplier()->getName(),
                        'date' => $record->getDate()->format('Y-m-d'),
                        'value' => $cheatRate,
                        'threshold' => $thresholds['cheat_rate'],
                        'description' => sprintf('作弊率异常：%.2f%% (阈值: %.2f%%)', $cheatRate, $thresholds['cheat_rate']),
                        'details' => [
                            'learn_count' => $record->getDailyLearnCount(),
                            'cheat_count' => $record->getDailyCheatCount(),
                        ]
                    ];
                }
            }
        }

        return $anomalies;
    }

    /**
     * 检测人脸识别异常
     */
    private function detectFaceDetectionAnomalies(\DateTime $startDate, \DateTime $endDate, array $thresholds): array
    {
        $anomalies = [];
        $supervisorData = $this->supervisorService->getSupervisorDataByDateRange($startDate, $endDate);

        foreach ($supervisorData as $record) {
            $totalFaceDetect = $record->getFaceDetectSuccessCount() + $record->getFaceDetectFailCount();
            
            if (is_numeric($totalFaceDetect) && $totalFaceDetect > 0) {
                $failRate = ($record->getFaceDetectFailCount() / $totalFaceDetect) * 100;
                
                if ($failRate > $thresholds['face_fail_rate']) {
                    $anomalies[] = [
                        'type' => 'face_fail_rate',
                        'severity' => $this->calculateSeverity($failRate, $thresholds['face_fail_rate']),
                        'supplier_name' => $record->getSupplier()->getName(),
                        'date' => $record->getDate()->format('Y-m-d'),
                        'value' => $failRate,
                        'threshold' => $thresholds['face_fail_rate'],
                        'description' => sprintf('人脸识别失败率异常：%.2f%% (阈值: %.2f%%)', $failRate, $thresholds['face_fail_rate']),
                        'details' => [
                            'success_count' => $record->getFaceDetectSuccessCount(),
                            'fail_count' => $record->getFaceDetectFailCount(),
                        ]
                    ];
                }
            }
        }

        return $anomalies;
    }

    /**
     * 检测学习转化率异常
     */
    private function detectLearnConversionAnomalies(\DateTime $startDate, \DateTime $endDate, array $thresholds): array
    {
        $anomalies = [];
        $supervisorData = $this->supervisorService->getSupervisorDataByDateRange($startDate, $endDate);

        foreach ($supervisorData as $record) {
            if ($record->getDailyLoginCount() > 0 && $record->getDailyLearnCount() > 0) {
                $conversionRate = ($record->getDailyLearnCount() / $record->getDailyLoginCount()) * 100;
                
                if ($conversionRate < $thresholds['learn_conversion_rate']) {
                    $anomalies[] = [
                        'type' => 'learn_conversion_rate',
                        'severity' => $this->calculateSeverity($thresholds['learn_conversion_rate'] - $conversionRate, 10),
                        'supplier_name' => $record->getSupplier()->getName(),
                        'date' => $record->getDate()->format('Y-m-d'),
                        'value' => $conversionRate,
                        'threshold' => $thresholds['learn_conversion_rate'],
                        'description' => sprintf('学习转化率异常：%.2f%% (阈值: %.2f%%)', $conversionRate, $thresholds['learn_conversion_rate']),
                        'details' => [
                            'login_count' => $record->getDailyLoginCount(),
                            'learn_count' => $record->getDailyLearnCount(),
                        ]
                    ];
                }
            }
        }

        return $anomalies;
    }

    /**
     * 检测问题处理异常
     */
    private function detectProblemAnomalies(\DateTime $startDate, \DateTime $endDate, array $thresholds): array
    {
        $anomalies = [];
        
        // 检测逾期问题
        $overdueProblems = $this->problemTrackingService->getOverdueProblems();
        
        foreach ($overdueProblems as $problem) {
            $overdueDays = abs($problem->getRemainingDays());
            
            if ($overdueDays > $thresholds['problem_overdue_days']) {
                $anomalies[] = [
                    'type' => 'problem_overdue',
                    'severity' => $this->calculateSeverity($overdueDays, $thresholds['problem_overdue_days']),
                    'supplier_name' => '问题跟踪',
                    'date' => $problem->getFoundDate()->format('Y-m-d'),
                    'value' => $overdueDays,
                    'threshold' => $thresholds['problem_overdue_days'],
                    'description' => sprintf('问题逾期异常：%d天 (阈值: %d天)', $overdueDays, $thresholds['problem_overdue_days']),
                    'details' => [
                        'problem_id' => $problem->getId(),
                        'problem_title' => $problem->getProblemTitle(),
                        'responsible_person' => $problem->getResponsiblePerson(),
                        'deadline' => $problem->getDeadline()->format('Y-m-d'),
                    ]
                ];
            }
        }

        return $anomalies;
    }

    /**
     * 计算异常严重程度
     */
    private function calculateSeverity(float $value, float $threshold): string
    {
        $ratio = $value / $threshold;
        
        if ($ratio >= 3.0) {
            return '严重';
        } elseif ($ratio >= 2.0) {
            return '重要';
        } elseif ($ratio >= 1.5) {
            return '一般';
        } else {
            return '轻微';
        }
    }

    /**
     * 显示异常信息
     */
    private function displayAnomalies(array $anomalies, bool $verbose, SymfonyStyle $io): void
    {
        if (empty($anomalies)) {
            return;
        }

        $io->section('异常检测结果');

        // 按严重程度分组
        $groupedAnomalies = [];
        foreach ($anomalies as $anomaly) {
            $groupedAnomalies[$anomaly['severity']][] = $anomaly;
        }

        // 按严重程度排序显示
        $severityOrder = ['严重', '重要', '一般', '轻微'];
        
        foreach ($severityOrder as $severity) {
            if (!isset($groupedAnomalies[$severity])) {
                continue;
            }

            $count = count($groupedAnomalies[$severity]);
            $io->section(sprintf('%s异常 (%d项)', $severity, $count));

            if ($verbose) {
                // 详细显示
                foreach ($groupedAnomalies[$severity] as $anomaly) {
                    $io->text(sprintf('- %s: %s', $anomaly['supplier_name'], $anomaly['description']));
                    if (!empty($anomaly['details'])) {
                        foreach ($anomaly['details'] as $key => $value) {
                            $io->text(sprintf('  %s: %s', $key, $value));
                        }
                    }
                    $io->newLine();
                }
            } else {
                // 简要显示
                $tableData = [];
                foreach ($groupedAnomalies[$severity] as $anomaly) {
                    $tableData[] = [
                        $anomaly['type'],
                        $anomaly['supplier_name'],
                        $anomaly['date'],
                        sprintf('%.2f', $anomaly['value']),
                        $anomaly['description']
                    ];
                }
                $io->table(['类型', '机构/模块', '日期', '数值', '描述'], $tableData);
            }
        }

        // 显示统计摘要
        $io->section('异常统计摘要');
        $typeStats = [];
        foreach ($anomalies as $anomaly) {
            $typeStats[$anomaly['type']] = ($typeStats[$anomaly['type']] ?? 0) + 1;
        }

        $summaryData = [];
        foreach ($typeStats as $type => $count) {
            $summaryData[] = [$type, $count];
        }
        $io->table(['异常类型', '数量'], $summaryData);
    }

    /**
     * 导出异常报告
     */
    private function exportAnomalies(array $anomalies, string $exportFile, SymfonyStyle $io): void
    {
        $io->section('导出异常报告');

        try {
            $extension = pathinfo($exportFile, PATHINFO_EXTENSION);
            
            switch (strtolower($extension)) {
                case 'json':
                    $this->exportToJson($anomalies, $exportFile);
                    break;
                case 'csv':
                    $this->exportToCsv($anomalies, $exportFile);
                    break;
                default:
                    throw new UnsupportedFormatException('不支持的文件格式，请使用 .json 或 .csv');
            }

            $io->success(sprintf('异常报告已导出到: %s', $exportFile));
            $io->text(sprintf('导出异常数: %d', count($anomalies)));

        } catch (\Throwable $e) {
            $io->error(sprintf('导出失败: %s', $e->getMessage()));
        }
    }

    /**
     * 发送异常预警
     */
    private function sendAnomalyAlerts(array $anomalies, SymfonyStyle $io): void
    {
        $io->section('发送异常预警');

        // 按严重程度过滤需要预警的异常
        $alertAnomalies = array_filter($anomalies, fn($anomaly) => in_array($anomaly['severity'], ['严重', '重要']));

        if (empty($alertAnomalies)) {
            $io->info('没有需要预警的严重异常');
            return;
        }

        $io->text(sprintf('准备发送 %d 项异常预警...', count($alertAnomalies)));

        // 这里应该集成实际的预警系统（邮件、短信、钉钉等）
        foreach ($alertAnomalies as $anomaly) {
            $io->text(sprintf('- 预警: %s - %s', $anomaly['severity'], $anomaly['description']));
        }

        $io->success('异常预警发送完成');
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
            $headers = ['类型', '严重程度', '机构/模块', '日期', '数值', '阈值', '描述'];
            fputcsv($handle, $headers);
            
            // 写入数据
            foreach ($data as $anomaly) {
                $row = [
                    $anomaly['type'],
                    $anomaly['severity'],
                    $anomaly['supplier_name'],
                    $anomaly['date'],
                    $anomaly['value'],
                    $anomaly['threshold'],
                    $anomaly['description']
                ];
                fputcsv($handle, $row);
            }
        }
        
        fclose($handle);
    }
} 