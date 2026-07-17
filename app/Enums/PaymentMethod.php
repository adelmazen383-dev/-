<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';
    case SEMI_ANNUAL = 'semi_annual';
    case ANNUAL = 'annual';

    public function label(): string
    {
        return match ($this) {
            self::MONTHLY => 'شهري',
            self::QUARTERLY => 'ربع سنوي',
            self::SEMI_ANNUAL => 'نصف سنوي',
            self::ANNUAL => 'سنوي',
        };
    }
}
