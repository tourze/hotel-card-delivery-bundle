<?php

namespace Tourze\HotelCardDeliveryBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 配送状态枚举
 */
enum DeliveryStatusEnum: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case PENDING = 'pending';       // 待分配
    case ASSIGNED = 'assigned';     // 已分配
    case IN_PROGRESS = 'in_progress'; // 配送中
    case COMPLETED = 'completed';   // 已完成
    case CANCELLED = 'cancelled';   // 已取消
    case EXCEPTION = 'exception';   // 异常

    public function getLabel(): string
    {
        return match($this) {
            self::PENDING => '待分配',
            self::ASSIGNED => '已分配',
            self::IN_PROGRESS => '配送中',
            self::COMPLETED => '已完成',
            self::CANCELLED => '已取消',
            self::EXCEPTION => '异常',
        };
    }

    public static function getChoices(): array
    {
        return [
            '待分配' => self::PENDING,
            '已分配' => self::ASSIGNED,
            '配送中' => self::IN_PROGRESS,
            '已完成' => self::COMPLETED,
            '已取消' => self::CANCELLED,
            '异常' => self::EXCEPTION,
        ];
    }

    public function isFinished(): bool
    {
        return in_array($this, [self::COMPLETED, self::CANCELLED, self::EXCEPTION]);
    }

    public function canChangeToComplete(): bool
    {
        return $this === self::IN_PROGRESS;
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::PENDING, self::ASSIGNED, self::IN_PROGRESS]);
    }
} 