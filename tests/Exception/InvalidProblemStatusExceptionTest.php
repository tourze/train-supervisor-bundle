<?php

namespace Tourze\TrainSupervisorBundle\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Exception\InvalidProblemStatusException;

class InvalidProblemStatusExceptionTest extends TestCase
{
    public function testExceptionExists(): void
    {
        $this->assertTrue(class_exists(InvalidProblemStatusException::class));
    }
}