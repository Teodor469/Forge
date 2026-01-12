<?php

namespace App\Enums;

enum CurrencyType: string
{
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';
    case JPY = 'JPY';
    case CHF = 'CHF';
    case CAD = 'CAD';
    case AUD = 'AUD';

    public function symbol()
    {
        return match($this) {
            self::USD => '$',
            self::EUR => '€',
            self::GBP => '£',
            self::JPY => '¥',
            self::CHF => 'CHF',
            self::CAD => 'C$',
            self::AUD => 'A$',
        };
    }

    public function name(): string
    {
        return match($this) {
            self::USD => 'US Dollar',
            self::EUR => 'Euro',
            self::GBP => 'British Pound',
            self::JPY => 'Japanese Yen',
            self::CHF => 'Swiss Franc',
            self::CAD => 'Canadian Dollar',
            self::AUD => 'Australian Dollar',
        };
    }
}
