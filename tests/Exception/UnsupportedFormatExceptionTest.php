<?php

namespace Tourze\TrainSupervisorBundle\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Exception\UnsupportedFormatException;

class UnsupportedFormatExceptionTest extends TestCase
{
    public function testExceptionExists(): void
    {
        $this->assertTrue(class_exists(UnsupportedFormatException::class));
    }
}