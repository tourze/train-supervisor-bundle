<?php

namespace Tourze\TrainSupervisorBundle\Tests\Integration\Controller\LearningStatistics;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Controller\LearningStatistics\ReportsController;

class ReportsControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists(ReportsController::class));
    }
}