<?php

namespace Tourze\HotelCardDeliveryBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\HotelCardDeliveryBundle\Enum\DeliveryStatusEnum;

class DeliveryStatusEnumTest extends TestCase
{
    public function test_cases_containsAllExpectedValues(): void
    {
        $expectedValues = ['pending', 'assigned', 'in_progress', 'completed', 'cancelled', 'exception'];
        $actualValues = array_map(fn($case) => $case->value, DeliveryStatusEnum::cases());

        $this->assertEquals($expectedValues, $actualValues);
    }

    public function test_getLabel_returnsCorrectTranslations(): void
    {
        $this->assertEquals('待分配', DeliveryStatusEnum::PENDING->getLabel());
        $this->assertEquals('已分配', DeliveryStatusEnum::ASSIGNED->getLabel());
        $this->assertEquals('配送中', DeliveryStatusEnum::IN_PROGRESS->getLabel());
        $this->assertEquals('已完成', DeliveryStatusEnum::COMPLETED->getLabel());
        $this->assertEquals('已取消', DeliveryStatusEnum::CANCELLED->getLabel());
        $this->assertEquals('异常', DeliveryStatusEnum::EXCEPTION->getLabel());
    }

    public function test_implements_required_interfaces(): void
    {
        $status = DeliveryStatusEnum::PENDING;
        
        $this->assertInstanceOf(Labelable::class, $status);
        $this->assertInstanceOf(Itemable::class, $status);
        $this->assertInstanceOf(Selectable::class, $status);
    }

    public function test_isFinished_returnsCorrectValues(): void
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

    public function test_canChangeToComplete_returnsCorrectValues(): void
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

    public function test_canCancel_returnsCorrectValues(): void
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

    public function test_getChoices_returnsCorrectValues(): void
    {
        $choices = DeliveryStatusEnum::getChoices();
        
        $this->assertIsArray($choices);
        $this->assertEquals(DeliveryStatusEnum::PENDING, $choices['待分配']);
        $this->assertEquals(DeliveryStatusEnum::ASSIGNED, $choices['已分配']);
        $this->assertEquals(DeliveryStatusEnum::IN_PROGRESS, $choices['配送中']);
        $this->assertEquals(DeliveryStatusEnum::COMPLETED, $choices['已完成']);
        $this->assertEquals(DeliveryStatusEnum::CANCELLED, $choices['已取消']);
        $this->assertEquals(DeliveryStatusEnum::EXCEPTION, $choices['异常']);
    }

    public function test_trait_methods_work(): void
    {
        // Test that the enum uses the SelectTrait properly
        $reflection = new \ReflectionClass(DeliveryStatusEnum::class);
        $traits = $reflection->getTraitNames();
        
        $this->assertContains('Tourze\EnumExtra\SelectTrait', $traits);
        $this->assertContains('Tourze\EnumExtra\ItemTrait', $traits);
    }
}