<?php

namespace Tourze\TrainSupervisorBundle\Tests\Integration\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Repository\QualityAssessmentRepository;

class QualityAssessmentRepositoryTest extends TestCase
{
    public function testRepositoryExists(): void
    {
        $this->assertTrue(class_exists(QualityAssessmentRepository::class));
    }
}