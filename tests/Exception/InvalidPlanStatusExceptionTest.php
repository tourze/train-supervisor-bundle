<?php

namespace Tourze\TrainSupervisorBundle\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Exception\InvalidPlanStatusException;

class InvalidPlanStatusExceptionTest extends TestCase
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