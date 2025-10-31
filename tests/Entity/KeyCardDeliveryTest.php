<?php

namespace Tourze\HotelCardDeliveryBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\HotelCardDeliveryBundle\Entity\KeyCardDelivery;
use Tourze\HotelCardDeliveryBundle\Enum\DeliveryStatusEnum;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(KeyCardDelivery::class)]
final class KeyCardDeliveryTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new KeyCardDelivery();
    }

    public function testConstructorSetsDefaultValues(): void
    {
        $delivery = new KeyCardDelivery();

        $this->assertEquals(DeliveryStatusEnum::PENDING, $delivery->getStatus());
        $this->assertEquals(0, $delivery->getRoomCount());
        $this->assertEquals('0.00', $delivery->getFee());
        $this->assertNull($delivery->getDeliveryTime());
        $this->assertNull($delivery->getCompletedTime());
        $this->assertNull($delivery->getReceiptPhotoUrl());
        $this->assertNull($delivery->getRemark());
    }

    public function testSettersAndGettersWorkCorrectly(): void
    {
        $delivery = new KeyCardDelivery();

        // Test room count
        $delivery->setRoomCount(5);
        $this->assertEquals(5, $delivery->getRoomCount());

        // Test fee
        $delivery->setFee('25.50');
        $this->assertEquals('25.50', $delivery->getFee());

        // Test delivery time
        $deliveryTime = new \DateTimeImmutable('2024-01-20 14:00:00');
        $delivery->setDeliveryTime($deliveryTime);
        $this->assertEquals($deliveryTime, $delivery->getDeliveryTime());

        // Test completed time
        $completedTime = new \DateTimeImmutable('2024-01-20 15:00:00');
        $delivery->setCompletedTime($completedTime);
        $this->assertEquals($completedTime, $delivery->getCompletedTime());

        // Test receipt photo URL
        $photoUrl = 'https://example.com/receipt.jpg';
        $delivery->setReceiptPhotoUrl($photoUrl);
        $this->assertEquals($photoUrl, $delivery->getReceiptPhotoUrl());

        // Test remark
        $remark = 'Special delivery instructions';
        $delivery->setRemark($remark);
        $this->assertEquals($remark, $delivery->getRemark());
    }

    public function testStatusTransitionMethodsWorkCorrectly(): void
    {
        $delivery = new KeyCardDelivery();

        // Initial status
        $this->assertEquals(DeliveryStatusEnum::PENDING, $delivery->getStatus());

        // Mark as in progress
        $delivery->markAsInProgress();
        $this->assertEquals(DeliveryStatusEnum::IN_PROGRESS, $delivery->getStatus());

        // Mark as completed
        $receiptUrl = 'https://example.com/receipt.jpg';
        $delivery->markAsCompleted($receiptUrl);
        $this->assertEquals(DeliveryStatusEnum::COMPLETED, $delivery->getStatus());
        $this->assertEquals($receiptUrl, $delivery->getReceiptPhotoUrl());
        $this->assertNotNull($delivery->getCompletedTime());

        // Reset and test cancel
        $delivery = new KeyCardDelivery();
        $cancelReason = 'Customer cancelled';
        $delivery->markAsCancelled($cancelReason);
        $this->assertEquals(DeliveryStatusEnum::CANCELLED, $delivery->getStatus());
        $this->assertStringContainsString($cancelReason, $delivery->getRemark() ?? '');

        // Reset and test exception
        $delivery = new KeyCardDelivery();
        $exceptionReason = 'Address not found';
        $delivery->markAsException($exceptionReason);
        $this->assertEquals(DeliveryStatusEnum::EXCEPTION, $delivery->getStatus());
        $this->assertStringContainsString($exceptionReason, $delivery->getRemark() ?? '');
    }

    public function testStatusCheckMethodsReturnCorrectValues(): void
    {
        $delivery = new KeyCardDelivery();

        // Test canStartDelivery
        $this->assertTrue($delivery->canStartDelivery());
        $this->assertFalse($delivery->isCompleted());
        $this->assertFalse($delivery->isCancelled());

        // Test after marking as in progress
        $delivery->markAsInProgress();
        $this->assertFalse($delivery->canStartDelivery());
        $this->assertFalse($delivery->isCompleted());

        // Test completed
        $delivery->markAsCompleted('receipt.jpg');
        $this->assertFalse($delivery->canStartDelivery());
        $this->assertTrue($delivery->isCompleted());

        // Test cancelled
        $delivery = new KeyCardDelivery();
        $delivery->markAsCancelled('reason');
        $this->assertFalse($delivery->canStartDelivery());
        $this->assertTrue($delivery->isCancelled());
    }

    public function testCalculateFeeReturnsCorrectValue(): void
    {
        $delivery = new KeyCardDelivery();

        // Test with default rate (100.00 per card)
        $delivery->setRoomCount(3);
        $delivery->calculateFee();
        $this->assertEquals('300', $delivery->getFee());

        // Test with custom rate
        $delivery->setRoomCount(5);
        $delivery->calculateFee(150.00);
        $this->assertEquals('750', $delivery->getFee());

        // Test with zero room count
        $delivery->setRoomCount(0);
        $delivery->calculateFee();
        $this->assertEquals('0', $delivery->getFee());
    }

    public function testStatusTransitionsWithRemarks(): void
    {
        $delivery = new KeyCardDelivery();

        // Test markAsCancelled adds remark
        $cancelReason = 'Customer request';
        $delivery->markAsCancelled($cancelReason);
        $this->assertStringContainsString($cancelReason, $delivery->getRemark() ?? '');
        $this->assertEquals($cancelReason, $delivery->getRemark());

        // Test markAsException adds remark
        $delivery2 = new KeyCardDelivery();
        $exceptionReason = 'Unable to contact';
        $delivery2->markAsException($exceptionReason);
        $this->assertEquals($exceptionReason, $delivery2->getRemark());
    }

    public function testToStringReturnsExpectedFormat(): void
    {
        $delivery = new KeyCardDelivery();

        // Without order and hotel - shows 'Unknown'
        $this->assertEquals('房卡配送: Unknown - Unknown', (string) $delivery);

        // Note: To test with actual order and hotel would require mocking external entities
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'roomCount' => ['roomCount', 5],
            'fee' => ['fee', '25.50'],
            'deliveryTime' => ['deliveryTime', new \DateTimeImmutable('2024-01-20 14:00:00')],
            'completedTime' => ['completedTime', new \DateTimeImmutable('2024-01-20 15:00:00')],
            'receiptPhotoUrl' => ['receiptPhotoUrl', 'https://example.com/receipt.jpg'],
            'remark' => ['remark', 'Special delivery instructions'],
        ];
    }
}
