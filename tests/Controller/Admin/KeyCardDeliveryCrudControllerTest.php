<?php

namespace Tourze\HotelCardDeliveryBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\TestCase;
use Tourze\HotelCardDeliveryBundle\Controller\Admin\KeyCardDeliveryCrudController;
use Tourze\HotelCardDeliveryBundle\Entity\KeyCardDelivery;

class KeyCardDeliveryCrudControllerTest extends TestCase
{
    public function test_getEntityFqcn_returnsCorrectClass(): void
    {
        $this->assertEquals(KeyCardDelivery::class, KeyCardDeliveryCrudController::getEntityFqcn());
    }

    public function test_requiredMethods_exist(): void
    {
        $controllerClass = KeyCardDeliveryCrudController::class;
        
        $this->assertTrue(method_exists($controllerClass, 'configureCrud'));
        $this->assertTrue(method_exists($controllerClass, 'configureFields'));
        $this->assertTrue(method_exists($controllerClass, 'configureFilters'));
        $this->assertTrue(method_exists($controllerClass, 'configureActions'));
    }

    public function test_extends_abstractCrudController(): void
    {
        $reflection = new \ReflectionClass(KeyCardDeliveryCrudController::class);
        $this->assertTrue($reflection->isSubclassOf(AbstractCrudController::class));
    }
}