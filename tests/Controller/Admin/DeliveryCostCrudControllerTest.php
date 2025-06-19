<?php

namespace Tourze\HotelCardDeliveryBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\TestCase;
use Tourze\HotelCardDeliveryBundle\Controller\Admin\DeliveryCostCrudController;
use Tourze\HotelCardDeliveryBundle\Entity\DeliveryCost;

class DeliveryCostCrudControllerTest extends TestCase
{
    public function test_getEntityFqcn_returnsCorrectClass(): void
    {
        $this->assertEquals(DeliveryCost::class, DeliveryCostCrudController::getEntityFqcn());
    }

    public function test_extends_abstractCrudController(): void
    {
        $reflection = new \ReflectionClass(DeliveryCostCrudController::class);
        $this->assertTrue($reflection->isSubclassOf(AbstractCrudController::class));
    }
}