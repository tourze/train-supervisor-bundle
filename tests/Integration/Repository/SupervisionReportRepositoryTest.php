<?php

namespace Tourze\TrainSupervisorBundle\Tests\Integration\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Repository\SupervisionReportRepository;

class SupervisionReportRepositoryTest extends TestCase
{
    public function testRepositoryExists(): void
    {
        $this->assertTrue(class_exists(SupervisionReportRepository::class));
    }
}