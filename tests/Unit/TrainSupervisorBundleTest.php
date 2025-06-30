<?php

namespace Tourze\TrainSupervisorBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\TrainSupervisorBundle\TrainSupervisorBundle;

class TrainSupervisorBundleTest extends TestCase
{
    public function testItIsASymfonyBundle(): void
    {
        $bundle = new TrainSupervisorBundle();
        $this->assertInstanceOf(Bundle::class, $bundle);
    }
}