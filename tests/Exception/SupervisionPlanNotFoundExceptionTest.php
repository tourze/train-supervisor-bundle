<?php

namespace Tourze\TrainSupervisorBundle\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Exception\SupervisionPlanNotFoundException;

class SupervisionPlanNotFoundExceptionTest extends TestCase
{
    public function testExceptionExists(): void
    {
        $this->assertTrue(class_exists(SupervisionPlanNotFoundException::class));
    }
}