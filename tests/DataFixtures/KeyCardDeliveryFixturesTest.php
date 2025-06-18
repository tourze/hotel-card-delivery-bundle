<?php

namespace Tourze\HotelCardDeliveryBundle\Tests\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use PHPUnit\Framework\TestCase;
use Tourze\HotelAgentBundle\DataFixtures\OrderFixtures;
use Tourze\HotelCardDeliveryBundle\DataFixtures\KeyCardDeliveryFixtures;

class KeyCardDeliveryFixturesTest extends TestCase
{
    public function test_implements_fixtureInterface(): void
    {
        $fixtures = new KeyCardDeliveryFixtures();
        $this->assertInstanceOf(FixtureInterface::class, $fixtures);
    }

    public function test_implements_dependentFixtureInterface(): void
    {
        $fixtures = new KeyCardDeliveryFixtures();
        $this->assertInstanceOf(DependentFixtureInterface::class, $fixtures);
    }

    public function test_getDependencies_returnsCorrectDependencies(): void
    {
        $fixtures = new KeyCardDeliveryFixtures();
        $dependencies = $fixtures->getDependencies();
        $this->assertContains(OrderFixtures::class, $dependencies);
    }

    public function test_load_methodExists(): void
    {
        $fixtures = new KeyCardDeliveryFixtures();
        $this->assertTrue(method_exists($fixtures, 'load'));
    }

    public function test_fixtureHasExpectedStructure(): void
    {
        $fixtures = new KeyCardDeliveryFixtures();
        
        // Test that the fixture class has expected properties/methods
        $reflection = new \ReflectionClass($fixtures);
        
        // Check for load method
        $this->assertTrue($reflection->hasMethod('load'));
        
        // Check for getDependencies method
        $this->assertTrue($reflection->hasMethod('getDependencies'));
        
        // Check for reference constants
        $this->assertTrue($reflection->hasConstant('PENDING_DELIVERY_REFERENCE'));
        $this->assertTrue($reflection->hasConstant('IN_PROGRESS_DELIVERY_REFERENCE'));
        $this->assertTrue($reflection->hasConstant('COMPLETED_DELIVERY_REFERENCE'));
    }
}