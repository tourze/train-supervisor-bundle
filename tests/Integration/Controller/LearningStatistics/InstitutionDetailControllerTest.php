<?php

namespace Tourze\TrainSupervisorBundle\Tests\Integration\Controller\LearningStatistics;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Controller\LearningStatistics\InstitutionDetailController;

class InstitutionDetailControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists(InstitutionDetailController::class));
    }
}