<?php

namespace Tourze\TrainSupervisorBundle\Service\AnomalyDetector;

trait SeverityCalculatorTrait
{
    private function calculateSeverity(float $value, float $threshold): string
    {
        $ratio = $value / $threshold;

        if ($ratio >= 3.0) {
            return '严重';
        }
        if ($ratio >= 2.0) {
            return '重要';
        }
        if ($ratio >= 1.5) {
            return '一般';
        }

        return '轻微';
    }
}
