<?php

namespace Tourze\TrainSupervisorBundle\Service\AnomalyDetector;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\TrainSupervisorBundle\Service\ProblemTrackingService;

#[Autoconfigure(tags: ['train_supervisor.anomaly_detector'])]
class ProblemAnomalyDetector implements AnomalyDetectorInterface
{
    use SeverityCalculatorTrait;

    public function __construct(
        private readonly ProblemTrackingService $problemTrackingService,
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

        // 检测逾期问题
        $overdueProblems = $this->problemTrackingService->getOverdueProblems();

        foreach ($overdueProblems as $problem) {
            $overdueDays = abs($problem->getRemainingDays());

            if ($overdueDays > $thresholds['problem_overdue_days']) {
                assert(is_numeric($thresholds['problem_overdue_days']));
                $threshold = (float) $thresholds['problem_overdue_days'];
                $anomalies[] = [
                    'type' => 'problem_overdue',
                    'severity' => $this->calculateSeverity($overdueDays, $threshold),
                    'supplier_name' => '问题跟踪',
                    'date' => $problem->getFoundDate()->format('Y-m-d'),
                    'value' => $overdueDays,
                    'threshold' => $threshold,
                    'description' => sprintf('问题逾期异常：%d天 (阈值: %d天)', $overdueDays, (int) $threshold),
                    'details' => [
                        'problem_id' => $problem->getId(),
                        'problem_title' => $problem->getProblemTitle(),
                        'responsible_person' => $problem->getResponsiblePerson(),
                        'deadline' => $problem->getDeadline()->format('Y-m-d'),
                    ],
                ];
            }
        }

        return $anomalies;
    }

    public function getType(): string
    {
        return 'problem';
    }
}
