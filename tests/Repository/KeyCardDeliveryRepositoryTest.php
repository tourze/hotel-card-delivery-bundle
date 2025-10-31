<?php

namespace Tourze\HotelCardDeliveryBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\HotelAgentBundle\Entity\Agent;
use Tourze\HotelAgentBundle\Entity\Order;
use Tourze\HotelCardDeliveryBundle\Entity\KeyCardDelivery;
use Tourze\HotelCardDeliveryBundle\Enum\DeliveryStatusEnum;
use Tourze\HotelCardDeliveryBundle\Repository\KeyCardDeliveryRepository;
use Tourze\HotelProfileBundle\Entity\Hotel;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(KeyCardDeliveryRepository::class)]
#[Group('integration')]
#[RunTestsInSeparateProcesses]
final class KeyCardDeliveryRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试的初始化逻辑可以在这里添加
    }

    protected function getRepository(): KeyCardDeliveryRepository
    {
        return self::getService(KeyCardDeliveryRepository::class);
    }

    private function createTestAgent(): Agent
    {
        $agent = new Agent();
        $agent->setCode('TEST-' . uniqid());
        $agent->setCompanyName('Test Agent Company');
        $agent->setContactPerson('Test Contact');
        $agent->setPhone('13800138000');
        self::getEntityManager()->persist($agent);
        self::getEntityManager()->flush();

        return $agent;
    }

    private function createTestOrder(): Order
    {
        $agent = $this->createTestAgent();
        $order = new Order();
        $order->setOrderNo('TEST-ORDER-' . uniqid());
        $order->setAgent($agent);
        $order->setTotalAmount('1000.00');
        self::getEntityManager()->persist($order);
        self::getEntityManager()->flush();

        return $order;
    }

    private function createTestHotel(): Hotel
    {
        $hotel = new Hotel();
        $hotel->setName('Test Hotel');
        $hotel->setAddress('Test Address 123');
        $hotel->setContactPerson('Test Manager');
        $hotel->setPhone('13900139000');
        $hotel->setStarLevel(4);
        self::getEntityManager()->persist($hotel);
        self::getEntityManager()->flush();

        return $hotel;
    }

    protected function createNewEntity(): object
    {
        $keyCardDelivery = new KeyCardDelivery();
        $keyCardDelivery->setOrder($this->createTestOrder());
        $keyCardDelivery->setHotel($this->createTestHotel());
        $keyCardDelivery->setRoomCount(2);
        $keyCardDelivery->setDeliveryTime(new \DateTimeImmutable('+1 hour'));
        $keyCardDelivery->setFee('200.00');

        return $keyCardDelivery;
    }

    public function testCountByDeliveryDate(): void
    {
        $targetDate = new \DateTimeImmutable('2024-12-25');

        // 创建目标日期的配送任务
        $delivery1 = new KeyCardDelivery();
        $delivery1->setOrder($this->createTestOrder());
        $delivery1->setHotel($this->createTestHotel());
        $delivery1->setRoomCount(2);
        $delivery1->setDeliveryTime(new \DateTimeImmutable('2024-12-25 10:00:00'));
        $delivery1->setFee('200.00');
        self::getEntityManager()->persist($delivery1);

        $delivery2 = new KeyCardDelivery();
        $delivery2->setOrder($this->createTestOrder());
        $delivery2->setHotel($this->createTestHotel());
        $delivery2->setRoomCount(3);
        $delivery2->setDeliveryTime(new \DateTimeImmutable('2024-12-25 14:30:00'));
        $delivery2->setFee('300.00');
        self::getEntityManager()->persist($delivery2);

        // 创建其他日期的配送任务
        $delivery3 = new KeyCardDelivery();
        $delivery3->setOrder($this->createTestOrder());
        $delivery3->setHotel($this->createTestHotel());
        $delivery3->setRoomCount(1);
        $delivery3->setDeliveryTime(new \DateTimeImmutable('2024-12-26 10:00:00'));
        $delivery3->setFee('100.00');
        self::getEntityManager()->persist($delivery3);

        self::getEntityManager()->flush();

        $repository = $this->getRepository();

        // 测试目标日期
        $count = $repository->countByDeliveryDate($targetDate);
        self::assertSame(2, $count);

        // 测试其他日期
        $otherDate = new \DateTimeImmutable('2024-12-26');
        $otherCount = $repository->countByDeliveryDate($otherDate);
        self::assertSame(1, $otherCount);

        // 测试不存在数据的日期
        $emptyDate = new \DateTimeImmutable('2024-12-27');
        $emptyCount = $repository->countByDeliveryDate($emptyDate);
        self::assertSame(0, $emptyCount);
    }

    public function testFindByDeliveryDate(): void
    {
        $targetDate = new \DateTimeImmutable('2024-12-25');

        // 创建目标日期的配送任务（时间排序测试）
        $delivery1 = new KeyCardDelivery();
        $delivery1->setOrder($this->createTestOrder());
        $delivery1->setHotel($this->createTestHotel());
        $delivery1->setRoomCount(2);
        $delivery1->setDeliveryTime(new \DateTimeImmutable('2024-12-25 14:00:00'));
        $delivery1->setFee('200.00');
        self::getEntityManager()->persist($delivery1);

        $delivery2 = new KeyCardDelivery();
        $delivery2->setOrder($this->createTestOrder());
        $delivery2->setHotel($this->createTestHotel());
        $delivery2->setRoomCount(3);
        $delivery2->setDeliveryTime(new \DateTimeImmutable('2024-12-25 10:00:00'));
        $delivery2->setFee('300.00');
        self::getEntityManager()->persist($delivery2);

        // 创建其他日期的配送任务
        $delivery3 = new KeyCardDelivery();
        $delivery3->setOrder($this->createTestOrder());
        $delivery3->setHotel($this->createTestHotel());
        $delivery3->setRoomCount(1);
        $delivery3->setDeliveryTime(new \DateTimeImmutable('2024-12-26 10:00:00'));
        $delivery3->setFee('100.00');
        self::getEntityManager()->persist($delivery3);

        self::getEntityManager()->flush();

        $repository = $this->getRepository();

        // 测试目标日期
        $results = $repository->findByDeliveryDate($targetDate);
        self::assertCount(2, $results);

        // 验证按时间排序（ASC）
        self::assertEquals($delivery2->getDeliveryTime(), $results[0]->getDeliveryTime());
        self::assertEquals($delivery1->getDeliveryTime(), $results[1]->getDeliveryTime());

        // 测试其他日期
        $otherDate = new \DateTimeImmutable('2024-12-26');
        $otherResults = $repository->findByDeliveryDate($otherDate);
        self::assertCount(1, $otherResults);
        self::assertEquals($delivery3->getDeliveryTime(), $otherResults[0]->getDeliveryTime());

        // 测试不存在数据的日期
        $emptyDate = new \DateTimeImmutable('2024-12-27');
        $emptyResults = $repository->findByDeliveryDate($emptyDate);
        self::assertCount(0, $emptyResults);
    }

    public function testFindByHotel(): void
    {
        $hotel1 = $this->createTestHotel();
        $hotel2 = $this->createTestHotel();

        // 创建hotel1的配送任务
        $delivery1 = new KeyCardDelivery();
        $delivery1->setOrder($this->createTestOrder());
        $delivery1->setHotel($hotel1);
        $delivery1->setRoomCount(2);
        $delivery1->setDeliveryTime(new \DateTimeImmutable('2024-12-25 14:00:00'));
        $delivery1->setFee('200.00');
        self::getEntityManager()->persist($delivery1);

        $delivery2 = new KeyCardDelivery();
        $delivery2->setOrder($this->createTestOrder());
        $delivery2->setHotel($hotel1);
        $delivery2->setRoomCount(3);
        $delivery2->setDeliveryTime(new \DateTimeImmutable('2024-12-25 10:00:00'));
        $delivery2->setFee('300.00');
        self::getEntityManager()->persist($delivery2);

        // 创建hotel2的配送任务
        $delivery3 = new KeyCardDelivery();
        $delivery3->setOrder($this->createTestOrder());
        $delivery3->setHotel($hotel2);
        $delivery3->setRoomCount(1);
        $delivery3->setDeliveryTime(new \DateTimeImmutable('2024-12-25 08:00:00'));
        $delivery3->setFee('100.00');
        self::getEntityManager()->persist($delivery3);

        self::getEntityManager()->flush();

        $repository = $this->getRepository();

        // 测试hotel1
        $hotel1Results = $repository->findByHotel($hotel1);
        self::assertCount(2, $hotel1Results);

        // 验证按时间排序（ASC）
        self::assertEquals($delivery2->getDeliveryTime(), $hotel1Results[0]->getDeliveryTime());
        self::assertEquals($delivery1->getDeliveryTime(), $hotel1Results[1]->getDeliveryTime());

        // 测试hotel2
        $hotel2Results = $repository->findByHotel($hotel2);
        self::assertCount(1, $hotel2Results);
        self::assertEquals($delivery3->getDeliveryTime(), $hotel2Results[0]->getDeliveryTime());
    }

    public function testFindByOrder(): void
    {
        $order1 = $this->createTestOrder();
        $order2 = $this->createTestOrder();

        // 创建order1的配送任务
        $delivery1 = new KeyCardDelivery();
        $delivery1->setOrder($order1);
        $delivery1->setHotel($this->createTestHotel());
        $delivery1->setRoomCount(2);
        $delivery1->setDeliveryTime(new \DateTimeImmutable('2024-12-25 14:00:00'));
        $delivery1->setFee('200.00');
        self::getEntityManager()->persist($delivery1);

        $delivery2 = new KeyCardDelivery();
        $delivery2->setOrder($order1);
        $delivery2->setHotel($this->createTestHotel());
        $delivery2->setRoomCount(3);
        $delivery2->setDeliveryTime(new \DateTimeImmutable('2024-12-25 10:00:00'));
        $delivery2->setFee('300.00');
        self::getEntityManager()->persist($delivery2);

        // 创建order2的配送任务
        $delivery3 = new KeyCardDelivery();
        $delivery3->setOrder($order2);
        $delivery3->setHotel($this->createTestHotel());
        $delivery3->setRoomCount(1);
        $delivery3->setDeliveryTime(new \DateTimeImmutable('2024-12-25 08:00:00'));
        $delivery3->setFee('100.00');
        self::getEntityManager()->persist($delivery3);

        self::getEntityManager()->flush();

        $repository = $this->getRepository();

        // 测试order1
        $order1Results = $repository->findByOrder($order1);
        self::assertCount(2, $order1Results);

        // 验证按时间排序（ASC）
        self::assertEquals($delivery2->getDeliveryTime(), $order1Results[0]->getDeliveryTime());
        self::assertEquals($delivery1->getDeliveryTime(), $order1Results[1]->getDeliveryTime());

        // 测试order2
        $order2Results = $repository->findByOrder($order2);
        self::assertCount(1, $order2Results);
        self::assertEquals($delivery3->getDeliveryTime(), $order2Results[0]->getDeliveryTime());
    }

    public function testFindByStatus(): void
    {
        // 先清理所有现有数据以确保测试隔离
        $em = self::getEntityManager();
        $connection = $em->getConnection();
        $connection->executeStatement('DELETE FROM key_card_delivery');

        // 创建不同状态的配送任务
        $delivery1 = new KeyCardDelivery();
        $delivery1->setOrder($this->createTestOrder());
        $delivery1->setHotel($this->createTestHotel());
        $delivery1->setRoomCount(2);
        $delivery1->setDeliveryTime(new \DateTimeImmutable('2024-12-25 14:00:00'));
        $delivery1->setFee('200.00');
        $delivery1->setStatus(DeliveryStatusEnum::PENDING);
        $em->persist($delivery1);

        $delivery2 = new KeyCardDelivery();
        $delivery2->setOrder($this->createTestOrder());
        $delivery2->setHotel($this->createTestHotel());
        $delivery2->setRoomCount(3);
        $delivery2->setDeliveryTime(new \DateTimeImmutable('2024-12-25 10:00:00'));
        $delivery2->setFee('300.00');
        $delivery2->setStatus(DeliveryStatusEnum::PENDING);
        $em->persist($delivery2);

        $delivery3 = new KeyCardDelivery();
        $delivery3->setOrder($this->createTestOrder());
        $delivery3->setHotel($this->createTestHotel());
        $delivery3->setRoomCount(1);
        $delivery3->setDeliveryTime(new \DateTimeImmutable('2024-12-25 08:00:00'));
        $delivery3->setFee('100.00');
        $delivery3->setStatus(DeliveryStatusEnum::COMPLETED);
        $em->persist($delivery3);

        $em->flush();

        $repository = $this->getRepository();

        // 测试PENDING状态
        $pendingResults = $repository->findByStatus(DeliveryStatusEnum::PENDING);
        self::assertCount(2, $pendingResults);

        // 验证按时间排序（ASC）
        self::assertEquals($delivery2->getDeliveryTime(), $pendingResults[0]->getDeliveryTime());
        self::assertEquals($delivery1->getDeliveryTime(), $pendingResults[1]->getDeliveryTime());

        // 测试COMPLETED状态
        $completedResults = $repository->findByStatus(DeliveryStatusEnum::COMPLETED);
        self::assertCount(1, $completedResults);
        self::assertEquals($delivery3->getDeliveryTime(), $completedResults[0]->getDeliveryTime());

        // 测试不存在的状态
        $inProgressResults = $repository->findByStatus(DeliveryStatusEnum::IN_PROGRESS);
        self::assertCount(0, $inProgressResults);
    }

    public function testFindPendingDeliveries(): void
    {
        // 先清理所有现有数据以确保测试隔离
        $em = self::getEntityManager();
        $connection = $em->getConnection();
        $connection->executeStatement('DELETE FROM key_card_delivery');

        // 创建不同状态的配送任务
        $delivery1 = new KeyCardDelivery();
        $delivery1->setOrder($this->createTestOrder());
        $delivery1->setHotel($this->createTestHotel());
        $delivery1->setRoomCount(2);
        $delivery1->setDeliveryTime(new \DateTimeImmutable('2024-12-25 14:00:00'));
        $delivery1->setFee('200.00');
        $delivery1->setStatus(DeliveryStatusEnum::PENDING);
        $em->persist($delivery1);

        $delivery2 = new KeyCardDelivery();
        $delivery2->setOrder($this->createTestOrder());
        $delivery2->setHotel($this->createTestHotel());
        $delivery2->setRoomCount(3);
        $delivery2->setDeliveryTime(new \DateTimeImmutable('2024-12-25 10:00:00'));
        $delivery2->setFee('300.00');
        $delivery2->setStatus(DeliveryStatusEnum::PENDING);
        $em->persist($delivery2);

        // 创建非PENDING状态的任务
        $delivery3 = new KeyCardDelivery();
        $delivery3->setOrder($this->createTestOrder());
        $delivery3->setHotel($this->createTestHotel());
        $delivery3->setRoomCount(1);
        $delivery3->setDeliveryTime(new \DateTimeImmutable('2024-12-25 08:00:00'));
        $delivery3->setFee('100.00');
        $delivery3->setStatus(DeliveryStatusEnum::COMPLETED);
        $em->persist($delivery3);

        $delivery4 = new KeyCardDelivery();
        $delivery4->setOrder($this->createTestOrder());
        $delivery4->setHotel($this->createTestHotel());
        $delivery4->setRoomCount(4);
        $delivery4->setDeliveryTime(new \DateTimeImmutable('2024-12-25 06:00:00'));
        $delivery4->setFee('400.00');
        $delivery4->setStatus(DeliveryStatusEnum::IN_PROGRESS);
        $em->persist($delivery4);

        $em->flush();

        $repository = $this->getRepository();

        // 测试获取待分配的配送任务
        $results = $repository->findPendingDeliveries();
        self::assertCount(2, $results);

        // 验证只包含PENDING状态的任务
        foreach ($results as $result) {
            self::assertEquals(DeliveryStatusEnum::PENDING, $result->getStatus());
        }

        // 验证按时间排序（ASC）
        self::assertEquals($delivery2->getDeliveryTime(), $results[0]->getDeliveryTime());
        self::assertEquals($delivery1->getDeliveryTime(), $results[1]->getDeliveryTime());
    }

    public function testFindTodayDeliveries(): void
    {
        // 先清理所有现有数据以确保测试隔离
        $em = self::getEntityManager();
        $connection = $em->getConnection();
        $connection->executeStatement('DELETE FROM key_card_delivery');

        $today = new \DateTimeImmutable();
        $todayStart = new \DateTimeImmutable($today->format('Y-m-d') . ' 10:00:00');
        $todayMiddle = new \DateTimeImmutable($today->format('Y-m-d') . ' 14:00:00');
        $todayEnd = new \DateTimeImmutable($today->format('Y-m-d') . ' 18:00:00');
        $tomorrow = new \DateTimeImmutable('+1 day');

        // 创建今日的配送任务（包含符合条件的状态）
        $delivery1 = new KeyCardDelivery();
        $delivery1->setOrder($this->createTestOrder());
        $delivery1->setHotel($this->createTestHotel());
        $delivery1->setRoomCount(2);
        $delivery1->setDeliveryTime($todayMiddle);
        $delivery1->setFee('200.00');
        $delivery1->setStatus(DeliveryStatusEnum::PENDING);
        self::getEntityManager()->persist($delivery1);

        $delivery2 = new KeyCardDelivery();
        $delivery2->setOrder($this->createTestOrder());
        $delivery2->setHotel($this->createTestHotel());
        $delivery2->setRoomCount(3);
        $delivery2->setDeliveryTime($todayStart);
        $delivery2->setFee('300.00');
        $delivery2->setStatus(DeliveryStatusEnum::ASSIGNED);
        self::getEntityManager()->persist($delivery2);

        $delivery3 = new KeyCardDelivery();
        $delivery3->setOrder($this->createTestOrder());
        $delivery3->setHotel($this->createTestHotel());
        $delivery3->setRoomCount(1);
        $delivery3->setDeliveryTime($todayEnd);
        $delivery3->setFee('100.00');
        $delivery3->setStatus(DeliveryStatusEnum::IN_PROGRESS);
        self::getEntityManager()->persist($delivery3);

        // 创建今日但不符合状态条件的任务
        $delivery4 = new KeyCardDelivery();
        $delivery4->setOrder($this->createTestOrder());
        $delivery4->setHotel($this->createTestHotel());
        $delivery4->setRoomCount(4);
        $delivery4->setDeliveryTime($todayMiddle);
        $delivery4->setFee('400.00');
        $delivery4->setStatus(DeliveryStatusEnum::COMPLETED);
        self::getEntityManager()->persist($delivery4);

        // 创建明日的配送任务
        $delivery5 = new KeyCardDelivery();
        $delivery5->setOrder($this->createTestOrder());
        $delivery5->setHotel($this->createTestHotel());
        $delivery5->setRoomCount(2);
        $delivery5->setDeliveryTime($tomorrow);
        $delivery5->setFee('200.00');
        $delivery5->setStatus(DeliveryStatusEnum::PENDING);
        self::getEntityManager()->persist($delivery5);

        self::getEntityManager()->flush();

        $repository = $this->getRepository();

        // 测试获取今日待配送的任务
        $results = $repository->findTodayDeliveries();
        self::assertCount(3, $results);

        // 验证只包含符合条件的状态
        $allowedStatuses = [DeliveryStatusEnum::PENDING, DeliveryStatusEnum::ASSIGNED, DeliveryStatusEnum::IN_PROGRESS];
        foreach ($results as $result) {
            self::assertContains($result->getStatus(), $allowedStatuses);
            // 验证都是今日的任务
            self::assertNotNull($result->getDeliveryTime());
            self::assertEquals($today->format('Y-m-d'), $result->getDeliveryTime()->format('Y-m-d'));
        }

        // 验证按时间排序（ASC）
        self::assertEquals($delivery2->getDeliveryTime(), $results[0]->getDeliveryTime());
        self::assertEquals($delivery1->getDeliveryTime(), $results[1]->getDeliveryTime());
        self::assertEquals($delivery3->getDeliveryTime(), $results[2]->getDeliveryTime());
    }

    public function testFindUrgentDeliveries(): void
    {
        // 先清理所有现有数据以确保测试隔离
        $em = self::getEntityManager();
        $connection = $em->getConnection();
        $connection->executeStatement('DELETE FROM key_card_delivery');

        $baseTime = new \DateTimeImmutable('2024-12-25 10:00:00');
        $futureTime1 = new \DateTimeImmutable('2024-12-25 11:00:00');
        $futureTime2 = new \DateTimeImmutable('2024-12-25 12:00:00');
        $pastTime = new \DateTimeImmutable('2024-12-24 10:00:00');

        // 创建符合条件的紧急配送任务（PENDING状态且时间>=today）
        $urgentDeliveries = [];
        for ($i = 0; $i < 25; ++$i) {
            $delivery = new KeyCardDelivery();
            $delivery->setOrder($this->createTestOrder());
            $delivery->setHotel($this->createTestHotel());
            $delivery->setRoomCount(2);
            // 简单使用baseTime + i分钟，确保时间递增且都>=baseTime
            $deliveryTime = $baseTime->modify("+{$i} minutes");
            $delivery->setDeliveryTime($deliveryTime);
            $delivery->setFee('200.00');
            $delivery->setStatus(DeliveryStatusEnum::PENDING);
            $em->persist($delivery);
            $urgentDeliveries[] = $delivery;
        }

        // 创建不符合条件的任务（非PENDING状态）
        $delivery1 = new KeyCardDelivery();
        $delivery1->setOrder($this->createTestOrder());
        $delivery1->setHotel($this->createTestHotel());
        $delivery1->setRoomCount(3);
        $delivery1->setDeliveryTime($futureTime1);
        $delivery1->setFee('300.00');
        $delivery1->setStatus(DeliveryStatusEnum::COMPLETED);
        $em->persist($delivery1);

        // 创建不符合条件的任务（时间在today之前）
        $delivery2 = new KeyCardDelivery();
        $delivery2->setOrder($this->createTestOrder());
        $delivery2->setHotel($this->createTestHotel());
        $delivery2->setRoomCount(1);
        $delivery2->setDeliveryTime($pastTime);
        $delivery2->setFee('100.00');
        $delivery2->setStatus(DeliveryStatusEnum::PENDING);
        $em->persist($delivery2);

        $em->flush();

        $repository = $this->getRepository();

        // 测试默认today参数
        $results = $repository->findUrgentDeliveries($baseTime);

        // 验证最多返回20条记录
        self::assertCount(20, $results);

        // 验证只包含PENDING状态的任务
        foreach ($results as $result) {
            self::assertEquals(DeliveryStatusEnum::PENDING, $result->getStatus());
            // 验证时间都>=baseTime
            self::assertGreaterThanOrEqual($baseTime, $result->getDeliveryTime());
        }

        // 验证按时间排序（ASC）- 检查前几个记录
        for ($i = 0; $i < 5; ++$i) {
            self::assertEquals($urgentDeliveries[$i]->getDeliveryTime(), $results[$i]->getDeliveryTime());
        }

        // 测试没有today参数时使用当前时间
        $resultsWithoutToday = $repository->findUrgentDeliveries();
        // 验证数组中的所有元素都是KeyCardDelivery实例
        foreach ($resultsWithoutToday as $item) {
            self::assertInstanceOf(KeyCardDelivery::class, $item);
        }
    }

    public function testFindUrgentDeliveriesEdgeCases(): void
    {
        // 先清理所有现有数据以确保测试隔离
        $em = self::getEntityManager();
        $connection = $em->getConnection();
        $connection->executeStatement('DELETE FROM key_card_delivery');

        $repository = $this->getRepository();

        // 测试没有符合条件的记录
        $emptyResults = $repository->findUrgentDeliveries(new \DateTimeImmutable('+1 year'));
        self::assertCount(0, $emptyResults);

        // 测试少于20条记录的情况
        $delivery = new KeyCardDelivery();
        $delivery->setOrder($this->createTestOrder());
        $delivery->setHotel($this->createTestHotel());
        $delivery->setRoomCount(1);
        $delivery->setDeliveryTime(new \DateTimeImmutable('+1 hour'));
        $delivery->setFee('100.00');
        $delivery->setStatus(DeliveryStatusEnum::PENDING);
        $em->persist($delivery);
        $em->flush();

        $limitedResults = $repository->findUrgentDeliveries(new \DateTimeImmutable());
        self::assertCount(1, $limitedResults);
        self::assertEquals($delivery->getId(), $limitedResults[0]->getId());
    }
}
