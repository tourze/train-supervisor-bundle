<?php

namespace Tourze\TrainSupervisorBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Service\SupervisorService;

class SupervisorServiceTest extends TestCase
{
    public function testServiceExists(): void
    {
        $this->assertTrue(class_exists(SupervisorService::class));
    }
}