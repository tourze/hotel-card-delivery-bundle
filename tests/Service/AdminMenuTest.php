<?php

namespace Tourze\HotelCardDeliveryBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\HotelCardDeliveryBundle\Entity\DeliveryCost;
use Tourze\HotelCardDeliveryBundle\Entity\KeyCardDelivery;
use Tourze\HotelCardDeliveryBundle\Service\AdminMenu;

class AdminMenuTest extends TestCase
{
    public function test_implements_menuProviderInterface(): void
    {
        $reflection = new \ReflectionClass(AdminMenu::class);
        $this->assertTrue($reflection->implementsInterface(MenuProviderInterface::class));
    }

    public function test_canInstantiateWithDependency(): void
    {
        $linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $adminMenu = new AdminMenu($linkGenerator);
        
        $this->assertInstanceOf(AdminMenu::class, $adminMenu);
    }

    public function test_canBeInvokedWithMockDependency(): void
    {
        $linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $linkGenerator->method('getCurdListPage')->willReturn('/test-url');
        
        $adminMenu = new AdminMenu($linkGenerator);
        $rootItem = $this->createMock(ItemInterface::class);
        $deliveryMenuItem = $this->createMock(ItemInterface::class);
        $taskMenuItem = $this->createMock(ItemInterface::class);
        
        $rootItem->method('getChild')->willReturn(null, $deliveryMenuItem);
        $rootItem->method('addChild')->willReturn($deliveryMenuItem);
        $deliveryMenuItem->method('addChild')->willReturn($taskMenuItem);
        $taskMenuItem->method('setUri')->willReturn($taskMenuItem);
        $taskMenuItem->method('setAttribute')->willReturn($taskMenuItem);
        
        // 如果能成功调用而不抛出异常，测试就通过
        $adminMenu($rootItem);
        $this->assertTrue(true);
    }
}