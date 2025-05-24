<?php

namespace Tourze\HotelCardDeliveryBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 配送员状态枚举
 */
enum DeliveryStaffStatusEnum: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case IDLE = 'idle';          // 空闲
    case BUSY = 'busy';          // 繁忙
    case ON_LEAVE = 'on_leave';  // 休假

    public function getLabel(): string
    {
        return match($this) {
            self::IDLE => '空闲',
            self::BUSY => '繁忙',
            self::ON_LEAVE => '休假',
        };
    }

    public static function getChoices(): array
    {
        return [
            '空闲' => self::IDLE,
            '繁忙' => self::BUSY,
            '休假' => self::ON_LEAVE,
        ];
    }

    public function canAssignWork(): bool
    {
        return $this === self::IDLE;
    }
} 