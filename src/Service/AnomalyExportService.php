<?php

namespace Tourze\TrainSupervisorBundle\Service;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\TrainSupervisorBundle\Exception\UnsupportedFormatException;

#[Autoconfigure(public: true)]
class AnomalyExportService
{
    /**
     * @param array<int, array<string, mixed>> $anomalies
     */
    public function exportAnomalies(array $anomalies, string $exportFile, SymfonyStyle $io): void
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
     * @param array<int, array<string, mixed>> $data
     */
    private function exportToJson(array $data, string $filename): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($filename, $json);
    }

    /**
     * @param array<int, array<string, mixed>> $data
     */
    private function exportToCsv(array $data, string $filename): void
    {
        $handle = fopen($filename, 'w');
        if (false === $handle) {
            throw new \RuntimeException(sprintf('无法打开文件用于写入: %s', $filename));
        }

        // 写入BOM以支持中文
        fwrite($handle, "\xEF\xBB\xBF");

        // 写入表头
        if ([] !== $data) {
            $headers = ['类型', '严重程度', '机构/模块', '日期', '数值', '阈值', '描述'];
            fputcsv($handle, $headers, ',', '"', '\\');

            // 写入数据
            foreach ($data as $anomaly) {
                $type = $anomaly['type'];
                $severity = $anomaly['severity'];
                $supplierName = $anomaly['supplier_name'];
                $date = $anomaly['date'];
                $value = $anomaly['value'];
                $threshold = $anomaly['threshold'];
                $description = $anomaly['description'];
                assert(is_string($type));
                assert(is_string($severity));
                assert(is_string($supplierName));
                assert(is_string($date));
                assert(is_numeric($value));
                assert(is_numeric($threshold));
                assert(is_string($description));

                $row = [
                    $type,
                    $severity,
                    $supplierName,
                    $date,
                    (string) $value,
                    (string) $threshold,
                    $description,
                ];
                fputcsv($handle, $row, ',', '"', '\\');
            }
        }

        fclose($handle);
    }
}
