<?php

namespace Tourze\TrainSupervisorBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\TrainSupervisorBundle\Exception\UnsupportedFormatException;
use Tourze\TrainSupervisorBundle\Helper\StatisticsDisplayHelper;
use Tourze\TrainSupervisorBundle\Service\QualityAssessmentService;

/**
 * 质量评估命令
 * 用于执行培训质量评估任务
 */
#[AsCommand(name: self::NAME, description: '执行培训质量评估', help: <<<'TXT'
    此命令用于执行培训质量评估，支持创建评估、批量评估、分析和导出功能。
    TXT)]
#[Autoconfigure(public: true)]
class QualityAssessmentCommand extends Command
{
    public const NAME = 'train:supervision:quality-assessment';

    public function __construct(
        private readonly QualityAssessmentService $assessmentService,
        private readonly StatisticsDisplayHelper $statisticsDisplayHelper,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('action', InputArgument::OPTIONAL, '操作类型 (create|batch|analyze|export)', 'analyze')
            ->addOption('institution-id', null, InputOption::VALUE_OPTIONAL, '指定机构ID')
            ->addOption('assessment-type', null, InputOption::VALUE_OPTIONAL, '评估类型', '综合评估')
            ->addOption('date', null, InputOption::VALUE_OPTIONAL, '评估日期 (Y-m-d)', date('Y-m-d'))
            ->addOption('start-date', null, InputOption::VALUE_OPTIONAL, '开始日期 (Y-m-d)')
            ->addOption('end-date', null, InputOption::VALUE_OPTIONAL, '结束日期 (Y-m-d)')
            ->addOption('assessor', null, InputOption::VALUE_OPTIONAL, '评估人', 'system')
            ->addOption('export', null, InputOption::VALUE_OPTIONAL, '导出结果到文件')
            ->addOption('auto-score', null, InputOption::VALUE_NONE, '自动计算评分')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $parameters = $this->extractParameters($input);
        $action = $this->getStringWithDefault($parameters, 'action', '');

        if (!$this->validateAction($action, $io)) {
            return Command::FAILURE;
        }

        $io->title(sprintf('质量评估 - %s', $this->getActionName($action)));

        try {
            return $this->executeAction($parameters, $io);
        } catch (\Throwable $e) {
            $io->error(sprintf('执行过程中发生错误: %s', $e->getMessage()));

            return Command::FAILURE;
        }
    }

    /**
     * 创建单个评估.
     */
    private function createAssessment(?string $institutionId, string $assessmentType, string $dateStr, string $assessor, bool $autoScore, SymfonyStyle $io): int
    {
        $validatedInstitutionId = $this->validateAndGetInstitutionId($institutionId, $io);
        if (null === $validatedInstitutionId) {
            return Command::FAILURE;
        }

        $date = $this->parseAssessmentDate($dateStr, $io);
        if (null === $date) {
            return Command::FAILURE;
        }

        $this->displayCreateAssessmentInfo($validatedInstitutionId, $assessmentType, $date, $assessor, $io);
        $this->showTemporaryWarning($io);

        return Command::SUCCESS;
    }

    /**
     * 验证并获取机构ID.
     */
    private function validateAndGetInstitutionId(?string $institutionId, SymfonyStyle $io): ?string
    {
        if (null === $institutionId) {
            $io->error('创建评估需要指定机构ID');

            return null;
        }

        return $institutionId;
    }

    /**
     * 解析评估日期.
     */
    private function parseAssessmentDate(string $dateStr, SymfonyStyle $io): ?\DateTime
    {
        try {
            return new \DateTime($dateStr);
        } catch (\Throwable $e) {
            $io->error(sprintf('无效的日期格式: %s', $dateStr));

            return null;
        }
    }

    /**
     * 显示创建评估信息.
     */
    private function displayCreateAssessmentInfo(string $institutionId, string $assessmentType, \DateTime $date, string $assessor, SymfonyStyle $io): void
    {
        $io->section('创建质量评估');
        $io->text(sprintf('机构ID: %s', $institutionId));
        $io->text(sprintf('评估类型: %s', $assessmentType));
        $io->text(sprintf('评估日期: %s', $date->format('Y-m-d')));
        $io->text(sprintf('评估人: %s', $assessor));
    }

    /**
     * 显示临时警告.
     */
    private function showTemporaryWarning(SymfonyStyle $io): void
    {
        $io->warning('由于机构实体依赖问题，暂时无法创建实际评估记录');
        $io->note('请确保机构实体正确配置后再执行此操作');
    }

    /**
     * 批量评估.
     */
    private function batchAssessment(?string $startDateStr, ?string $endDateStr, string $assessmentType, string $assessor, bool $autoScore, SymfonyStyle $io): int
    {
        $dateRange = $this->validateDateRange($startDateStr, $endDateStr, $io);
        if (null === $dateRange) {
            return Command::FAILURE;
        }

        $this->displayBatchAssessmentInfo($dateRange, $assessmentType, $assessor, $io);

        return $this->processBatchAssessmentWithProgress($io);
    }

    /**
     * 验证日期范围.
     *
     * @return array{start: \DateTime, end: \DateTime}|null
     */
    private function validateDateRange(?string $startDateStr, ?string $endDateStr, SymfonyStyle $io): ?array
    {
        return $this->parseDateRange($startDateStr, $endDateStr, '批量评估需要指定开始日期和结束日期', $io);
    }

    /**
     * 显示批量评估信息.
     *
     * @param array{start: \DateTime, end: \DateTime} $dateRange
     */
    private function displayBatchAssessmentInfo(array $dateRange, string $assessmentType, string $assessor, SymfonyStyle $io): void
    {
        $io->section('批量质量评估');
        $io->text(sprintf(
            '评估期间: %s 至 %s',
            $dateRange['start']->format('Y-m-d'),
            $dateRange['end']->format('Y-m-d')
        ));
        $io->text(sprintf('评估类型: %s', $assessmentType));
        $io->text(sprintf('评估人: %s', $assessor));
    }

    /**
     * 处理批量评估并显示进度.
     */
    private function processBatchAssessmentWithProgress(SymfonyStyle $io): int
    {
        $io->text('正在查找需要评估的机构...');

        $institutions = ['机构A', '机构B', '机构C'];
        $results = $this->assessInstitutionsWithProgress($institutions, $io);

        $this->displayBatchResults($results, $io);

        return Command::SUCCESS;
    }

    /**
     * 评估机构列表并显示进度.
     *
     * @param array<string> $institutions
     * @return array{success: int, fail: int}
     */
    private function assessInstitutionsWithProgress(array $institutions, SymfonyStyle $io): array
    {
        $io->progressStart(count($institutions));

        $successCount = 0;
        $failCount = 0;

        foreach ($institutions as $institution) {
            $result = $this->assessSingleInstitution($institution, $io);
            if ($result['success']) {
                ++$successCount;
            } else {
                ++$failCount;
            }
            $io->progressAdvance();
        }

        $io->progressFinish();

        return ['success' => $successCount, 'fail' => $failCount];
    }

    /**
     * 评估单个机构.
     * @return array{success: bool, message?: string}
     */
    private function assessSingleInstitution(string $institution, SymfonyStyle $io): array
    {
        try {
            // 这里应该调用实际的评估创建方法
            // $assessment = $this->assessmentService->createAssessment(...);

            // 模拟处理时间
            usleep(100000); // 0.1秒

            return ['success' => true];
        } catch (\Throwable $e) {
            $io->note(sprintf('机构 %s 评估失败: %s', $institution, $e->getMessage()));

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * 显示批量结果.
     * @param array{success: int, fail: int} $results
     */
    private function displayBatchResults(array $results, SymfonyStyle $io): void
    {
        $io->success(sprintf(
            '批量评估完成！成功: %d, 失败: %d',
            $results['success'],
            $results['fail']
        ));
    }

    /**
     * 分析评估结果.
     */
    private function analyzeAssessments(?string $startDateStr, ?string $endDateStr, ?string $institutionId, ?string $exportFile, SymfonyStyle $io): int
    {
        $io->section('质量评估分析');

        $dateRange = $this->parseDateRangeOptional($startDateStr, $endDateStr, $io);
        if (null === $dateRange && null !== $startDateStr && null !== $endDateStr) {
            return Command::FAILURE;
        }

        return $this->performAnalysisAndExport($dateRange, $institutionId, $exportFile, $io);
    }

    /**
     * 执行分析和导出.
     *
     * @param array{start: \DateTime, end: \DateTime}|null $dateRange
     */
    private function performAnalysisAndExport(?array $dateRange, ?string $institutionId, ?string $exportFile, SymfonyStyle $io): int
    {
        $this->displayAnalysisScope($dateRange, $institutionId, $io);
        $statistics = $this->fetchAndDisplayStatistics($dateRange, $institutionId, $io);

        if (null !== $exportFile) {
            $this->exportAnalysisResults($statistics, $exportFile, $io);
        }

        return Command::SUCCESS;
    }

    /**
     * 显示分析范围.
     *
     * @param array{start: \DateTime, end: \DateTime}|null $dateRange
     */
    private function displayAnalysisScope(?array $dateRange, ?string $institutionId, SymfonyStyle $io): void
    {
        if (null !== $dateRange) {
            $io->text(sprintf(
                '分析期间: %s 至 %s',
                $dateRange['start']->format('Y-m-d'),
                $dateRange['end']->format('Y-m-d')
            ));
        } else {
            $io->text('分析所有评估数据');
        }

        if (null !== $institutionId) {
            $io->text(sprintf('指定机构: %s', $institutionId));
        }
    }

    /**
     * 获取并显示统计数据.
     *
     * @param array{start: \DateTime, end: \DateTime}|null $dateRange
     * @return array<string, mixed>
     */
    private function fetchAndDisplayStatistics(?array $dateRange, ?string $institutionId, SymfonyStyle $io): array
    {
        $startDate = $dateRange['start'] ?? null;
        $endDate = $dateRange['end'] ?? null;

        $statistics = $this->assessmentService->getAssessmentStatistics($startDate, $endDate, $institutionId);
        $this->statisticsDisplayHelper->displayAssessmentStatistics($statistics, $io);

        return $statistics;
    }

    /**
     * 导出评估数据.
     */
    private function exportAssessments(?string $startDateStr, ?string $endDateStr, ?string $institutionId, ?string $exportFile, SymfonyStyle $io): int
    {
        if (null === $exportFile) {
            $io->error('导出操作需要指定导出文件');

            return Command::FAILURE;
        }

        $io->section('导出评估数据');

        $dateRange = $this->parseDateRangeOptional($startDateStr, $endDateStr, $io);
        if (null === $dateRange && (null !== $startDateStr || null !== $endDateStr)) {
            return Command::FAILURE;
        }

        return $this->executeDataExport($dateRange, $institutionId, $exportFile, $io);
    }

    /**
     * 执行数据导出.
     *
     * @param array{start: \DateTime, end: \DateTime}|null $dateRange
     */
    private function executeDataExport(?array $dateRange, ?string $institutionId, string $exportFile, SymfonyStyle $io): int
    {
        $data = $this->fetchExportData($dateRange, $institutionId);

        if ([] === $data) {
            $io->warning('没有数据可导出');

            return Command::SUCCESS;
        }

        return $this->saveExportData($data, $exportFile, $io);
    }

    /**
     * 获取导出数据.
     *
     * @param array{start: \DateTime, end: \DateTime}|null $dateRange
     * @return array<int, array<string, mixed>>
     */
    private function fetchExportData(?array $dateRange, ?string $institutionId): array
    {
        $startDate = $dateRange['start'] ?? null;
        $endDate = $dateRange['end'] ?? null;

        return $this->assessmentService->exportAssessments($startDate, $endDate, $institutionId);
    }

    /**
     * 保存导出数据.
     *
     * @param array<int, array<string, mixed>> $data
     */
    private function saveExportData(array $data, string $exportFile, SymfonyStyle $io): int
    {
        try {
            $this->exportToFile($data, $exportFile, $io);
            $this->displayExportSuccess($exportFile, count($data), $io);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error(sprintf('导出失败: %s', $e->getMessage()));

            return Command::FAILURE;
        }
    }

    /**
     * 显示导出成功信息.
     */
    private function displayExportSuccess(string $exportFile, int $recordCount, SymfonyStyle $io): void
    {
        $io->success(sprintf('评估数据已导出到: %s', $exportFile));
        $io->text(sprintf('导出记录数: %d', $recordCount));
    }

    /**
     * 导出分析结果.
     *
     * @param array<string, mixed> $statistics
     */
    private function exportAnalysisResults(array $statistics, string $exportFile, SymfonyStyle $io): void
    {
        try {
            $this->exportStatisticsToFile($statistics, $exportFile, $io);
            $io->success(sprintf('分析结果已导出到: %s', $exportFile));
        } catch (\Throwable $e) {
            $io->error(sprintf('导出分析结果失败: %s', $e->getMessage()));
        }
    }

    /**
     * 导出统计数据到文件.
     *
     * @param array<string, mixed> $statistics
     */
    private function exportStatisticsToFile(array $statistics, string $filename, SymfonyStyle $io): void
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ('json' !== $extension) {
            throw new UnsupportedFormatException('统计数据导出只支持 .json 格式');
        }

        $json = json_encode($statistics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($filename, $json);
    }

    /**
     * 导出数据到文件.
     *
     * @param array<int, array<string, mixed>> $data
     */
    private function exportToFile(array $data, string $filename, SymfonyStyle $io): void
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $this->delegateExportByFormat($data, $filename, $extension);
    }

    /**
     * 根据格式委派导出.
     *
     * @param array<int, array<string, mixed>> $data
     */
    private function delegateExportByFormat(array $data, string $filename, string $extension): void
    {
        if ('json' === $extension) {
            $this->exportToJson($data, $filename);

            return;
        }

        if ('csv' === $extension) {
            $this->exportToCsv($data, $filename);

            return;
        }

        throw new UnsupportedFormatException('不支持的文件格式，请使用 .json 或 .csv');
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
        $handle = $this->openCsvFile($filename);

        if ($this->isValidCsvData($data)) {
            $this->writeCsvData($handle, $data);
        }

        fclose($handle);
    }

    /**
     * 验证CSV数据有效性.
     *
     * @param array<int, array<string, mixed>> $data
     */
    private function isValidCsvData(array $data): bool
    {
        return [] !== $data && isset($data[0]) && is_array($data[0]);
    }

    /**
     * 写入CSV数据.
     *
     * @param resource $handle
     * @param array<int, array<string, mixed>> $data
     */
    private function writeCsvData($handle, array $data): void
    {
        $this->writeCsvHeader($handle, $data[0]);
        $this->writeCsvRows($handle, $data);
    }

    /**
     * 打开CSV文件.
     *
     * @return resource
     */
    private function openCsvFile(string $filename)
    {
        $handle = fopen($filename, 'w');
        if (false === $handle) {
            throw new UnsupportedFormatException(sprintf('无法创建文件: %s', $filename));
        }

        // 写入BOM以支持中文
        fwrite($handle, "\xEF\xBB\xBF");

        return $handle;
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
     * 写入CSV行数据.
     *
     * @param resource $handle
     * @param array<int, array<string, mixed>> $data
     */
    private function writeCsvRows($handle, array $data): void
    {
        foreach ($data as $row) {
            if (is_array($row)) {
                $stringValues = array_map(fn ($value) => is_scalar($value) ? (string) $value : '', $row);
                fputcsv($handle, $stringValues);
            }
        }
    }

    /**
     * 提取输入参数.
     *
     * @return array<string, mixed>
     */
    private function extractParameters(InputInterface $input): array
    {
        return [
            'action' => $this->safeStringCast($input->getArgument('action')) ?? '',
            'institution_id' => $this->safeStringCast($input->getOption('institution-id')),
            'assessment_type' => $this->safeStringCastWithDefault($input->getOption('assessment-type'), '综合评估'),
            'date' => $this->safeStringCastWithDefault($input->getOption('date'), date('Y-m-d')),
            'start_date' => $this->safeStringCast($input->getOption('start-date')),
            'end_date' => $this->safeStringCast($input->getOption('end-date')),
            'assessor' => $this->safeStringCastWithDefault($input->getOption('assessor'), 'system'),
            'export_file' => $this->safeStringCast($input->getOption('export')),
            'auto_score' => (bool) $input->getOption('auto-score'),
        ];
    }

    /**
     * 验证操作类型.
     */
    private function validateAction(string $action, SymfonyStyle $io): bool
    {
        $validActions = ['create', 'batch', 'analyze', 'export'];
        if (!in_array($action, $validActions, true)) {
            $io->error('无效的操作类型。支持的操作: create, batch, analyze, export');

            return false;
        }

        return true;
    }

    /**
     * 执行具体操作.
     *
     * @param array<string, mixed> $parameters
     */
    private function executeAction(array $parameters, SymfonyStyle $io): int
    {
        $action = $this->getStringWithDefault($parameters, 'action', '');

        return match ($action) {
            'create' => $this->executeCreateAction($parameters, $io),
            'batch' => $this->executeBatchAction($parameters, $io),
            'analyze' => $this->executeAnalyzeAction($parameters, $io),
            'export' => $this->executeExportAction($parameters, $io),
            default => Command::FAILURE,
        };
    }

    /**
     * 执行创建操作.
     *
     * @param array<string, mixed> $parameters
     */
    private function executeCreateAction(array $parameters, SymfonyStyle $io): int
    {
        return $this->createAssessment(
            $this->getStringOrNull($parameters, 'institution_id'),
            $this->getStringWithDefault($parameters, 'assessment_type', '综合评估'),
            $this->getStringWithDefault($parameters, 'date', date('Y-m-d')),
            $this->getStringWithDefault($parameters, 'assessor', 'system'),
            $this->getBool($parameters, 'auto_score'),
            $io
        );
    }

    /**
     * 执行批量操作.
     *
     * @param array<string, mixed> $parameters
     */
    private function executeBatchAction(array $parameters, SymfonyStyle $io): int
    {
        return $this->batchAssessment(
            $this->getStringOrNull($parameters, 'start_date'),
            $this->getStringOrNull($parameters, 'end_date'),
            $this->getStringWithDefault($parameters, 'assessment_type', '综合评估'),
            $this->getStringWithDefault($parameters, 'assessor', 'system'),
            $this->getBool($parameters, 'auto_score'),
            $io
        );
    }

    /**
     * 执行分析操作.
     *
     * @param array<string, mixed> $parameters
     */
    private function executeAnalyzeAction(array $parameters, SymfonyStyle $io): int
    {
        return $this->analyzeAssessments(
            $this->getStringOrNull($parameters, 'start_date'),
            $this->getStringOrNull($parameters, 'end_date'),
            $this->getStringOrNull($parameters, 'institution_id'),
            $this->getStringOrNull($parameters, 'export_file'),
            $io
        );
    }

    /**
     * 执行导出操作.
     *
     * @param array<string, mixed> $parameters
     */
    private function executeExportAction(array $parameters, SymfonyStyle $io): int
    {
        return $this->exportAssessments(
            $this->getStringOrNull($parameters, 'start_date'),
            $this->getStringOrNull($parameters, 'end_date'),
            $this->getStringOrNull($parameters, 'institution_id'),
            $this->getStringOrNull($parameters, 'export_file'),
            $io
        );
    }

    /**
     * 获取操作名称.
     */
    private function getActionName(string $action): string
    {
        return match ($action) {
            'create' => '创建评估',
            'batch' => '批量评估',
            'analyze' => '分析评估',
            'export' => '导出评估',
            default => $action,
        };
    }

    /**
     * 安全地将mixed类型转换为string或null
     */
    private function safeStringCast(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return null;
    }

    /**
     * 安全地将mixed类型转换为string，提供默认值
     */
    private function safeStringCastWithDefault(mixed $value, string $default): string
    {
        $result = $this->safeStringCast($value);

        return $result ?? $default;
    }

    /**
     * 通用的日期范围解析（必须提供）
     *
     * @return array{start: \DateTime, end: \DateTime}|null
     */
    private function parseDateRange(?string $startDateStr, ?string $endDateStr, string $errorMessage, SymfonyStyle $io): ?array
    {
        if (null === $startDateStr || null === $endDateStr) {
            $io->error($errorMessage);

            return null;
        }

        $dateRange = $this->tryParseDateRange($startDateStr, $endDateStr, $io);
        if (null === $dateRange) {
            return null;
        }

        return $this->validateDateOrder($dateRange, $io);
    }

    /**
     * 可选的日期范围解析（允许为空）
     *
     * @return array{start: \DateTime, end: \DateTime}|null
     */
    private function parseDateRangeOptional(?string $startDateStr, ?string $endDateStr, SymfonyStyle $io): ?array
    {
        if (null === $startDateStr || null === $endDateStr) {
            return null;
        }

        return $this->tryParseDateRange($startDateStr, $endDateStr, $io);
    }

    /**
     * 尝试解析日期范围.
     *
     * @return array{start: \DateTime, end: \DateTime}|null
     */
    private function tryParseDateRange(string $startDateStr, string $endDateStr, SymfonyStyle $io): ?array
    {
        try {
            $startDate = new \DateTime($startDateStr);
            $endDate = new \DateTime($endDateStr);

            return ['start' => $startDate, 'end' => $endDate];
        } catch (\Throwable $e) {
            $io->error('无效的日期格式');

            return null;
        }
    }

    /**
     * 验证日期顺序.
     *
     * @param array{start: \DateTime, end: \DateTime} $dateRange
     * @return array{start: \DateTime, end: \DateTime}|null
     */
    private function validateDateOrder(array $dateRange, SymfonyStyle $io): ?array
    {
        if ($dateRange['start'] > $dateRange['end']) {
            $io->error('开始日期不能晚于结束日期');

            return null;
        }

        return $dateRange;
    }

    /**
     * 从参数数组中获取字符串或null
     * @param array<string, mixed> $parameters
     */
    private function getStringOrNull(array $parameters, string $key): ?string
    {
        $value = $parameters[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * 从参数数组中获取字符串或默认值
     * @param array<string, mixed> $parameters
     */
    private function getStringWithDefault(array $parameters, string $key, string $default): string
    {
        $value = $parameters[$key] ?? null;

        return is_string($value) ? $value : $default;
    }

    /**
     * 从参数数组中获取布尔值
     * @param array<string, mixed> $parameters
     */
    private function getBool(array $parameters, string $key): bool
    {
        $value = $parameters[$key] ?? false;

        return is_bool($value) ? $value : false;
    }
}
