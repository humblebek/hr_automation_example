<?php

namespace App\Enum;

enum QuestionFile: int
{
    case INT = 100;
    case TEXT = 200;
    case IMAGE = 300;


    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function name($key): string
    {
        return match ($key) {
            self::INT->value  => "Raqam",
            self::TEXT->value => "Text",
            self::IMAGE->value => "Rasm",
        };
    }


    public static function getList(): array
    {
        return array_combine(self::getAllValues(), array_map(fn($v) => self::name($v), self::getAllValues()));
    }
}
