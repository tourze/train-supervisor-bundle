<?php

namespace Tourze\TrainSupervisorBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainSupervisorBundle\Exception\UnsupportedFormatException;

/**
 * @internal
 */
#[CoversClass(UnsupportedFormatException::class)]
final class UnsupportedFormatExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new UnsupportedFormatException('Test message');
        $this->assertInstanceOf(UnsupportedFormatException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }
}
