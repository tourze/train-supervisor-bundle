<?php

namespace Tourze\TrainSupervisorBundle\Service\AnomalyDetector;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\TrainSupervisorBundle\Service\SupervisorService;

#[Autoconfigure(tags: ['train_supervisor.anomaly_detector'])]
class FaceDetectionAnomalyDetector implements AnomalyDetectorInterface
{
    use SeverityCalculatorTrait;

    public function __construct(
        private readonly SupervisorService $supervisorService,
    ) {
    }

    /**
     * @param array<string, mixed> $thresholds
     *
     * @return array<int, array<string, mixed>>
     */
    public function detect(\DateTime $startDate, \DateTime $endDate, array $thresholds): array
    {
        $anomalies = [];
        $supervisorData = $this->supervisorService->getSupervisorDataByDateRange($startDate, $endDate);

        foreach ($supervisorData as $record) {
            $totalFaceDetect = $record->getFaceDetectSuccessCount() + $record->getFaceDetectFailCount();

            if ($totalFaceDetect > 0) {
                $failRate = ($record->getFaceDetectFailCount() / $totalFaceDetect) * 100;

                if ($failRate > $thresholds['face_fail_rate']) {
                    assert(is_numeric($thresholds['face_fail_rate']));
                    $threshold = (float) $thresholds['face_fail_rate'];
                    $anomalies[] = [
                        'type' => 'face_fail_rate',
                        'severity' => $this->calculateSeverity($failRate, $threshold),
                        'supplier_name' => $record->getSupplier()?->getName(),
                        'date' => $record->getDate()?->format('Y-m-d'),
                        'value' => $failRate,
                        'threshold' => $threshold,
                        'description' => sprintf('人脸识别失败率异常：%.2f%% (阈值: %.2f%%)', $failRate, $threshold),
                        'details' => [
                            'success_count' => $record->getFaceDetectSuccessCount(),
                            'fail_count' => $record->getFaceDetectFailCount(),
                        ],
                    ];
                }
            }
        }

        return $anomalies;
    }

    public function getType(): string
    {
        return 'face';
    }
}
