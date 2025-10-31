<?php

namespace Tourze\HotelCardDeliveryBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\HotelCardDeliveryBundle\Service\AdminMenu;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    protected function onSetUp(): void
    {
        // AdminMenu 测试不需要特殊的设置
    }

    public function testServiceCreation(): void
    {
        // 测试服务创建
        $adminMenu = self::getService(AdminMenu::class);
        $this->assertInstanceOf(AdminMenu::class, $adminMenu);
    }

    public function testImplementsMenuProviderInterface(): void
    {
        // 测试实现接口
        $adminMenu = self::getService(AdminMenu::class);
        $this->assertInstanceOf(MenuProviderInterface::class, $adminMenu);
    }

    public function testInvokeShouldBeCallable(): void
    {
        // AdminMenu实现了__invoke方法，所以是可调用的
        $adminMenu = self::getService(AdminMenu::class);
        $reflection = new \ReflectionClass($adminMenu);
        $this->assertTrue($reflection->hasMethod('__invoke'));
    }

    public function testInvoke(): void
    {
        // 测试 __invoke 方法的基本功能
        $adminMenu = self::getService(AdminMenu::class);
        $item = $this->createMock(ItemInterface::class);
        $childItem = $this->createMock(ItemInterface::class);
        $subMenuItem1 = $this->createMock(ItemInterface::class);
        $subMenuItem2 = $this->createMock(ItemInterface::class);

        // 设置期望：getChild 方法会被调用两次
        $item->expects($this->exactly(2))
            ->method('getChild')
            ->with('房卡配送')
            ->willReturnOnConsecutiveCalls(null, $childItem)
        ;

        // 设置期望：addChild 方法会被调用一次
        $item->expects($this->once())
            ->method('addChild')
            ->with('房卡配送')
            ->willReturn($childItem)
        ;

        // 设置期望：子菜单的 addChild 方法会被调用两次
        $childItem->expects($this->exactly(2))
            ->method('addChild')
            ->willReturnOnConsecutiveCalls($subMenuItem1, $subMenuItem2)
        ;

        // 设置期望：子菜单项的 setUri 方法会被调用两次，使用实际的EasyAdmin URL格式
        $subMenuItem1->expects($this->once())
            ->method('setUri')
            ->with(self::stringContains('crudAction=index&crudControllerFqcn=Tourze%5CHotelCardDeliveryBundle%5CEntity%5CKeyCardDelivery'))
            ->willReturn($subMenuItem1)
        ;

        $subMenuItem2->expects($this->once())
            ->method('setUri')
            ->with(self::stringContains('crudAction=index&crudControllerFqcn=Tourze%5CHotelCardDeliveryBundle%5CEntity%5CDeliveryCost'))
            ->willReturn($subMenuItem2)
        ;

        // 设置期望：子菜单项的 setAttribute 方法会被调用两次
        $subMenuItem1->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-truck')
            ->willReturn($subMenuItem1)
        ;

        $subMenuItem2->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-calculator')
            ->willReturn($subMenuItem2)
        ;

        // 不会抛出异常
        $adminMenu($item);
    }
}
