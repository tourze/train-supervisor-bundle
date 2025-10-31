<?php

namespace Tourze\TrainSupervisorBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainSupervisorBundle\Exception\InvalidProblemStatusException;

/**
 * @internal
 */
#[CoversClass(InvalidProblemStatusException::class)]
final class InvalidProblemStatusExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new InvalidProblemStatusException('Test message');
        $this->assertInstanceOf(InvalidProblemStatusException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }
}
