<?php

namespace Tourze\HotelCardDeliveryBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\HotelCardDeliveryBundle\Enum\DeliveryStatusEnum;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(DeliveryStatusEnum::class)]
final class DeliveryStatusEnumTest extends AbstractEnumTestCase
{
    #[TestWith(['pending', '待分配'])]
    #[TestWith(['assigned', '已分配'])]
    #[TestWith(['in_progress', '配送中'])]
    #[TestWith(['completed', '已完成'])]
    #[TestWith(['cancelled', '已取消'])]
    #[TestWith(['exception', '异常'])]
    public function testValueAndLabelMapping(string $value, string $expectedLabel): void
    {
        $enum = DeliveryStatusEnum::from($value);
        $this->assertEquals($value, $enum->value);
        $this->assertEquals($expectedLabel, $enum->getLabel());
    }

    public function testImplementsRequiredInterfaces(): void
    {
        $status = DeliveryStatusEnum::PENDING;

        $this->assertInstanceOf(Labelable::class, $status);
        $this->assertInstanceOf(Itemable::class, $status);
        $this->assertInstanceOf(Selectable::class, $status);
    }

    public function testIsFinishedReturnsCorrectValues(): void
    {
        // Finished statuses
        $this->assertTrue(DeliveryStatusEnum::COMPLETED->isFinished());
        $this->assertTrue(DeliveryStatusEnum::CANCELLED->isFinished());
        $this->assertTrue(DeliveryStatusEnum::EXCEPTION->isFinished());

        // Not finished statuses
        $this->assertFalse(DeliveryStatusEnum::PENDING->isFinished());
        $this->assertFalse(DeliveryStatusEnum::ASSIGNED->isFinished());
        $this->assertFalse(DeliveryStatusEnum::IN_PROGRESS->isFinished());
    }

    public function testCanChangeToCompleteReturnsCorrectValues(): void
    {
        // Can change to complete
        $this->assertTrue(DeliveryStatusEnum::IN_PROGRESS->canChangeToComplete());

        // Cannot change to complete
        $this->assertFalse(DeliveryStatusEnum::PENDING->canChangeToComplete());
        $this->assertFalse(DeliveryStatusEnum::ASSIGNED->canChangeToComplete());
        $this->assertFalse(DeliveryStatusEnum::COMPLETED->canChangeToComplete());
        $this->assertFalse(DeliveryStatusEnum::CANCELLED->canChangeToComplete());
        $this->assertFalse(DeliveryStatusEnum::EXCEPTION->canChangeToComplete());
    }

    public function testCanCancelReturnsCorrectValues(): void
    {
        // Can cancel
        $this->assertTrue(DeliveryStatusEnum::PENDING->canCancel());
        $this->assertTrue(DeliveryStatusEnum::ASSIGNED->canCancel());
        $this->assertTrue(DeliveryStatusEnum::IN_PROGRESS->canCancel());

        // Cannot cancel
        $this->assertFalse(DeliveryStatusEnum::COMPLETED->canCancel());
        $this->assertFalse(DeliveryStatusEnum::CANCELLED->canCancel());
        $this->assertFalse(DeliveryStatusEnum::EXCEPTION->canCancel());
    }

    public function testGetChoicesReturnsCorrectValues(): void
    {
        $choices = DeliveryStatusEnum::getChoices();
        $this->assertEquals(DeliveryStatusEnum::PENDING, $choices['待分配']);
        $this->assertEquals(DeliveryStatusEnum::ASSIGNED, $choices['已分配']);
        $this->assertEquals(DeliveryStatusEnum::IN_PROGRESS, $choices['配送中']);
        $this->assertEquals(DeliveryStatusEnum::COMPLETED, $choices['已完成']);
        $this->assertEquals(DeliveryStatusEnum::CANCELLED, $choices['已取消']);
        $this->assertEquals(DeliveryStatusEnum::EXCEPTION, $choices['异常']);
    }

    public function testTraitMethodsWork(): void
    {
        // Test that the enum uses the SelectTrait properly
        $reflection = new \ReflectionClass(DeliveryStatusEnum::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains('Tourze\EnumExtra\SelectTrait', $traits);
        $this->assertContains('Tourze\EnumExtra\ItemTrait', $traits);
    }

    public function testToArrayMethodExists(): void
    {
        $reflection = new \ReflectionClass(DeliveryStatusEnum::class);
        $this->assertTrue($reflection->hasMethod('toArray'));
        $method = $reflection->getMethod('toArray');
        $this->assertTrue($method->isPublic());
    }

    public function testToSelectItemMethodExists(): void
    {
        $reflection = new \ReflectionClass(DeliveryStatusEnum::class);
        $this->assertTrue($reflection->hasMethod('toSelectItem'));
        $method = $reflection->getMethod('toSelectItem');
        $this->assertTrue($method->isPublic());
    }

    public function testFromThrowsExceptionForInvalidValue(): void
    {
        $this->expectException(\ValueError::class);
        DeliveryStatusEnum::from('invalid_value');
    }

    public function testTryFromReturnsNullForInvalidValue(): void
    {
        $result = DeliveryStatusEnum::tryFrom('invalid_value');
        $this->assertSame(null, $result);
    }

    public function testTryFromReturnsEnumForValidValue(): void
    {
        $result = DeliveryStatusEnum::tryFrom('pending');
        $this->assertInstanceOf(DeliveryStatusEnum::class, $result);
        $this->assertEquals(DeliveryStatusEnum::PENDING, $result);
    }

    public function testLabelUniqueness(): void
    {
        $labels = [];
        foreach (DeliveryStatusEnum::cases() as $case) {
            $label = $case->getLabel();
            $this->assertNotContains($label, $labels, 'Label "' . $label . '" is not unique');
            $labels[] = $label;
        }
    }

    public function testValueUniqueness(): void
    {
        $values = [];
        foreach (DeliveryStatusEnum::cases() as $case) {
            $value = $case->value;
            $this->assertNotContains($value, $values, 'Value "' . $value . '" is not unique');
            $values[] = $value;
        }
    }
}
