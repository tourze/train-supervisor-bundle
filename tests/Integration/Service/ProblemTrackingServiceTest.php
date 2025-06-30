<?php

namespace Tourze\TrainSupervisorBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Service\ProblemTrackingService;

class ProblemTrackingServiceTest extends TestCase
{
    public function testServiceExists(): void
    {
        $this->assertTrue(class_exists(ProblemTrackingService::class));
    }
}