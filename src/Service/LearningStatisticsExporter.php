<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Service;

use Tourze\TrainSupervisorBundle\Exception\UnsupportedFormatException;

/**
 * 学习统计导出器
 * 负责处理学习统计数据的导出功能.
 */
class LearningStatisticsExporter
{
    /**
     * 导出学习统计数据.
     *
     * @param array<string, mixed> $statistics
     * @return array<string, mixed>
     */
    public function export(array $statistics, string $format): array
    {
        switch ($format) {
            case 'csv':
                return $this->exportToCsv($statistics);
            case 'excel':
                return $this->exportToExcel($statistics);
            case 'pdf':
                return $this->exportToPdf($statistics);
            default:
                throw new UnsupportedFormatException('不支持的导出格式');
        }
    }

    /**
     * 导出为CSV格式.
     *
     * @param array<string, mixed> $data
     * @return array{content: string, mime_type: string}
     */
    private function exportToCsv(array $data): array
    {
        $output = "机构名称,报名人数,完成人数,完成率,在线人数\n";
        $output .= $this->buildCsvRows($data);

        return [
            'content' => $output,
            'mime_type' => 'text/csv; charset=utf-8',
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function buildCsvRows(array $data): string
    {
        $rows = '';
        $byInstitution = $this->extractByInstitution($data);

        foreach ($byInstitution as $item) {
            $institutionName = $this->extractInstitutionName($item);
            $count = $this->extractCount($item);

            $rows .= sprintf(
                "%s,%d,%d,%.2f%%,%d\n",
                $institutionName,
                $count,
                0,
                0.0,
                0
            );
        }

        return $rows;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<int, array<string, mixed>>
     */
    private function extractByInstitution(array $data): array
    {
        if (!isset($data['enrollment']) || !is_array($data['enrollment'])) {
            return [];
        }

        $enrollment = $data['enrollment'];
        if (!isset($enrollment['by_institution']) || !is_array($enrollment['by_institution'])) {
            return [];
        }

        /** @var array<int, array<string, mixed>> */
        return $enrollment['by_institution'];
    }

    /**
     * @param mixed $item
     */
    private function extractInstitutionName(mixed $item): string
    {
        if (!is_array($item)) {
            return '';
        }

        $name = $item['institution_name'] ?? '';

        return is_string($name) ? $name : '';
    }

    /**
     * @param mixed $item
     */
    private function extractCount(mixed $item): int
    {
        if (!is_array($item)) {
            return 0;
        }

        $count = $item['count'] ?? 0;

        return is_int($count) ? $count : 0;
    }

    /**
     * 导出为Excel格式.
     *
     * @param array<string, mixed> $data
     * @return array{content: string, mime_type: string}
     */
    private function exportToExcel(array $data): array
    {
        return $this->exportToCsv($data);
    }

    /**
     * 导出为PDF格式.
     *
     * @param array<string, mixed> $data
     * @return array{content: string, mime_type: string}
     */
    private function exportToPdf(array $data): array
    {
        $html = '<h1>学习统计报告</h1>';
        $html .= '<p>生成时间：' . date('Y-m-d H:i:s') . '</p>';

        return [
            'content' => $html,
            'mime_type' => 'text/html; charset=utf-8',
        ];
    }
}
