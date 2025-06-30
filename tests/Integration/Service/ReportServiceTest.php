<?php

namespace Tourze\TrainSupervisorBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Service\ReportService;

class ReportServiceTest extends TestCase
{
    public function testServiceExists(): void
    {
        $this->assertTrue(class_exists(ReportService::class));
    }
}