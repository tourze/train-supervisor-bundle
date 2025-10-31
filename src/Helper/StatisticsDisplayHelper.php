<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Helper;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 统计显示助手类
 * 负责格式化和显示评估统计信息
 */
class StatisticsDisplayHelper
{
    /**
     * 显示评估统计信息.
     *
     * @param array<string, mixed> $statistics
     */
    public function displayAssessmentStatistics(array $statistics, SymfonyStyle $io): void
    {
        $this->displayBasicStatistics($statistics, $io);
        $this->displayTypeStatistics($statistics, $io);
        $this->displayInstitutionStatistics($statistics, $io);
        $this->displayTrendAnalysis($statistics, $io);
    }

    /**
     * @param array<string, mixed> $statistics
     */
    private function displayBasicStatistics(array $statistics, SymfonyStyle $io): void
    {
        $io->section('评估统计概览');
        $io->definitionList(...$this->buildBasicStatisticsList($statistics));
    }

    /**
     * 构建基本统计列表.
     *
     * @param array<string, mixed> $statistics
     * @return array<int, array<string, string|int>>
     */
    private function buildBasicStatisticsList(array $statistics): array
    {
        $totalAssessments = is_scalar($statistics['total_assessments'] ?? null) ? (int) $statistics['total_assessments'] : 0;

        return [
            ['总评估数' => $totalAssessments],
            ['平均分数' => $this->formatFloat($statistics['average_score'] ?? 0)],
            ['最高分数' => $this->formatFloat($statistics['max_score'] ?? 0)],
            ['最低分数' => $this->formatFloat($statistics['min_score'] ?? 0)],
            ['优秀率' => $this->formatPercentage($statistics['excellent_rate'] ?? 0)],
            ['良好率' => $this->formatPercentage($statistics['good_rate'] ?? 0)],
            ['合格率' => $this->formatPercentage($statistics['pass_rate'] ?? 0)],
        ];
    }

    /**
     * @param array<string, mixed> $statistics
     */
    private function displayTypeStatistics(array $statistics, SymfonyStyle $io): void
    {
        $byType = $statistics['by_type'] ?? [];
        if (!is_array($byType) || [] === $byType) {
            return;
        }

        $io->section('按评估类型统计');
        $tableData = $this->buildTypeTableData($byType);
        $io->table(['评估类型', '数量', '平均分', '合格率'], $tableData);
    }

    /**
     * 构建类型统计表格数据.
     *
     * @param mixed $byType
     * @return array<int, array<int, string|int>>
     */
    private function buildTypeTableData($byType): array
    {
        if (!is_array($byType)) {
            return [];
        }

        $tableData = [];
        foreach ($byType as $type => $data) {
            if (!is_array($data)) {
                continue;
            }
            // 确保$data是关联数组
            $dataTyped = [];
            foreach ($data as $key => $value) {
                if (is_string($key)) {
                    $dataTyped[$key] = $value;
                }
            }
            $tableData[] = $this->buildStatisticsRowData($type, $dataTyped);
        }

        return $tableData;
    }

    /**
     * @param array<string, mixed> $statistics
     */
    private function displayInstitutionStatistics(array $statistics, SymfonyStyle $io): void
    {
        $byInstitution = $statistics['by_institution'] ?? [];
        if (!is_array($byInstitution) || [] === $byInstitution) {
            return;
        }

        $io->section('按机构统计（前10名）');
        $tableData = $this->buildInstitutionTableData($byInstitution);
        $io->table(['机构名称', '评估次数', '平均分', '合格率'], $tableData);
    }

    /**
     * 构建机构统计表格数据.
     *
     * @param mixed $byInstitution
     * @return array<int, array<int, string|int>>
     */
    private function buildInstitutionTableData($byInstitution): array
    {
        if (!is_array($byInstitution)) {
            return [];
        }

        $tableData = [];
        $count = 0;
        foreach ($byInstitution as $institution => $data) {
            if ($count >= 10) {
                break;
            }

            if (!$this->isValidKeyAndData($institution, $data)) {
                continue;
            }

            $tableData[] = $this->buildStatisticsRowData($institution, $this->normalizeAssoc($data));
            ++$count;
        }

        return $tableData;
    }

    /**
     * @param mixed $key
     * @param mixed $data
     */
    private function isValidKeyAndData($key, $data): bool
    {
        if (!is_string($key) && !is_int($key)) {
            return false;
        }

        return is_array($data);
    }

    /**
     * 规范化为关联数组（过滤掉非字符串键）
     *
     * @param mixed $data
     * @return array<string, mixed>
     */
    private function normalizeAssoc($data): array
    {
        if (!is_array($data)) {
            return [];
        }

        $assoc = [];
        foreach ($data as $key => $value) {
            if (is_string($key)) {
                $assoc[$key] = $value;
            }
        }

        return $assoc;
    }

    /**
     * @param array<string, mixed> $statistics
     */
    private function displayTrendAnalysis(array $statistics, SymfonyStyle $io): void
    {
        if (!isset($statistics['trends']) || [] === $statistics['trends']) {
            return;
        }

        $io->section('评估趋势');
        $io->text('最近评估趋势分析:');
        $this->renderTrendItems($statistics['trends'], $io);
    }

    /**
     * 渲染趋势项目.
     *
     * @param mixed $trends
     */
    private function renderTrendItems($trends, SymfonyStyle $io): void
    {
        if (!is_array($trends)) {
            return;
        }

        foreach ($trends as $trend) {
            if (!is_array($trend)) {
                continue;
            }
            $directionValue = $trend['direction'] ?? '';
            $directionStr = is_string($directionValue) ? $directionValue : '';
            $direction = $this->getTrendDirection($directionStr);

            $metricValue = $trend['metric'] ?? '';
            $metricStr = is_string($metricValue) ? $metricValue : '';

            $percentageValue = $trend['percentage'] ?? 0;
            $percentageFloat = is_numeric($percentageValue) ? (float) $percentageValue : 0.0;

            $io->text(sprintf(
                '- %s: %s (变化: %.2f%%)',
                $metricStr,
                $direction,
                $percentageFloat
            ));
        }
    }

    /**
     * 获取趋势方向.
     */
    private function getTrendDirection(string $direction): string
    {
        return match ($direction) {
            'up' => '↗️ 上升',
            'down' => '↘️ 下降',
            default => '➡️ 稳定',
        };
    }

    /**
     * 构建统计表格行数据.
     *
     * @param mixed $name
     * @param array<string, mixed> $data
     * @return array<int, string|int>
     */
    private function buildStatisticsRowData($name, array $data): array
    {
        $nameStr = is_string($name) || is_int($name) ? (string) $name : '';
        $countValue = $data['count'] ?? 0;
        $count = is_scalar($countValue) ? (int) $countValue : 0;

        return [
            $nameStr,
            $count,
            $this->formatFloat($data['average_score'] ?? 0),
            $this->formatPercentage($data['pass_rate'] ?? 0),
        ];
    }

    /**
     * 格式化浮点数.
     */
    private function formatFloat(mixed $value): string
    {
        $floatValue = is_numeric($value) ? (float) $value : 0.0;

        return sprintf('%.2f', $floatValue);
    }

    /**
     * 格式化百分比.
     */
    private function formatPercentage(mixed $value): string
    {
        $floatValue = is_numeric($value) ? (float) $value : 0.0;

        return sprintf('%.2f%%', $floatValue);
    }
}
