<?php

namespace Tourze\HotelCardDeliveryBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\HotelAgentBundle\DataFixtures\OrderFixtures;
use Tourze\HotelAgentBundle\Entity\Order;
use Tourze\HotelCardDeliveryBundle\Entity\KeyCardDelivery;
use Tourze\HotelCardDeliveryBundle\Enum\DeliveryStatusEnum;
use Tourze\HotelProfileBundle\Entity\Hotel;

/**
 * 房卡配送任务数据填充
 * 创建测试用的房卡配送任务数据，包括不同状态的配送任务
 */
class KeyCardDeliveryFixtures extends Fixture implements DependentFixtureInterface
{
    // 引用名称常量
    public const PENDING_DELIVERY_REFERENCE = 'pending-delivery';
    public const IN_PROGRESS_DELIVERY_REFERENCE = 'in-progress-delivery';
    public const COMPLETED_DELIVERY_REFERENCE = 'completed-delivery';
    public const CANCELLED_DELIVERY_REFERENCE = 'cancelled-delivery';
    public const COMPLEX_DELIVERY_REFERENCE = 'complex-delivery';

    public function load(ObjectManager $manager): void
    {
        // 获取订单和酒店引用
        $sampleOrder = $this->getReference(OrderFixtures::ORDER_CONFIRMED_REFERENCE, Order::class);
        $sampleHotel = $this->getReference(OrderFixtures::HOTEL_SAMPLE_REFERENCE, Hotel::class);
        $businessHotel = $this->getReference(OrderFixtures::HOTEL_BUSINESS_REFERENCE, Hotel::class);
        $luxuryHotel = $this->getReference(OrderFixtures::HOTEL_LUXURY_REFERENCE, Hotel::class);

        // 创建待分配的配送任务
        $pendingDelivery = new KeyCardDelivery();
        $pendingDelivery->setRoomCount(2);
        $pendingDelivery->setDeliveryTime(new \DateTimeImmutable('+1 day 14:00'));
        $pendingDelivery->setStatus(DeliveryStatusEnum::PENDING);
        $pendingDelivery->setFee('150.00');
        $pendingDelivery->setRemark('标准配送任务');
        $pendingDelivery->setOrder($sampleOrder);
        $pendingDelivery->setHotel($sampleHotel);

        $manager->persist($pendingDelivery);
        $this->addReference(self::PENDING_DELIVERY_REFERENCE, $pendingDelivery);

        // 创建配送中的任务
        $inProgressDelivery = new KeyCardDelivery();
        $inProgressDelivery->setRoomCount(3);
        $inProgressDelivery->setDeliveryTime(new \DateTimeImmutable('+2 days 16:30'));
        $inProgressDelivery->setStatus(DeliveryStatusEnum::IN_PROGRESS);
        $inProgressDelivery->setFee('220.00');
        $inProgressDelivery->setRemark('商务酒店配送中');
        $inProgressDelivery->setOrder($sampleOrder);
        $inProgressDelivery->setHotel($businessHotel);

        $manager->persist($inProgressDelivery);
        $this->addReference(self::IN_PROGRESS_DELIVERY_REFERENCE, $inProgressDelivery);

        // 创建已完成的配送任务
        $completedDelivery = new KeyCardDelivery();
        $completedDelivery->setRoomCount(1);
        $completedDelivery->setDeliveryTime(new \DateTimeImmutable('-1 day 10:00'));
        $completedDelivery->setStatus(DeliveryStatusEnum::COMPLETED);
        $completedDelivery->setFee('100.00');
        $completedDelivery->setRemark('已成功完成配送');
        $completedDelivery->setOrder($sampleOrder);
        $completedDelivery->setHotel($sampleHotel);

        $manager->persist($completedDelivery);
        $this->addReference(self::COMPLETED_DELIVERY_REFERENCE, $completedDelivery);

        // 创建已取消的配送任务
        $cancelledDelivery = new KeyCardDelivery();
        $cancelledDelivery->setRoomCount(4);
        $cancelledDelivery->setDeliveryTime(new \DateTimeImmutable('+5 days 12:00'));
        $cancelledDelivery->setStatus(DeliveryStatusEnum::CANCELLED);
        $cancelledDelivery->setFee('0.00');
        $cancelledDelivery->setRemark('客户取消订单');
        $cancelledDelivery->setOrder($sampleOrder);
        $cancelledDelivery->setHotel($businessHotel);

        $manager->persist($cancelledDelivery);
        $this->addReference(self::CANCELLED_DELIVERY_REFERENCE, $cancelledDelivery);

        // 创建紧急配送任务
        $urgentDelivery = new KeyCardDelivery();
        $urgentDelivery->setRoomCount(2);
        $urgentDelivery->setDeliveryTime(new \DateTimeImmutable('+6 hours'));
        $urgentDelivery->setStatus(DeliveryStatusEnum::PENDING);
        $urgentDelivery->setFee('350.00');
        $urgentDelivery->setRemark('紧急配送，需要加急处理');
        $urgentDelivery->setOrder($sampleOrder);
        $urgentDelivery->setHotel($luxuryHotel);

        $manager->persist($urgentDelivery);

        // 创建复杂配送场景任务（远距离+夜间+多房卡）
        $complexDelivery = new KeyCardDelivery();
        $complexDelivery->setRoomCount(8);
        $complexDelivery->setDeliveryTime(new \DateTimeImmutable('+3 days 22:00')); // 夜间配送
        $complexDelivery->setStatus(DeliveryStatusEnum::PENDING);
        $complexDelivery->setFee('800.00');
        $complexDelivery->setRemark('远距离夜间配送，多房卡，费用较高');
        $complexDelivery->setOrder($sampleOrder);
        $complexDelivery->setHotel($luxuryHotel);

        $manager->persist($complexDelivery);
        $this->addReference(self::COMPLEX_DELIVERY_REFERENCE, $complexDelivery);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            OrderFixtures::class,
        ];
    }
}
