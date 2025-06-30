<?php

namespace Tourze\TrainSupervisorBundle\Tests\Integration\Controller\LearningStatistics;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Controller\Admin\Statistics\TrendsController;

class TrendsControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists(TrendsController::class));
    }
}