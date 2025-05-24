<?php

namespace Tourze\HotelCardDeliveryBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\HotelAgentBundle\DataFixtures\OrderFixtures;
use Tourze\HotelCardDeliveryBundle\Entity\DeliveryCost;
use Tourze\HotelCardDeliveryBundle\Entity\KeyCardDelivery;

/**
 * 配送费用数据填充
 * 创建测试用的配送费用数据，包括不同计费方式和结算状态
 */
class DeliveryCostFixtures extends Fixture implements DependentFixtureInterface
{
    // 引用名称常量
    public const PENDING_COST_REFERENCE = 'pending-delivery-cost';
    public const COMPLETED_COST_REFERENCE = 'completed-delivery-cost';
    public const SETTLED_COST_REFERENCE = 'settled-delivery-cost';

    public function load(ObjectManager $manager): void
    {
        // 为待分配的配送任务创建费用记录
        $pendingCost = new DeliveryCost();
        $pendingCost->setDelivery($this->getReference(KeyCardDeliveryFixtures::PENDING_DELIVERY_REFERENCE, KeyCardDelivery::class));
        $pendingCost->setBaseCost('50.00');
        $pendingCost->setDistance(5.2);
        $pendingCost->calculateDistanceCost(1.5); // 使用 1.5元/km 的费率
        $pendingCost->setUrgencyCost('0.00');
        $pendingCost->setExtraCost('0.00');
        $pendingCost->setSettled(false);
        $pendingCost->setRemarks('标准距离配送费用');

        $manager->persist($pendingCost);
        $this->addReference(self::PENDING_COST_REFERENCE, $pendingCost);

        // 为已完成的配送任务创建费用记录
        $completedCost = new DeliveryCost();
        $completedCost->setDelivery($this->getReference(KeyCardDeliveryFixtures::COMPLETED_DELIVERY_REFERENCE, KeyCardDelivery::class));
        $completedCost->setBaseCost('50.00');
        $completedCost->setDistance(3.8);
        $completedCost->calculateDistanceCost(1.5); // 使用 1.5元/km 的费率
        $completedCost->setUrgencyCost('0.00');
        $completedCost->setExtraCost('5.00'); // 电梯费
        $completedCost->setSettled(true);
        $completedCost->setSettlementTime(new \DateTime('-1 day 12:00'));
        $completedCost->setRemarks('已完成配送的费用结算');

        $manager->persist($completedCost);
        $this->addReference(self::COMPLETED_COST_REFERENCE, $completedCost);

        // 为取消的配送任务创建处理费用记录
        $cancelledCost = new DeliveryCost();
        $cancelledCost->setDelivery($this->getReference(KeyCardDeliveryFixtures::CANCELLED_DELIVERY_REFERENCE, KeyCardDelivery::class));
        $cancelledCost->setBaseCost('20.00'); // 取消处理费
        $cancelledCost->setDistance(0.0); // 未实际配送
        $cancelledCost->setUrgencyCost('0.00');
        $cancelledCost->setExtraCost('0.00');
        $cancelledCost->setSettled(true);
        $cancelledCost->setSettlementTime(new \DateTime('-2 hours'));
        $cancelledCost->setRemarks('订单取消的处理费用');

        $manager->persist($cancelledCost);

        // 创建复杂配送场景的费用记录
        $complexCost = new DeliveryCost();
        $complexCost->setDelivery($this->getReference(KeyCardDeliveryFixtures::COMPLEX_DELIVERY_REFERENCE, KeyCardDelivery::class));
        $complexCost->setBaseCost('100.00');
        $complexCost->setDistance(15.8);
        $complexCost->calculateDistanceCost(2.5); // 使用 2.5元/km 的费率
        $complexCost->setUrgencyCost('50.00'); // 夜间配送加急费
        $complexCost->setExtraCost('20.00'); // 电梯费、停车费等
        $complexCost->setSettled(false);
        $complexCost->setRemarks('复杂配送场景：夜间+远距离+多项额外费用');

        $manager->persist($complexCost);
        $this->addReference(self::SETTLED_COST_REFERENCE, $complexCost);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            OrderFixtures::class,
            KeyCardDeliveryFixtures::class,
        ];
    }
}
