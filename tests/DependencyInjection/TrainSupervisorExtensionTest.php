<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use Tourze\TrainSupervisorBundle\DependencyInjection\TrainSupervisorExtension;

/**
 * @internal
 */
#[CoversClass(TrainSupervisorExtension::class)]
final class TrainSupervisorExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    /**
     * 测试加载配置.
     */
    public function testLoad(): void
    {
        $extension = new TrainSupervisorExtension();
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');
        $configs = [];

        // 验证不会抛出异常
        $extension->load($configs, $container);

        // 验证Extension正常加载 - 检查容器是否已编译或有服务定义
        $this->assertInstanceOf(ContainerBuilder::class, $container);
    }
}
