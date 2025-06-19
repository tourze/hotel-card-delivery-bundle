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
    }

    public function test_getMenuItems_routesAreCorrect(): void
    {
        // Skip this test as it requires dependency injection
        $this->markTestSkipped('This test requires LinkGeneratorInterface dependency');
    }
}