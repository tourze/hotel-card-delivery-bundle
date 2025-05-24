<?php

namespace Tourze\HotelCardDeliveryBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;

class HotelCardDeliveryBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            \Tourze\HotelAgentBundle\HotelAgentBundle::class => ['all' => true],
        ];
    }
}
