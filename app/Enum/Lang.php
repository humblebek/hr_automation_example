<?php

namespace App\Enum;

enum Lang: string
{
    public const UZ = 'uz';
    public const RU = 'ru';
    public const EN = 'en';


    public static function getValues(): array
    {
        return [
            self::UZ,
            self::RU,
            self::EN,
        ];
    }

    public static function name($key): string
    {
        return match ($key) {
            self::UZ => "O'zbek tili",
            self::RU => "Rus tili",
            self::EN => "Ingliz tili",

        };
    }

    public static function localizedName($key, string $lang = 'uz'): string
    {
        return match ($lang) {
            'ru' => match ($key) {
                self::UZ => "узбекский",
                self::RU => "русский",
                self::EN => "английский",
                default => strtoupper($key),
            },
            'en' => match ($key) {
                self::UZ => "Uzbek",
                self::RU => "Russian",
                self::EN => "English",
                default => strtoupper($key),
            },
            default => match ($key) {
                self::UZ => "O'zbek tili",
                self::RU => "Rus tili",
                self::EN => "Ingliz tili",
                default => strtoupper($key),
            },
        };
    }

    public static function getList(?string $lang = null): array
    {
        $select = [];
        foreach (self::getValues() as $value):
            $select[$value] = $lang ? self::localizedName($value, $lang) : self::name($value);
        endforeach;
        return $select;
    }
}
