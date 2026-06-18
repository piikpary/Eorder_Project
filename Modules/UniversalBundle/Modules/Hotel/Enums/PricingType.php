<?php

namespace Modules\Hotel\Enums;

enum PricingType: string
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case BIWEEKLY = 'biweekly';
    case MONTHLY = 'monthly';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match($this) {
            self::DAILY    => 'Daily',
            self::WEEKLY   => 'Weekly',
            self::BIWEEKLY => 'Biweekly',
            self::MONTHLY  => 'Monthly',
            self::CUSTOM   => 'Custom',
        };
    }

    public function shortLabel(): string
    {
        return match($this) {
            self::DAILY    => 'day',
            self::WEEKLY   => 'week',
            self::BIWEEKLY => '2 weeks',
            self::MONTHLY  => 'month',
            self::CUSTOM   => 'stay',
        };
    }
}
