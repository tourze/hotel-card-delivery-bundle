<?php

namespace Tourze\HotelCardDeliveryBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\Group;
use Tourze\HotelCardDeliveryBundle\Entity\KeyCardDelivery;
use Tourze\HotelCardDeliveryBundle\Enum\DeliveryStatusEnum;
use Tourze\HotelCardDeliveryBundle\Repository\KeyCardDeliveryRepository;

#[Group('integration')]
class KeyCardDeliveryRepositoryTest extends BaseRepositoryTest
{
    private KeyCardDeliveryRepository $repository;
    private TestEntityFactory $factory;

    public function test_save_withValidEntity_persistsToDatabase(): void
    {
        // Arrange
        $delivery = $this->createTestDelivery();

        // Act & Assert - delivery is already persisted by factory
        $this->assertNotNull($delivery->getId());
    }

    private function createTestDelivery(
        DeliveryStatusEnum $status = DeliveryStatusEnum::PENDING,
        ?\DateTime $deliveryTime = null
    ): KeyCardDelivery {
        $data = ['status' => $status->value];
        if ($deliveryTime !== null) {
            $data['delivery_time'] = $deliveryTime->format('Y-m-d H:i:s');
        }
        return $this->factory->createKeyCardDelivery($data);
    }

    public function test_save_withFlush_immediatelyPersists(): void
    {
        // Arrange
        $delivery = $this->createTestDelivery();

        // Assert - verify by direct query
        $result = $this->entityManager->getConnection()
            ->executeQuery('SELECT COUNT(*) as count FROM key_card_delivery')
            ->fetchAssociative();

        $this->assertEquals(1, $result['count']);
    }

    public function test_remove_withValidEntity_deletesFromDatabase(): void
    {
        // Arrange
        $delivery = $this->createTestDelivery();
        $deliveryId = $delivery->getId();

        // Act
        $this->entityManager->remove($delivery);
        $this->entityManager->flush();

        // Assert
        $deletedDelivery = $this->repository->find($deliveryId);
        $this->assertNull($deletedDelivery);
    }

    public function test_findByStatus_returnsCorrectDeliveries(): void
    {
        // Arrange
        $pendingDelivery1 = $this->createTestDelivery(DeliveryStatusEnum::PENDING);
        $pendingDelivery2 = $this->createTestDelivery(DeliveryStatusEnum::PENDING);
        $deliveringDelivery = $this->createTestDelivery(DeliveryStatusEnum::IN_PROGRESS);
        $completedDelivery = $this->createTestDelivery(DeliveryStatusEnum::COMPLETED);

        // Act
        $pendingResults = $this->repository->findByStatus(DeliveryStatusEnum::PENDING);
        $deliveringResults = $this->repository->findByStatus(DeliveryStatusEnum::IN_PROGRESS);

        // Assert
        $this->assertCount(2, $pendingResults);
        $this->assertCount(1, $deliveringResults);

        foreach ($pendingResults as $result) {
            $this->assertEquals(DeliveryStatusEnum::PENDING, $result->getStatus());
        }
    }

    public function test_findPendingDeliveries_returnsOnlyPendingDeliveries(): void
    {
        // Arrange
        $pendingDelivery = $this->createTestDelivery(DeliveryStatusEnum::PENDING);
        $assignedDelivery = $this->createTestDelivery(DeliveryStatusEnum::ASSIGNED);
        $deliveringDelivery = $this->createTestDelivery(DeliveryStatusEnum::IN_PROGRESS);

        // Act
        $results = $this->repository->findPendingDeliveries();

        // Assert
        $this->assertCount(1, $results);
        $this->assertEquals(DeliveryStatusEnum::PENDING, $results[0]->getStatus());
    }

    public function test_findTodayDeliveries_returnsCorrectDeliveries(): void
    {
        // Arrange
        $todayMorning = new \DateTime('today 09:00');
        $todayAfternoon = new \DateTime('today 14:00');
        $todayEvening = new \DateTime('today 18:00');
        $tomorrow = new \DateTime('tomorrow 14:00');

        $urgentDelivery = $this->createTestDelivery(DeliveryStatusEnum::PENDING, $todayMorning);
        $nonUrgentDelivery = $this->createTestDelivery(DeliveryStatusEnum::PENDING, $todayAfternoon);
        $pastDelivery = $this->createTestDelivery(DeliveryStatusEnum::PENDING, $todayEvening);
        $completedDelivery = $this->createTestDelivery(DeliveryStatusEnum::COMPLETED, $tomorrow);

        // Act
        $results = $this->repository->findTodayDeliveries();

        // Assert
        $this->assertCount(3, $results); // today's pending deliveries
        foreach ($results as $result) {
            $this->assertNotEquals(DeliveryStatusEnum::COMPLETED, $result->getStatus());
        }
    }

    public function test_findByDeliveryDate_returnsCorrectDeliveries(): void
    {
        // Arrange
        $todayTime = new \DateTime('today 14:00');
        $tomorrowTime = new \DateTime('tomorrow 14:00');
        $yesterdayTime = new \DateTime('yesterday 14:00');

        $todayDelivery1 = $this->createTestDelivery(DeliveryStatusEnum::PENDING, $todayTime);
        $todayDelivery2 = $this->createTestDelivery(DeliveryStatusEnum::ASSIGNED, $todayTime);
        $tomorrowDelivery = $this->createTestDelivery(DeliveryStatusEnum::PENDING, $tomorrowTime);
        $yesterdayDelivery = $this->createTestDelivery(DeliveryStatusEnum::PENDING, $yesterdayTime);

        // Act
        $results = $this->repository->findByDeliveryDate(new \DateTime('today'));

        // Assert
        $expectedCount = 2; // today's deliveries
        $this->assertCount($expectedCount, $results);
    }

    public function test_countByDeliveryDate_returnsCorrectCount(): void
    {
        // Arrange
        $targetDate = new \DateTime('2024-01-20');
        $otherDate = new \DateTime('2024-01-21');

        $delivery1 = $this->createTestDelivery(DeliveryStatusEnum::PENDING, (clone $targetDate)->setTime(10, 0));
        $delivery2 = $this->createTestDelivery(DeliveryStatusEnum::COMPLETED, (clone $targetDate)->setTime(14, 0));
        $delivery3 = $this->createTestDelivery(DeliveryStatusEnum::IN_PROGRESS, (clone $targetDate)->setTime(16, 0));
        $delivery4 = $this->createTestDelivery(DeliveryStatusEnum::PENDING, $otherDate);

        // Act
        $count = $this->repository->countByDeliveryDate($targetDate);

        // Assert
        $this->assertEquals(3, $count);
    }

    public function test_getTotalFeeInPeriod_returnsCorrectSum(): void
    {
        // Arrange - 使用今天的时间确保在查询范围内
        $todayTime = new \DateTime('today 14:00');
        $delivery1 = $this->factory->createKeyCardDelivery([
            'fee' => '50.00',
            'delivery_time' => $todayTime->format('Y-m-d H:i:s')
        ]);
        $delivery2 = $this->factory->createKeyCardDelivery([
            'fee' => '75.50',
            'delivery_time' => $todayTime->format('Y-m-d H:i:s')
        ]);
        $delivery3 = $this->factory->createKeyCardDelivery([
            'fee' => '25.25',
            'delivery_time' => $todayTime->format('Y-m-d H:i:s')
        ]);

        $startDate = new \DateTime('-1 day');
        $endDate = new \DateTime('+1 day');

        // Act
        // First mark some as completed
        $delivery1->setStatus(DeliveryStatusEnum::COMPLETED);
        $delivery2->setStatus(DeliveryStatusEnum::COMPLETED);
        $this->entityManager->flush();

        $totalCost = $this->repository->getTotalFeeInPeriod($startDate, $endDate);

        // Assert
        // Only completed deliveries are counted
        $expectedTotal = 50.00 + 75.50;
        $this->assertEquals($expectedTotal, $totalCost);
    }

    public function test_getTotalFeeInPeriod_withNoData_returnsZero(): void
    {
        // Arrange
        $startDate = new \DateTime('-1 day');
        $endDate = new \DateTime('+1 day');

        // Act
        $totalCost = $this->repository->getTotalFeeInPeriod($startDate, $endDate);

        // Assert
        $this->assertEquals(0.0, $totalCost);
    }

    public function test_save_and_remove_methods_inherited(): void
    {
        // Test that the save and remove methods from ServiceEntityRepository work correctly
        $delivery = $this->createTestDelivery();

        // Save should persist the entity
        $this->assertNotNull($delivery->getId());

        // Remove should delete the entity
        $id = $delivery->getId();
        $this->entityManager->remove($delivery);
        $this->entityManager->flush();
        $this->assertNull($this->repository->find($id));
    }

    protected function setupRepository(): void
    {
        $this->repository = static::getContainer()->get(KeyCardDeliveryRepository::class);
        $this->factory = new TestEntityFactory($this->entityManager);
    }
}