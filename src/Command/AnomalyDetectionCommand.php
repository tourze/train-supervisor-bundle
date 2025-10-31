<?php

namespace Tourze\TrainSupervisorBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\TrainSupervisorBundle\Service\AnomalyAlertService;
use Tourze\TrainSupervisorBundle\Service\AnomalyDetectionService;
use Tourze\TrainSupervisorBundle\Service\AnomalyDisplayService;
use Tourze\TrainSupervisorBundle\Service\AnomalyExportService;

/**
 * 异常检测命令
 * 用于检测监督数据中的异常情况并生成预警.
 */
#[AsCommand(name: self::NAME, description: '检测监督数据异常', help: <<<'TXT'
    此命令用于检测监督数据中的异常情况，包括作弊率异常、人脸识别异常、学习转化率异常等。
    TXT)]
#[Autoconfigure(public: true)]
class AnomalyDetectionCommand extends Command
{
    public const NAME = 'train:supervision:anomaly-detection';

    public function __construct(
        private readonly AnomalyDetectionService $anomalyDetectionService,
        private readonly AnomalyDisplayService $anomalyDisplayService,
        private readonly AnomalyExportService $anomalyExportService,
        private readonly AnomalyAlertService $anomalyAlertService,
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $options = $this->extractOptions($input);

        $type = is_string($options['type']) ? $options['type'] : 'all';
        if (!$this->validateDetectionType($type, $io)) {
            $io->error('无效的检测类型');

            return Command::FAILURE;
        }

        $io->title('监督数据异常检测');

        try {
            return $this->runDetection($options, $io);
        } catch (\Throwable $e) {
            $io->error(sprintf('异常检测过程中发生错误: %s', $e->getMessage()));
            $io->error('错误追踪: ' . $e->getTraceAsString());

            return Command::FAILURE;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function extractOptions(InputInterface $input): array
    {
        return [
            'date' => $input->getOption('date'),
            'start_date' => $input->getOption('start-date'),
            'end_date' => $input->getOption('end-date'),
            'type' => $input->getOption('type'),
            'threshold_json' => $input->getOption('threshold'),
            'export_file' => $input->getOption('export'),
            'auto_alert' => $input->getOption('auto-alert'),
            'verbose_output' => $input->getOption('verbose-output'),
        ];
    }

    private function validateDetectionType(string $type, SymfonyStyle $io): bool
    {
        if (!in_array($type, ['all', 'cheat', 'face', 'learn', 'problem'], true)) {
            $io->error('无效的检测类型。支持的类型: all, cheat, face, learn, problem');

            return false;
        }

        return true;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function runDetection(array $options, SymfonyStyle $io): int
    {
        $dateRange = $this->determineDateRange($options, $io);
        $thresholdJson = is_string($options['threshold_json']) ? $options['threshold_json'] : null;
        $thresholds = $this->parseThresholds($thresholdJson, $io);
        $type = is_string($options['type']) ? $options['type'] : 'all';
        $verbose = (bool) $options['verbose_output'];
        $exportFile = is_string($options['export_file']) ? $options['export_file'] : null;

        $io->section('执行异常检测');
        $io->text(sprintf('检测类型: %s', $type));

        $anomalies = $this->anomalyDetectionService->detectAnomalies(
            $dateRange['start'],
            $dateRange['end'],
            $type,
            $thresholds
        );

        $this->anomalyDisplayService->displayAnomalies($anomalies, $verbose, $io);

        if (null !== $exportFile) {
            $this->anomalyExportService->exportAnomalies($anomalies, $exportFile, $io);
        }

        if ((bool) $options['auto_alert'] && [] !== $anomalies) {
            $this->anomalyAlertService->sendAnomalyAlerts($anomalies, $io);
        }

        return $this->generateResult($anomalies, $io);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, \DateTime>
     */
    private function determineDateRange(array $options, SymfonyStyle $io): array
    {
        $startDateStr = is_string($options['start_date']) ? $options['start_date'] : null;
        $endDateStr = is_string($options['end_date']) ? $options['end_date'] : null;
        $dateStr = is_string($options['date']) ? $options['date'] : date('Y-m-d');

        if (null !== $startDateStr && null !== $endDateStr) {
            $startDate = new \DateTime($startDateStr);
            $endDate = new \DateTime($endDateStr);
            $io->text(sprintf('检测期间: %s 至 %s', $startDate->format('Y-m-d'), $endDate->format('Y-m-d')));
        } else {
            $date = new \DateTime($dateStr);
            $startDate = (clone $date)->setTime(0, 0, 0);
            $endDate = (clone $date)->setTime(23, 59, 59);
            $io->text(sprintf('检测日期: %s', $date->format('Y-m-d')));
        }

        return ['start' => $startDate, 'end' => $endDate];
    }

    /**
     * @param array<mixed> $anomalies
     */
    private function generateResult(array $anomalies, SymfonyStyle $io): int
    {
        if ([] === $anomalies) {
            $io->success('未检测到异常数据');
        } else {
            $io->warning(sprintf('检测到 %d 项异常，请及时处理', count($anomalies)));
        }

        return Command::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseThresholds(?string $thresholdJson, SymfonyStyle $io): array
    {
        /** @var array<string, mixed> $defaultThresholds */
        $defaultThresholds = [
            'cheat_rate' => 5.0,           // 作弊率超过5%
            'face_fail_rate' => 20.0,      // 人脸识别失败率超过20%
            'learn_conversion_rate' => 50.0, // 学习转化率低于50%
            'problem_overdue_days' => 3,    // 问题逾期超过3天
            'new_classroom_ratio' => 100.0, // 新开班比例超过100%
        ];

        if (null === $thresholdJson) {
            return $defaultThresholds;
        }

        try {
            $decoded = json_decode($thresholdJson, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($decoded)) {
                return $defaultThresholds;
            }

            /** @var array<string, mixed> $customThresholds */
            $customThresholds = $decoded;

            return array_merge($defaultThresholds, $customThresholds);
        } catch (\JsonException $e) {
            $io->warning(sprintf('阈值配置解析失败，使用默认配置: %s', $e->getMessage()));

            return $defaultThresholds;
        }
    }
}
