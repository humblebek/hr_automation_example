<?php

namespace App\Enum;

enum QuestionType: int
{
    case MANDATORY = 100;
    case OPTIONAL = 200;


    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function name($key): string
    {
        return match ($key) {
            self::MANDATORY->value  => "Maxsus savol",
            self::OPTIONAL->value => "Umumiy savol",
        };
    }


    public static function getList(): array
    {
        return array_combine(self::getAllValues(), array_map(fn($v) => self::name($v), self::getAllValues()));
    }
}
