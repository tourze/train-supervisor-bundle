<?php

namespace Tourze\TrainSupervisorBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Service\LearningStatisticsService;

class LearningStatisticsServiceTest extends TestCase
{
    public function testServiceExists(): void
    {
        $this->assertTrue(class_exists(LearningStatisticsService::class));
    }
}