<?php

namespace Tourze\TrainSupervisorBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainSupervisorBundle\Exception\SupervisionPlanNotFoundException;

/**
 * @internal
 */
#[CoversClass(SupervisionPlanNotFoundException::class)]
final class SupervisionPlanNotFoundExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new SupervisionPlanNotFoundException('Test message');
        $this->assertInstanceOf(SupervisionPlanNotFoundException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }
}
