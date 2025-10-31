<?php

namespace Tourze\HotelCardDeliveryBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\HotelCardDeliveryBundle\DependencyInjection\HotelCardDeliveryExtension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(HotelCardDeliveryExtension::class)]
final class HotelCardDeliveryExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    public function testBundleCanBeLoaded(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');

        $extension = new HotelCardDeliveryExtension();
        $extension->load([], $container);

        // 验证服务配置已正确加载
        $this->assertTrue($container->hasDefinition('Tourze\HotelCardDeliveryBundle\Controller\Admin\DeliveryCostCrudController'));
        $this->assertTrue($container->hasDefinition('Tourze\HotelCardDeliveryBundle\Controller\Admin\KeyCardDeliveryCrudController'));
        $this->assertTrue($container->hasDefinition('Tourze\HotelCardDeliveryBundle\Repository\DeliveryCostRepository'));
        $this->assertTrue($container->hasDefinition('Tourze\HotelCardDeliveryBundle\Repository\KeyCardDeliveryRepository'));
        $this->assertTrue($container->hasDefinition('Tourze\HotelCardDeliveryBundle\Service\AdminMenu'));

        // 验证测试环境服务已正确加载
        $this->assertTrue($container->hasDefinition('Tourze\HotelCardDeliveryBundle\DataFixtures\KeyCardDeliveryFixtures'));
        $this->assertTrue($container->hasDefinition('Tourze\HotelCardDeliveryBundle\DataFixtures\DeliveryCostFixtures'));
    }
}
