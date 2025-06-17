<?php

namespace Tourze\HotelCardDeliveryBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\HotelCardDeliveryBundle\Entity\DeliveryCost;
use Tourze\HotelCardDeliveryBundle\Entity\KeyCardDelivery;

class DeliveryCostTest extends TestCase
{
    public function test_constructor_setsDefaultValues(): void
    {
        $deliveryCost = new DeliveryCost();

        $this->assertFalse($deliveryCost->isSettled());
        $this->assertEquals('0.00', $deliveryCost->getBaseCost());
        $this->assertEquals('0.00', $deliveryCost->getDistanceCost());
        $this->assertEquals('0.00', $deliveryCost->getUrgencyCost());
        $this->assertEquals('0.00', $deliveryCost->getExtraCost());
        $this->assertEquals('0.00', $deliveryCost->getTotalCost());
        $this->assertEquals(0.0, $deliveryCost->getDistance());
        $this->assertNull($deliveryCost->getSettlementTime());
    }

    public function test_settersAndGetters_workCorrectly(): void
    {
        $deliveryCost = new DeliveryCost();
        $delivery = new KeyCardDelivery();

        // Test delivery association
        $deliveryCost->setDelivery($delivery);
        $this->assertSame($delivery, $deliveryCost->getDelivery());

        // Test cost setters
        $deliveryCost->setBaseCost('10.50');
        $this->assertEquals('10.50', $deliveryCost->getBaseCost());

        $deliveryCost->setDistanceCost('5.25');
        $this->assertEquals('5.25', $deliveryCost->getDistanceCost());

        $deliveryCost->setUrgencyCost('3.00');
        $this->assertEquals('3.00', $deliveryCost->getUrgencyCost());

        $deliveryCost->setExtraCost('1.75');
        $this->assertEquals('1.75', $deliveryCost->getExtraCost());
        
        // Test distance setter
        $deliveryCost->setDistance(15.5);
        $this->assertEquals(15.5, $deliveryCost->getDistance());

        // Test settled status
        $deliveryCost->setSettled(true);
        $this->assertTrue($deliveryCost->isSettled());

        // Test remarks
        $remarks = 'Additional delivery notes';
        $deliveryCost->setRemarks($remarks);
        $this->assertEquals($remarks, $deliveryCost->getRemarks());
    }

    public function test_setters_automaticallyCalculateTotalCost(): void
    {
        $deliveryCost = new DeliveryCost();
        
        $deliveryCost->setBaseCost('10.00');
        $deliveryCost->setDistanceCost('5.00');
        $deliveryCost->setUrgencyCost('3.00');
        $deliveryCost->setExtraCost('2.00');

        // Total should be automatically calculated when setting costs
        $this->assertEquals('20', $deliveryCost->getTotalCost());
    }

    public function test_calculateDistanceCost_returnsCorrectValue(): void
    {
        $deliveryCost = new DeliveryCost();
        $delivery = new KeyCardDelivery();
        $deliveryCost->setDelivery($delivery);
        
        $deliveryCost->setDistance(10.0);
        
        // Test with default rate (2.0)
        $deliveryCost->calculateDistanceCost();
        $this->assertEquals('20', $deliveryCost->getDistanceCost());
        
        // Test with custom rate
        $deliveryCost->calculateDistanceCost(3.0);
        $this->assertEquals('30', $deliveryCost->getDistanceCost());
    }

    public function test_markAsSettled_setsSettlementTime(): void
    {
        $deliveryCost = new DeliveryCost();
        
        // Initially not settled
        $this->assertFalse($deliveryCost->isSettled());
        $this->assertNull($deliveryCost->getSettlementTime());
        
        // Mark as settled
        $deliveryCost->markAsSettled();
        
        // Should be settled with settlement time
        $this->assertTrue($deliveryCost->isSettled());
        $this->assertNotNull($deliveryCost->getSettlementTime());
        $this->assertInstanceOf(\DateTimeInterface::class, $deliveryCost->getSettlementTime());
    }

    public function test_toString_returnsExpectedFormat(): void
    {
        $deliveryCost = new DeliveryCost();
        $deliveryCost->setBaseCost('10.00');
        $deliveryCost->setDistanceCost('15.50');

        // Test with associated delivery
        $delivery = new KeyCardDelivery();
        $deliveryCost->setDelivery($delivery);
        
        $expectedString = '配送费用 #0 - New';
        $this->assertEquals($expectedString, (string) $deliveryCost);
    }
}