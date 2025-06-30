<?php

namespace Tourze\TrainSupervisorBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use Tourze\TrainSupervisorBundle\Service\AttributeControllerLoader;

class AttributeControllerLoaderTest extends TestCase
{
    public function testServiceExists(): void
    {
        $this->assertTrue(class_exists(AttributeControllerLoader::class));
    }
}