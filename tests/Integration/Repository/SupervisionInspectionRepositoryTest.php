<?php

namespace Tourze\TrainSupervisorBundle\Tests\Integration\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Repository\SupervisionInspectionRepository;

class SupervisionInspectionRepositoryTest extends TestCase
{
    public function testRepositoryExists(): void
    {
        $this->assertTrue(class_exists(SupervisionInspectionRepository::class));
    }
}