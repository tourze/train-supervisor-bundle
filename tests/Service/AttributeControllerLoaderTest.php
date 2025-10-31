<?php

namespace Tourze\TrainSupervisorBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Routing\RouteCollection;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainSupervisorBundle\Service\AttributeControllerLoader;

/**
 * @internal
 */
#[CoversClass(AttributeControllerLoader::class)]
#[RunTestsInSeparateProcesses]
final class AttributeControllerLoaderTest extends AbstractIntegrationTestCase
{
    private AttributeControllerLoader $attributeControllerLoader;

    protected function onSetUp(): void
    {
        $this->attributeControllerLoader = self::getService(AttributeControllerLoader::class);
    }

    public function testLoad(): void
    {
        $result = $this->attributeControllerLoader->load('resource');

        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    public function testSupports(): void
    {
        $result = $this->attributeControllerLoader->supports('resource');

        $this->assertIsBool($result);
        $this->assertFalse($result);
    }

    public function testAutoload(): void
    {
        $result = $this->attributeControllerLoader->autoload();

        $this->assertInstanceOf(RouteCollection::class, $result);
        $this->assertGreaterThan(0, $result->count());
    }
}
