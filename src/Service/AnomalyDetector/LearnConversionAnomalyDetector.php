<?php

namespace Tourze\TrainSupervisorBundle\Service\AnomalyDetector;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\TrainSupervisorBundle\Service\SupervisorService;

#[Autoconfigure(tags: ['train_supervisor.anomaly_detector'])]
class LearnConversionAnomalyDetector implements AnomalyDetectorInterface
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
            if ($record->getDailyLoginCount() > 0 && $record->getDailyLearnCount() > 0) {
                $conversionRate = ($record->getDailyLearnCount() / $record->getDailyLoginCount()) * 100;

                if ($conversionRate < $thresholds['learn_conversion_rate']) {
                    assert(is_numeric($thresholds['learn_conversion_rate']));
                    $threshold = (float) $thresholds['learn_conversion_rate'];
                    $anomalies[] = [
                        'type' => 'learn_conversion_rate',
                        'severity' => $this->calculateSeverity($threshold - $conversionRate, 10),
                        'supplier_name' => $record->getSupplier()?->getName(),
                        'date' => $record->getDate()?->format('Y-m-d'),
                        'value' => $conversionRate,
                        'threshold' => $threshold,
                        'description' => sprintf('学习转化率异常：%.2f%% (阈值: %.2f%%)', $conversionRate, $threshold),
                        'details' => [
                            'login_count' => $record->getDailyLoginCount(),
                            'learn_count' => $record->getDailyLearnCount(),
                        ],
                    ];
                }
            }
        }

        return $anomalies;
    }

    public function getType(): string
    {
        return 'learn';
    }
}
