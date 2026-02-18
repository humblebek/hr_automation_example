<?php

namespace App\Enum;

enum Gender: int
{
    case MALE = 100;
    case FEMALE = 200;

    public function label(): string
    {
        return match ($this) {
            self::MALE => 'Erkak',
            self::FEMALE => 'Ayol',
        };
    }

    public static function getList(): array
    {
        $select = [];
        foreach (self::cases() as $case) {
            $select[$case->value] = $case->label();
        }
        return $select;
    }

    public static function name(int $key): string
    {
        return match ($key) {
            self::MALE->value => 'Erkak',
            self::FEMALE->value => 'Ayol',
            default => 'Noma’lum',
        };
    }
}
