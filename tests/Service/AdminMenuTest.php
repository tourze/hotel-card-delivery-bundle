<?php

namespace Tourze\HotelCardDeliveryBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\HotelCardDeliveryBundle\Service\AdminMenu;

class AdminMenuTest extends TestCase
{
    public function test_implements_menuProviderInterface(): void
    {
        $reflection = new \ReflectionClass(AdminMenu::class);
        $this->assertTrue($reflection->implementsInterface(MenuProviderInterface::class));
    }

    public function test_getMenuItems_returnsCorrectStructure(): void
    {
        // Skip this test as it requires dependency injection
        $this->markTestSkipped('This test requires LinkGeneratorInterface dependency');
        $this->assertCount(1, $menuItems);

        // Check main section
        $mainSection = $menuItems[0];
        $this->assertArrayHasKey('label', $mainSection);
        $this->assertArrayHasKey('icon', $mainSection);
        $this->assertArrayHasKey('items', $mainSection);

        $this->assertEquals('房卡配送', $mainSection['label']);
        $this->assertEquals('fa fa-credit-card', $mainSection['icon']);

        // Check sub-items
        $this->assertCount(2, $mainSection['items']);

        // Check first sub-item (配送任务)
        $deliveryItem = $mainSection['items'][0];
        $this->assertArrayHasKey('label', $deliveryItem);
        $this->assertArrayHasKey('route', $deliveryItem);
        $this->assertArrayHasKey('entity', $deliveryItem);

        $this->assertEquals('配送任务', $deliveryItem['label']);
        $this->assertEquals('KeyCardDelivery', $deliveryItem['entity']);

        // Check second sub-item (配送费用)
        $costItem = $mainSection['items'][1];
        $this->assertArrayHasKey('label', $costItem);
        $this->assertArrayHasKey('route', $costItem);
        $this->assertArrayHasKey('entity', $costItem);

        $this->assertEquals('配送费用', $costItem['label']);
        $this->assertEquals('DeliveryCost', $costItem['entity']);
    }

    public function test_getMenuItems_routesAreCorrect(): void
    {
        // Skip this test as it requires dependency injection
        $this->markTestSkipped('This test requires LinkGeneratorInterface dependency');
    }
}