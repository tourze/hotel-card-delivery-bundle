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

    public function test_extends_abstractCrudController(): void
    {
        $reflection = new \ReflectionClass(KeyCardDeliveryCrudController::class);
        $this->assertTrue($reflection->isSubclassOf(AbstractCrudController::class));
    }
}