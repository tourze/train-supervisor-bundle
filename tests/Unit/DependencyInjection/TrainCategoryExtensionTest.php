<?php

namespace Tourze\TrainSupervisorBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\DependencyInjection\TrainCategoryExtension;

class TrainCategoryExtensionTest extends TestCase
{
    public function testDependencyInjectionExists(): void
    {
        $this->assertTrue(class_exists(TrainCategoryExtension::class));
    }
}