<?php

namespace Modules\Hotel\Enums;

enum AgreementType: string
{
    case SALE  = 'sale';
    case LEASE = 'lease';
    case RENT  = 'rent';

    public function label(): string
    {
        return match($this) {
            self::SALE  => 'Sale Agreement',
            self::LEASE => 'Lease Agreement',
            self::RENT  => 'Rent Agreement',
        };
    }
}
