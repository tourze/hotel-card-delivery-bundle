<?php

namespace Tourze\HotelCardDeliveryBundle\Tests\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use PHPUnit\Framework\TestCase;
use Tourze\HotelCardDeliveryBundle\DataFixtures\DeliveryCostFixtures;
use Tourze\HotelCardDeliveryBundle\DataFixtures\KeyCardDeliveryFixtures;

class DeliveryCostFixturesTest extends TestCase
{
    public function test_implements_fixtureInterface(): void
    {
        $fixtures = new DeliveryCostFixtures();
        $this->assertInstanceOf(FixtureInterface::class, $fixtures);
    }

    public function test_implements_dependentFixtureInterface(): void
    {
        $fixtures = new DeliveryCostFixtures();
        $this->assertInstanceOf(DependentFixtureInterface::class, $fixtures);
    }

    public function test_getDependencies_returnsCorrectDependencies(): void
    {
        $fixtures = new DeliveryCostFixtures();
        $dependencies = $fixtures->getDependencies();

        $this->assertIsArray($dependencies);
        $this->assertContains(KeyCardDeliveryFixtures::class, $dependencies);
    }

    public function test_load_methodExists(): void
    {
        $fixtures = new DeliveryCostFixtures();
        $this->assertTrue(method_exists($fixtures, 'load'));
    }

    public function test_fixtureHasExpectedStructure(): void
    {
        $fixtures = new DeliveryCostFixtures();
        
        // Test that the fixture class has expected properties/methods
        $reflection = new \ReflectionClass($fixtures);
        
        // Check for load method
        $this->assertTrue($reflection->hasMethod('load'));
        
        // Check for getDependencies method
        $this->assertTrue($reflection->hasMethod('getDependencies'));
    }
}