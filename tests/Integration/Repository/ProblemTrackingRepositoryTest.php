<?php

namespace Tourze\TrainSupervisorBundle\Tests\Integration\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Repository\ProblemTrackingRepository;

class ProblemTrackingRepositoryTest extends TestCase
{
    public function testRepositoryExists(): void
    {
        $this->assertTrue(class_exists(ProblemTrackingRepository::class));
    }
}