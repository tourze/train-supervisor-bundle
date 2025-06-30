<?php

namespace Tourze\TrainSupervisorBundle\Tests\Integration\Controller\LearningStatistics;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Controller\LearningStatistics\IndexController;

class IndexControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists(IndexController::class));
    }
}