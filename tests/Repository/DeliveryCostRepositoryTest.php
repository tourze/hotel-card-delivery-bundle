<?php

namespace Tourze\HotelCardDeliveryBundle\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\HotelAgentBundle\Entity\Agent;
use Tourze\HotelAgentBundle\Entity\Order;
use Tourze\HotelCardDeliveryBundle\Entity\DeliveryCost;
use Tourze\HotelCardDeliveryBundle\Entity\KeyCardDelivery;
use Tourze\HotelCardDeliveryBundle\Repository\DeliveryCostRepository;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(DeliveryCostRepository::class)]
#[Group('integration')]
#[RunTestsInSeparateProcesses]
final class DeliveryCostRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试的初始化逻辑可以在这里添加
    }

    protected function getRepository(): DeliveryCostRepository
    {
        return self::getService(DeliveryCostRepository::class);
    }

    protected function createNewEntity(): DeliveryCost
    {
        $entityManager = self::getService(EntityManagerInterface::class);

        // 创建 Agent 实体
        $agent = new Agent();
        $agent->setCode('TEST_AGENT_' . uniqid());
        $agent->setCompanyName('Test Company');
        $agent->setContactPerson('Test Person');
        $agent->setPhone('13800138000');
        $entityManager->persist($agent);

        // 创建 Order 实体
        $order = new Order();
        $order->setOrderNo('TEST_ORDER_' . uniqid());
        $order->setAgent($agent);
        $order->setTotalAmount('1000.00');
        $entityManager->persist($order);

        // 创建 Hotel 实体
        $hotel = new Hotel();
        $hotel->setName('Test Hotel');
        $hotel->setAddress('Test Address');
        $hotel->setContactPerson('Hotel Manager');
        $hotel->setPhone('13900139000');
        $entityManager->persist($hotel);

        // 创建 KeyCardDelivery 实体
        $keyCardDelivery = new KeyCardDelivery();
        $keyCardDelivery->setOrder($order);
        $keyCardDelivery->setHotel($hotel);
        $keyCardDelivery->setRoomCount(1);
        $keyCardDelivery->setDeliveryTime(new \DateTimeImmutable());
        $keyCardDelivery->setFee('100.00');
        $entityManager->persist($keyCardDelivery);

        $entityManager->flush();

        // 创建 DeliveryCost 实体
        $deliveryCost = new DeliveryCost();
        $deliveryCost->setDelivery($keyCardDelivery);
        $deliveryCost->setBaseCost('50.00');
        $deliveryCost->setDistanceCost('20.00');
        $deliveryCost->setUrgencyCost('10.00');
        $deliveryCost->setExtraCost('5.00');
        $deliveryCost->setDistance(5.0);
        $deliveryCost->setCreatedBy('test_user');

        return $deliveryCost;
    }

    public function testFindByDelivery(): void
    {
        $entityManager = self::getService(EntityManagerInterface::class);
        $repository = $this->getRepository();

        // 创建第一个配送任务
        $deliveryCost1 = $this->createNewEntity();
        $delivery1 = $deliveryCost1->getDelivery();
        $repository->save($deliveryCost1);

        // 创建第二个配送任务
        $deliveryCost2 = $this->createNewEntity();
        $delivery2 = $deliveryCost2->getDelivery();
        $repository->save($deliveryCost2);

        // 测试找到对应的配送费用
        $foundCost1 = $repository->findByDelivery($delivery1);
        self::assertNotNull($foundCost1);
        self::assertSame($delivery1, $foundCost1->getDelivery());
        self::assertSame('50.00', $foundCost1->getBaseCost());

        $foundCost2 = $repository->findByDelivery($delivery2);
        self::assertNotNull($foundCost2);
        self::assertSame($delivery2, $foundCost2->getDelivery());

        // 确保不会返回错误的配送费用
        self::assertNotSame($foundCost1->getId(), $foundCost2->getId());

        // 测试不存在的配送任务
        $anotherDelivery = $this->createNewEntity()->getDelivery();
        $notFoundCost = $repository->findByDelivery($anotherDelivery);
        self::assertNull($notFoundCost);
    }

    public function testFindUnsettled(): void
    {
        $repository = $this->getRepository();
        $entityManager = self::getService(EntityManagerInterface::class);

        // 清理现有的 DeliveryCost 记录以确保测试隔离
        $entityManager->createQuery('DELETE FROM ' . DeliveryCost::class)->execute();

        // 创建未结算的配送费用
        $unsettledCost1 = $this->createNewEntity();
        $unsettledCost1->setSettled(false);
        $repository->save($unsettledCost1);

        $unsettledCost2 = $this->createNewEntity();
        $unsettledCost2->setSettled(false);
        $repository->save($unsettledCost2);

        // 创建已结算的配送费用
        $settledCost = $this->createNewEntity();
        $settledCost->setSettled(true);
        $repository->save($settledCost);

        // 查找未结算的配送费用
        $unsettledCosts = $repository->findUnsettled();

        // 验证结果
        self::assertCount(2, $unsettledCosts);

        $unsettledIds = array_map(fn ($cost) => $cost->getId(), $unsettledCosts);
        self::assertContains($unsettledCost1->getId(), $unsettledIds);
        self::assertContains($unsettledCost2->getId(), $unsettledIds);
        self::assertNotContains($settledCost->getId(), $unsettledIds);

        // 验证所有返回的费用都是未结算状态
        foreach ($unsettledCosts as $cost) {
            self::assertFalse($cost->isSettled());
        }

        // 测试没有未结算费用的情况
        $unsettledCost1->setSettled(true);
        $unsettledCost2->setSettled(true);
        $repository->save($unsettledCost1, false);
        $repository->save($unsettledCost2);

        $emptyResult = $repository->findUnsettled();
        self::assertEmpty($emptyResult);
    }

    public function testFindByDateRange(): void
    {
        $repository = $this->getRepository();
        $entityManager = self::getService(EntityManagerInterface::class);

        // 清理现有的 DeliveryCost 记录以确保测试隔离
        $entityManager->createQuery('DELETE FROM ' . DeliveryCost::class)->execute();

        // 创建不同时间的配送费用
        $now = new \DateTimeImmutable();
        $yesterday = $now->modify('-1 day');
        $twoDaysAgo = $now->modify('-2 days');
        $tomorrow = $now->modify('+1 day');

        // 创建昨天的配送费用
        $cost1 = $this->createNewEntity();
        $cost1->setCreateTime($yesterday);
        $repository->save($cost1);

        // 创建两天前的配送费用
        $cost2 = $this->createNewEntity();
        $cost2->setCreateTime($twoDaysAgo);
        $repository->save($cost2);

        // 创建明天的配送费用
        $cost3 = $this->createNewEntity();
        $cost3->setCreateTime($tomorrow);
        $repository->save($cost3);

        // 测试查找昨天到今天的范围
        $costs = $repository->findByDateRange($yesterday, $now);
        self::assertCount(1, $costs);
        self::assertSame($cost1->getId(), $costs[0]->getId());

        // 测试查找两天前到昨天的范围
        $costs = $repository->findByDateRange($twoDaysAgo, $yesterday);
        self::assertCount(2, $costs);
        $costIds = array_map(fn ($cost) => $cost->getId(), $costs);
        self::assertContains($cost1->getId(), $costIds);
        self::assertContains($cost2->getId(), $costIds);

        // 测试查找所有范围
        $costs = $repository->findByDateRange($twoDaysAgo, $tomorrow);
        self::assertCount(3, $costs);

        // 测试没有结果的范围
        $futureStart = $now->modify('+2 days');
        $futureEnd = $now->modify('+3 days');
        $costs = $repository->findByDateRange($futureStart, $futureEnd);
        self::assertEmpty($costs);
    }

    public function testCalculateTotalCostByPeriod(): void
    {
        $repository = $this->getRepository();
        $entityManager = self::getService(EntityManagerInterface::class);

        // 清理现有的 DeliveryCost 记录以确保测试隔离
        $entityManager->createQuery('DELETE FROM ' . DeliveryCost::class)->execute();

        $now = new \DateTimeImmutable();
        $yesterday = $now->modify('-1 day');
        $twoDaysAgo = $now->modify('-2 days');

        // 创建第一个配送费用：总费用85.00
        $cost1 = $this->createNewEntity();
        $cost1->setBaseCost('50.00');
        $cost1->setDistanceCost('20.00');
        $cost1->setUrgencyCost('10.00');
        $cost1->setExtraCost('5.00');
        $cost1->setCreateTime($yesterday);
        $repository->save($cost1);

        // 创建第二个配送费用：总费用130.00
        $cost2 = $this->createNewEntity();
        $cost2->setBaseCost('100.00');
        $cost2->setDistanceCost('20.00');
        $cost2->setUrgencyCost('5.00');
        $cost2->setExtraCost('5.00');
        $cost2->setCreateTime($yesterday);
        $repository->save($cost2);

        // 创建时间范围外的配送费用
        $cost3 = $this->createNewEntity();
        $cost3->setBaseCost('200.00');
        $cost3->setDistanceCost('0.00');
        $cost3->setUrgencyCost('0.00');
        $cost3->setExtraCost('0.00');
        $cost3->setCreateTime($twoDaysAgo);
        $repository->save($cost3);

        // 测试计算指定时间段内的总费用
        $totalCost = $repository->calculateTotalCostByPeriod($yesterday, $now);
        self::assertEquals(215.0, $totalCost); // 85.00 + 130.00

        // 测试没有记录的时间段
        $futureStart = $now->modify('+1 day');
        $futureEnd = $now->modify('+2 days');
        $totalCost = $repository->calculateTotalCostByPeriod($futureStart, $futureEnd);
        self::assertEquals(0.0, $totalCost);

        // 测试包含所有记录的时间段
        $totalCost = $repository->calculateTotalCostByPeriod($twoDaysAgo, $now);
        self::assertEquals(415.0, $totalCost); // 85.00 + 130.00 + 200.00
    }
}
