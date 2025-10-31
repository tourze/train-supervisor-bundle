<?php

namespace Tourze\TrainSupervisorBundle\Service\AnomalyDetector;

interface AnomalyDetectorInterface
{
    /**
     * @param array<string, mixed> $thresholds
     *
     * @return array<int, array<string, mixed>>
     */
    public function detect(\DateTime $startDate, \DateTime $endDate, array $thresholds): array;

    public function getType(): string;
}
