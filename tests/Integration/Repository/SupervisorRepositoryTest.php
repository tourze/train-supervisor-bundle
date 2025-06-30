<?php

namespace Tourze\TrainSupervisorBundle\Tests\Integration\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Repository\SupervisorRepository;

class SupervisorRepositoryTest extends TestCase
{
    public function testRepositoryExists(): void
    {
        $this->assertTrue(class_exists(SupervisorRepository::class));
    }
}