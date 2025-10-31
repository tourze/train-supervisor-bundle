<?php

namespace Tourze\TrainSupervisorBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainSupervisorBundle\Exception\InvalidPlanStatusException;

/**
 * @internal
 */
#[CoversClass(InvalidPlanStatusException::class)]
final class InvalidPlanStatusExceptionTest extends AbstractExceptionTestCase
{
    public function testItExtendsRuntimeException(): void
    {
        $exception = new InvalidPlanStatusException();

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testItCanBeCreatedWithMessage(): void
    {
        $message = 'Invalid plan status: invalid_status';
        $exception = new InvalidPlanStatusException($message);

        $this->assertSame($message, $exception->getMessage());
    }
}
