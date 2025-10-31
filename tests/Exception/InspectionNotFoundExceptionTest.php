<?php

namespace Tourze\TrainSupervisorBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainSupervisorBundle\Exception\InspectionNotFoundException;

/**
 * @internal
 */
#[CoversClass(InspectionNotFoundException::class)]
final class InspectionNotFoundExceptionTest extends AbstractExceptionTestCase
{
    public function testItExtendsInvalidArgumentException(): void
    {
        $exception = new InspectionNotFoundException();

        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
    }

    public function testItCanBeCreatedWithMessage(): void
    {
        $message = 'Inspection with ID 123 not found';
        $exception = new InspectionNotFoundException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testItCanBeCreatedWithMessageAndCode(): void
    {
        $message = 'Inspection not found';
        $code = 404;
        $exception = new InspectionNotFoundException($message, $code);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
    }

    public function testItCanBeCreatedWithPreviousException(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new InspectionNotFoundException('Inspection not found', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
