<?php

namespace Tourze\TrainSupervisorBundle\Service;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
class AnomalyDisplayService
{
    /**
     * @param array<int, array<string, mixed>> $anomalies
     */
    public function displayAnomalies(array $anomalies, bool $verbose, SymfonyStyle $io): void
    {
        if ([] === $anomalies) {
            return;
        }

        $io->section('异常检测结果');
        $groupedAnomalies = $this->groupAnomaliesBySeverity($anomalies);
        $this->displayGroupedAnomalies($groupedAnomalies, $verbose, $io);
        $this->displayAnomalySummary($anomalies, $io);
    }

    /**
     * @param array<int, array<string, mixed>> $anomalies
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function groupAnomaliesBySeverity(array $anomalies): array
    {
        $groupedAnomalies = [];
        foreach ($anomalies as $anomaly) {
            $severity = $anomaly['severity'];
            assert(is_string($severity));
            $groupedAnomalies[$severity][] = $anomaly;
        }

        return $groupedAnomalies;
    }

    /**
     * @param array<string, array<int, array<string, mixed>>> $groupedAnomalies
     */
    private function displayGroupedAnomalies(array $groupedAnomalies, bool $verbose, SymfonyStyle $io): void
    {
        $severityOrder = ['严重', '重要', '一般', '轻微'];

        foreach ($severityOrder as $severity) {
            if (!isset($groupedAnomalies[$severity])) {
                continue;
            }

            $count = count($groupedAnomalies[$severity]);
            $io->section(sprintf('%s异常 (%d项)', $severity, $count));

            if ($verbose) {
                $this->displayVerboseAnomalies($groupedAnomalies[$severity], $io);
            } else {
                $this->displayTableAnomalies($groupedAnomalies[$severity], $io);
            }
        }
    }

    /**
     * @param array<int, array<string, mixed>> $anomalies
     */
    private function displayVerboseAnomalies(array $anomalies, SymfonyStyle $io): void
    {
        foreach ($anomalies as $anomaly) {
            $this->displaySingleVerboseAnomaly($anomaly, $io);
        }
    }

    /**
     * @param array<string, mixed> $anomaly
     */
    private function displaySingleVerboseAnomaly(array $anomaly, SymfonyStyle $io): void
    {
        $supplierName = $anomaly['supplier_name'];
        $description = $anomaly['description'];
        $details = $anomaly['details'] ?? [];
        assert(is_string($supplierName));
        assert(is_string($description));
        assert(is_array($details));

        // 确保 details 是 array<string, mixed> 类型
        $stringKeyedDetails = [];
        foreach ($details as $key => $value) {
            $stringKeyedDetails[(string) $key] = $value;
        }

        $io->text(sprintf('- %s: %s', $supplierName, $description));
        $this->displayAnomalyDetails($stringKeyedDetails, $io);
        $io->newLine();
    }

    /**
     * @param array<string, mixed> $details
     */
    private function displayAnomalyDetails(array $details, SymfonyStyle $io): void
    {
        if ([] === $details) {
            return;
        }

        foreach ($details as $key => $value) {
            $keyStr = is_string($key) ? $key : (string) $key;
            $valueStr = is_scalar($value) ? (string) $value : json_encode($value);
            $io->text(sprintf('  %s: %s', $keyStr, $valueStr));
        }
    }

    /**
     * @param array<int, array<string, mixed>> $anomalies
     */
    private function displayTableAnomalies(array $anomalies, SymfonyStyle $io): void
    {
        $tableData = [];
        foreach ($anomalies as $anomaly) {
            $type = $anomaly['type'];
            $supplierName = $anomaly['supplier_name'];
            $date = $anomaly['date'];
            $value = $anomaly['value'];
            $description = $anomaly['description'];
            assert(is_string($type));
            assert(is_string($supplierName));
            assert(is_string($date));
            assert(is_numeric($value));
            assert(is_string($description));

            $tableData[] = [
                $type,
                $supplierName,
                $date,
                sprintf('%.2f', $value),
                $description,
            ];
        }
        $io->table(['类型', '机构/模块', '日期', '数值', '描述'], $tableData);
    }

    /**
     * @param array<int, array<string, mixed>> $anomalies
     */
    private function displayAnomalySummary(array $anomalies, SymfonyStyle $io): void
    {
        $io->section('异常统计摘要');
        $typeStats = [];
        foreach ($anomalies as $anomaly) {
            $type = $anomaly['type'];
            assert(is_string($type));
            $typeStats[$type] = ($typeStats[$type] ?? 0) + 1;
        }

        $summaryData = [];
        foreach ($typeStats as $type => $count) {
            $summaryData[] = [$type, $count];
        }
        $io->table(['异常类型', '数量'], $summaryData);
    }
}
