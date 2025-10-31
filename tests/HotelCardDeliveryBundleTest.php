<?php

declare(strict_types=1);

namespace Tourze\HotelCardDeliveryBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\HotelCardDeliveryBundle\HotelCardDeliveryBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(HotelCardDeliveryBundle::class)]
#[RunTestsInSeparateProcesses]
final class HotelCardDeliveryBundleTest extends AbstractBundleTestCase
{
}
