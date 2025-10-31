<?php

namespace Tourze\TrainSupervisorBundle\Service\AnomalyDetector;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\TrainSupervisorBundle\Service\SupervisorService;

#[Autoconfigure(tags: ['train_supervisor.anomaly_detector'])]
class CheatRateAnomalyDetector implements AnomalyDetectorInterface
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
            if ($record->getDailyLearnCount() > 0) {
                $cheatRate = ($record->getDailyCheatCount() / $record->getDailyLearnCount()) * 100;

                if ($cheatRate > $thresholds['cheat_rate']) {
                    assert(is_numeric($thresholds['cheat_rate']));
                    $threshold = (float) $thresholds['cheat_rate'];
                    $anomalies[] = [
                        'type' => 'cheat_rate',
                        'severity' => $this->calculateSeverity($cheatRate, $threshold),
                        'supplier_name' => $record->getSupplier()?->getName(),
                        'date' => $record->getDate()?->format('Y-m-d'),
                        'value' => $cheatRate,
                        'threshold' => $threshold,
                        'description' => sprintf('作弊率异常：%.2f%% (阈值: %.2f%%)', $cheatRate, $threshold),
                        'details' => [
                            'learn_count' => $record->getDailyLearnCount(),
                            'cheat_count' => $record->getDailyCheatCount(),
                        ],
                    ];
                }
            }
        }

        return $anomalies;
    }

    public function getType(): string
    {
        return 'cheat';
    }
}
