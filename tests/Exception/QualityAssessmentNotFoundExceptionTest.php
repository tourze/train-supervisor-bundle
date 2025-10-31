<?php

namespace Tourze\TrainSupervisorBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainSupervisorBundle\Exception\QualityAssessmentNotFoundException;

/**
 * @internal
 */
#[CoversClass(QualityAssessmentNotFoundException::class)]
final class QualityAssessmentNotFoundExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new QualityAssessmentNotFoundException('Test message');
        $this->assertInstanceOf(QualityAssessmentNotFoundException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }
}
