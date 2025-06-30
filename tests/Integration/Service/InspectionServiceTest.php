<?php

namespace Tourze\TrainSupervisorBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Service\InspectionService;

class InspectionServiceTest extends TestCase
{
    public function testServiceExists(): void
    {
        $this->assertTrue(class_exists(InspectionService::class));
    }
}