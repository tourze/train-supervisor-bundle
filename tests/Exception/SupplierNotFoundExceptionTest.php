<?php

namespace Tourze\TrainSupervisorBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainSupervisorBundle\Exception\SupplierNotFoundException;
use Tourze\TrainSupervisorBundle\Exception\TrainSupervisorException;

/**
 * @internal
 */
#[CoversClass(SupplierNotFoundException::class)]
class SupplierNotFoundExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeCreated(): void
    {
        $exception = new SupplierNotFoundException('123');
        $this->assertInstanceOf(SupplierNotFoundException::class, $exception);
        $this->assertSame('Supplier with ID 123 not found', $exception->getMessage());
    }

    public function testExceptionExtendsTrainSupervisorException(): void
    {
        $exception = new SupplierNotFoundException('456');
        $this->assertInstanceOf(TrainSupervisorException::class, $exception);
    }
}
