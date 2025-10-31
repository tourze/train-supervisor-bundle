<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Service;

use Tourze\TrainSupervisorBundle\Entity\SupervisionInspection;

/**
 * 检查趋势分析器
 * 负责分析检查数据的趋势.
 */
class InspectionTrendAnalyzer
{
    /**
     * @param SupervisionInspection[] $inspections
     * @return array<string, array<string, mixed>>
     */
    public function buildTrendData(array $inspections): array
    {
        $trends = ['inspections' => [], 'problems' => [], 'scores' => []];

        foreach ($inspections as $inspection) {
            $inspectionDate = $inspection->getInspectionDate();
            $date = $inspectionDate->format('Y-m-d');
            $trends = $this->initializeTrendDataForDate($trends, $date);
            $trends = $this->updateTrendDataForInspection($trends, $date, $inspection);
        }

        return $this->finalizeTrendData($trends);
    }

    /**
     * @param array<string, array<string, mixed>> $trends
     * @return array<string, array<string, mixed>>
     */
    private function initializeTrendDataForDate(array $trends, string $date): array
    {
        if (!isset($trends['inspections'][$date])) {
            $trends['inspections'][$date] = 0;
            $trends['problems'][$date] = 0;
            $trends['scores'][$date] = [];
        }

        return $trends;
    }

    /**
     * @param array<string, array<string, mixed>> $trends
     * @return array<string, array<string, mixed>>
     */
    private function updateTrendDataForInspection(array $trends, string $date, SupervisionInspection $inspection): array
    {
        $inspectionCount = $trends['inspections'][$date];
        assert(is_int($inspectionCount));
        $trends['inspections'][$date] = $inspectionCount + 1;

        if ($inspection->hasProblems()) {
            $problemCount = $trends['problems'][$date];
            assert(is_int($problemCount));
            $trends['problems'][$date] = $problemCount + 1;
        }

        if (null !== $inspection->getOverallScore()) {
            $dateScores = $trends['scores'][$date];
            assert(is_array($dateScores));
            $dateScores[] = $inspection->getOverallScore();
            $trends['scores'][$date] = $dateScores;
        }

        return $trends;
    }

    /**
     * @param array<string, array<string, mixed>> $trends
     * @return array<string, array<string, mixed>>
     */
    private function finalizeTrendData(array $trends): array
    {
        foreach ($trends['scores'] as $date => $scores) {
            assert(is_array($scores));
            if ([] !== $scores) {
                $validScores = array_filter($scores, 'is_numeric');
                $trends['scores'][$date] = [] !== $validScores ? array_sum($validScores) / count($validScores) : 0;
            } else {
                $trends['scores'][$date] = 0;
            }
        }

        return $trends;
    }

    /**
     * @param SupervisionInspection[] $inspections
     * @param array<string, array<string, mixed>> $trends
     * @return array<string, mixed>
     */
    public function calculateTrendsSummary(array $inspections, array $trends): array
    {
        $scores = $trends['scores'];
        assert(is_array($scores));
        $validScores = array_filter($scores, static fn ($value): bool => is_numeric($value) && $value > 0);

        $problems = $trends['problems'];
        assert(is_array($problems));
        $totalProblems = array_sum(array_filter($problems, 'is_numeric'));

        return [
            'totalInspections' => count($inspections),
            'totalProblems' => $totalProblems,
            'averageScore' => [] !== $validScores ? array_sum($validScores) / count($validScores) : 0,
        ];
    }
}
