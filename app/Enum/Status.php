<?php

namespace App\Enum;



enum Status: int
{
    case ACTIVE = 100;
    case INACTIVE = 200;

    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function name($key): string
    {
        return match ($key) {
            self::ACTIVE->value => "Aktiv",
            self::INACTIVE->value => "Aktiv emas",
        };
    }

    public static function getList(): array
    {
        return array_combine(self::getAllValues(), array_map(fn($v) => self::name($v), self::getAllValues()));
    }
}
