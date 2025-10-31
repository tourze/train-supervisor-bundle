<?php

namespace Tourze\TrainSupervisorBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Tourze\TrainSupervisorBundle\Service\AnomalyDetector\AnomalyDetectorInterface;

#[Autoconfigure(public: true)]
class AnomalyDetectionService
{
    /**
     * @param iterable<AnomalyDetectorInterface> $detectors
     */
    public function __construct(
        #[AutowireIterator(tag: 'train_supervisor.anomaly_detector')]
        private readonly iterable $detectors,
    ) {
    }

    /**
     * @param array<string, mixed> $thresholds
     *
     * @return array<int, array<string, mixed>>
     */
    public function detectAnomalies(\DateTime $startDate, \DateTime $endDate, string $type, array $thresholds): array
    {
        $allAnomalies = [];

        foreach ($this->detectors as $detector) {
            if ($this->shouldRunDetector($detector, $type)) {
                $anomalies = $detector->detect($startDate, $endDate, $thresholds);
                $allAnomalies = array_merge($allAnomalies, $anomalies);
            }
        }

        return $allAnomalies;
    }

    private function shouldRunDetector(AnomalyDetectorInterface $detector, string $type): bool
    {
        return 'all' === $type || $detector->getType() === $type;
    }
}
