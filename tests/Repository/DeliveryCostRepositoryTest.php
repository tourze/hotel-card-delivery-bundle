<?php

namespace Tourze\HotelCardDeliveryBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\Group;
use Tourze\HotelCardDeliveryBundle\Entity\DeliveryCost;
use Tourze\HotelCardDeliveryBundle\Entity\KeyCardDelivery;
use Tourze\HotelCardDeliveryBundle\Repository\DeliveryCostRepository;

#[Group('integration')]
class DeliveryCostRepositoryTest extends BaseRepositoryTest
{
    private DeliveryCostRepository $repository;
    private TestEntityFactory $factory;

    public function test_save_withValidEntity_persistsToDatabase(): void
    {
        // Arrange
        $delivery = $this->createTestDelivery(); // 已经持久化
        $cost = $this->createTestDeliveryCost($delivery);

        // Act
        $this->repository->save($cost, true);

        // Assert
        $this->assertNotNull($cost->getId());
        $this->assertEquals(20.00, $cost->getTotalCost());
    }

    private function createTestDelivery(): KeyCardDelivery
    {
        return $this->factory->createKeyCardDelivery();
    }

    private function createTestDeliveryCost(KeyCardDelivery $delivery, bool $settled = false): DeliveryCost
    {
        return $this->factory->createDeliveryCost($delivery, [
            'baseCost' => '10.00',
            'distanceCost' => '5.00', 
            'urgencyCost' => '3.00',
            'extraCost' => '2.00',
            'settled' => $settled
        ]);
    }

    public function test_save_withFlush_immediatelyPersists(): void
    {
        // Arrange
        $delivery = $this->createTestDelivery(); // 已经持久化
        $cost = $this->createTestDeliveryCost($delivery);

        // Act
        $this->repository->save($cost, true);

        // Assert - verify by direct query
        $result = $this->entityManager->getConnection()
            ->executeQuery('SELECT COUNT(*) as count FROM delivery_cost')
            ->fetchAssociative();

        $this->assertEquals(1, $result['count']);
    }

    public function test_remove_withValidEntity_deletesFromDatabase(): void
    {
        // Arrange
        $delivery = $this->createTestDelivery(); // 已经持久化
        $cost = $this->createTestDeliveryCost($delivery);
        $this->repository->save($cost, true);
        $costId = $cost->getId();

        // Act
        $this->repository->remove($cost, true);

        // Assert
        $deletedCost = $this->repository->find($costId);
        $this->assertNull($deletedCost);
    }

    public function test_findByDelivery_withExistingDelivery_returnsDeliveryCost(): void
    {
        // Arrange
        $delivery = $this->createTestDelivery(); // 已经持久化
        $cost = $this->createTestDeliveryCost($delivery);
        $this->repository->save($cost, true);

        // Act
        $result = $this->repository->findByDelivery($delivery);

        // Assert
        $this->assertNotNull($result);
        $this->assertInstanceOf(DeliveryCost::class, $result);
        $this->assertEquals($delivery->getId(), $result->getDelivery()->getId());
    }

    public function test_findByDelivery_withNonExistentDelivery_returnsNull(): void
    {
        // Arrange
        $delivery = $this->createTestDelivery(); // 已经持久化

        // Act - no cost created for this delivery
        $result = $this->repository->findByDelivery($delivery);

        // Assert
        $this->assertNull($result);
    }

    public function test_findUnsettled_returnsOnlyUnsettledCosts(): void
    {
        // Arrange
        $delivery1 = $this->createTestDelivery();
        $delivery2 = $this->createTestDelivery();
        $delivery3 = $this->createTestDelivery();

        $unsettledCost1 = $this->createTestDeliveryCost($delivery1, false);
        $unsettledCost2 = $this->createTestDeliveryCost($delivery2, false);
        $settledCost = $this->createTestDeliveryCost($delivery3, true);

        $this->repository->save($unsettledCost1, false);
        $this->repository->save($unsettledCost2, false);
        $this->repository->save($settledCost, true);

        // Act
        $results = $this->repository->findUnsettled();

        // Assert
        $this->assertCount(2, $results);
        foreach ($results as $result) {
            $this->assertFalse($result->isSettled());
        }
    }

    public function test_findByDateRange_returnsCorrectCosts(): void
    {
        // Arrange
        $delivery1 = $this->createTestDelivery();
        $delivery2 = $this->createTestDelivery();
        $delivery3 = $this->createTestDelivery();

        $cost1 = $this->createTestDeliveryCost($delivery1);
        $cost2 = $this->createTestDeliveryCost($delivery2);
        $cost3 = $this->createTestDeliveryCost($delivery3);

        $this->repository->save($cost1, false);
        $this->repository->save($cost2, false);
        $this->repository->save($cost3, true);

        $startDate = new \DateTime('-1 day');
        $endDate = new \DateTime('+1 day');

        // Act
        $results = $this->repository->findByDateRange($startDate, $endDate);

        // Assert
        $this->assertCount(3, $results);
        foreach ($results as $result) {
            $this->assertInstanceOf(DeliveryCost::class, $result);
        }
    }

    public function test_calculateTotalCostByPeriod_returnsCorrectSum(): void
    {
        // Arrange
        $delivery1 = $this->createTestDelivery();
        $delivery2 = $this->createTestDelivery();

        $cost1 = $this->createTestDeliveryCost($delivery1);
        $cost1->setBaseCost('10.00');
        $cost1->setDistanceCost('5.00');
        $cost1->setUrgencyCost('0.00');
        $cost1->setExtraCost('0.00');

        $cost2 = $this->createTestDeliveryCost($delivery2);
        $cost2->setBaseCost('15.00');
        $cost2->setDistanceCost('10.00');
        $cost2->setUrgencyCost('5.00');
        $cost2->setExtraCost('0.00');

        $this->repository->save($cost1, false);
        $this->repository->save($cost2, true);

        $startDate = new \DateTime('-1 day');
        $endDate = new \DateTime('+1 day');

        // Act
        $total = $this->repository->calculateTotalCostByPeriod($startDate, $endDate);

        // Assert
        $expectedTotal = 15.00 + 30.00; // cost1 total + cost2 total
        $this->assertEquals($expectedTotal, (float)$total);
    }

    public function test_calculateTotalCostByPeriod_withNoData_returnsZero(): void
    {
        // Arrange
        $startDate = new \DateTime('-1 day');
        $endDate = new \DateTime('+1 day');

        // Act
        $total = $this->repository->calculateTotalCostByPeriod($startDate, $endDate);

        // Assert
        $this->assertEquals(0.00, $total);
    }

    protected function setupRepository(): void
    {
        $this->repository = static::getContainer()->get(DeliveryCostRepository::class);
        $this->factory = new TestEntityFactory($this->entityManager);
    }
}