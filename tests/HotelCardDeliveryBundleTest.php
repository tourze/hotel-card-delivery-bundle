<?php

namespace Tourze\HotelCardDeliveryBundle\Tests;

use PHPUnit\Framework\TestCase;
use Tourze\HotelCardDeliveryBundle\HotelCardDeliveryBundle;

class HotelCardDeliveryBundleTest extends TestCase
{
    public function test_getPath_returnsCorrectPath(): void
    {
        $bundle = new HotelCardDeliveryBundle();
        $expectedPath = dirname(__DIR__) . '/src';

        $this->assertEquals($expectedPath, $bundle->getPath());
    }
}