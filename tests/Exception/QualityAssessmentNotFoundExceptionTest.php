<?php

namespace Tourze\TrainSupervisorBundle\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Exception\QualityAssessmentNotFoundException;

class QualityAssessmentNotFoundExceptionTest extends TestCase
{
    public function testExceptionExists(): void
    {
        $this->assertTrue(class_exists(QualityAssessmentNotFoundException::class));
    }
}