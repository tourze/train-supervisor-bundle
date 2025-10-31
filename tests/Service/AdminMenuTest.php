<?php

declare(strict_types=1);

namespace Tourze\TrainSupervisorBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use Tourze\TrainSupervisorBundle\Service\AdminMenu;

/**
 * AdminMenu 单元测试
 *
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    protected function onSetUp(): void
    {
        // 初始化测试环境
    }

    public function testInvokeMethod(): void
    {
        // 创建模拟的依赖
        $linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $linkGenerator
            ->expects($this->atLeastOnce())
            ->method('getCurdListPage')
            ->willReturn('/admin/test-url')
        ;

        $item = $this->createMock(ItemInterface::class);
        $childItem = $this->createMock(ItemInterface::class);

        // 设置 mock 的返回值
        $item
            ->expects($this->once())
            ->method('addChild')
            ->with('培训监管')
            ->willReturn($childItem)
        ;
        $item
            ->expects($this->exactly(2))
            ->method('getChild')
            ->willReturnOnConsecutiveCalls(null, $childItem)
        ;

        // 设置子菜单项的 mock 行为
        $childItem
            ->expects($this->atLeastOnce())
            ->method('addChild')
            ->willReturn($childItem)
        ;
        $childItem
            ->expects($this->atLeastOnce())
            ->method('setUri')
            ->willReturn($childItem)
        ;
        $childItem
            ->expects($this->atLeastOnce())
            ->method('setAttribute')
            ->willReturn($childItem)
        ;

        // 创建 AdminMenu 实例并测试
        self::getContainer()->set(LinkGeneratorInterface::class, $linkGenerator);
        $adminMenu = self::getService(AdminMenu::class);

        // 直接调用，不应该抛出异常
        ($adminMenu)($item);
    }
}
