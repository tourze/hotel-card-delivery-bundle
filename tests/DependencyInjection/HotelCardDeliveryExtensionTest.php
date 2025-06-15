<?php

namespace Tourze\HotelCardDeliveryBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\HotelCardDeliveryBundle\DependencyInjection\HotelCardDeliveryExtension;

class HotelCardDeliveryExtensionTest extends TestCase
{
    public function test_load_registersExpectedServices(): void
    {
        $container = new ContainerBuilder();
        $extension = new HotelCardDeliveryExtension();

        $extension->load([], $container);

        // Check that repositories are registered
        $this->assertTrue($container->hasDefinition('Tourze\HotelCardDeliveryBundle\Repository\DeliveryCostRepository'));
        $this->assertTrue($container->hasDefinition('Tourze\HotelCardDeliveryBundle\Repository\KeyCardDeliveryRepository'));

        // Check that admin controllers are registered
        $this->assertTrue($container->hasDefinition('Tourze\HotelCardDeliveryBundle\Controller\Admin\DeliveryCostCrudController'));
        $this->assertTrue($container->hasDefinition('Tourze\HotelCardDeliveryBundle\Controller\Admin\KeyCardDeliveryCrudController'));

        // Check that menu service is registered
        $this->assertTrue($container->hasDefinition('Tourze\HotelCardDeliveryBundle\Service\AdminMenu'));
    }

    public function test_load_configuresServicesCorrectly(): void
    {
        $container = new ContainerBuilder();
        $extension = new HotelCardDeliveryExtension();

        $extension->load([], $container);

        // Check that services are configured with autowire and autoconfigure
        $deliveryCostRepoDefinition = $container->getDefinition('Tourze\HotelCardDeliveryBundle\Repository\DeliveryCostRepository');
        $this->assertTrue($deliveryCostRepoDefinition->isAutowired());
        $this->assertTrue($deliveryCostRepoDefinition->isAutoconfigured());

        $keyCardRepoDefinition = $container->getDefinition('Tourze\HotelCardDeliveryBundle\Repository\KeyCardDeliveryRepository');
        $this->assertTrue($keyCardRepoDefinition->isAutowired());
        $this->assertTrue($keyCardRepoDefinition->isAutoconfigured());
    }
}