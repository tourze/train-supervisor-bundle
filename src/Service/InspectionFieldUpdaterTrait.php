<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Service;

use Tourze\TrainSupervisorBundle\Entity\SupervisionInspection;

/**
 * 检查字段更新器
 * 负责处理检查对象的各种字段更新逻辑
 */
trait InspectionFieldUpdaterTrait
{
    /**
     * 更新数组类型的字段
     *
     * @param array<string, mixed> $results
     */
    private function updateArrayField(SupervisionInspection $inspection, array $results, string $fieldKey, string $setterMethod): void
    {
        if (!isset($results[$fieldKey])) {
            return;
        }

        $fieldValue = $results[$fieldKey];
        assert(is_array($fieldValue));

        // 确保数组索引为字符串类型
        $stringKeyedValue = [];
        foreach ($fieldValue as $key => $value) {
            $stringKeyedValue[(string) $key] = $value;
        }

        match ($setterMethod) {
            'setInspectionResults' => $inspection->setInspectionResults($stringKeyedValue),
            'setFoundProblems' => $inspection->setFoundProblems($stringKeyedValue),
            default => throw new \InvalidArgumentException("Unknown setter method: {$setterMethod}"),
        };
    }

    /**
     * 更新数值类型的字段
     *
     * @param array<string, mixed> $results
     */
    private function updateNumericField(SupervisionInspection $inspection, array $results, string $fieldKey, string $setterMethod): void
    {
        if (!isset($results[$fieldKey])) {
            return;
        }

        $fieldValue = $results[$fieldKey];
        assert(is_float($fieldValue) || is_int($fieldValue));

        match ($setterMethod) {
            'setOverallScore' => $inspection->setOverallScore($fieldValue),
            default => throw new \InvalidArgumentException("Unknown setter method: {$setterMethod}"),
        };
    }

    /**
     * 更新字符串类型的字段
     *
     * @param array<string, mixed> $results
     */
    private function updateStringField(SupervisionInspection $inspection, array $results, string $fieldKey, string $setterMethod): void
    {
        if (!isset($results[$fieldKey])) {
            return;
        }

        $fieldValue = $results[$fieldKey];
        assert(is_string($fieldValue));

        match ($setterMethod) {
            'setInspectionReport' => $inspection->setInspectionReport($fieldValue),
            'setInspectionStatus' => $inspection->setInspectionStatus($fieldValue),
            'setRemarks' => $inspection->setRemarks($fieldValue),
            default => throw new \InvalidArgumentException("Unknown setter method: {$setterMethod}"),
        };
    }
}
