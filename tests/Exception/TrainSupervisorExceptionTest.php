<?php

namespace Tourze\TrainSupervisorBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainSupervisorBundle\Exception\TrainSupervisorException;

/**
 * @internal
 */
#[CoversClass(TrainSupervisorException::class)]
final class TrainSupervisorExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new class('Test message') extends TrainSupervisorException {
        };
        $this->assertInstanceOf(TrainSupervisorException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }

    public function testExceptionExtendsRuntimeException(): void
    {
        $exception = new class('Test message') extends TrainSupervisorException {
        };
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testExceptionCanBeCreatedWithCode(): void
    {
        $exception = new class('Test message', 500) extends TrainSupervisorException {
        };
        $this->assertEquals(500, $exception->getCode());
    }

    public function testExceptionCanBeCreatedWithPreviousException(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new class('Test message', 0, $previous) extends TrainSupervisorException {
        };
        $this->assertSame($previous, $exception->getPrevious());
    }
}
