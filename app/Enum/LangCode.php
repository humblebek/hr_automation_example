<?php

namespace App\Enum;

enum LangCode: string
{
    case UZ = 'uz';
    case RU = 'ru';
    case EN = 'en';




    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function name($key): string
    {
        $key = trim($key);
        return match ($key) {
            self::UZ->value => "O'zbekcha",
            self::RU->value => "Ruscha",
            self::EN->value => "Inglizcha",
        };
    }


    public static function getList(): array
    {
        return array_combine(self::getAllValues(), array_map(fn($v) => self::name($v), self::getAllValues()));
    }

}

