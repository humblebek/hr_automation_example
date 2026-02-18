<?php

namespace App\Enum;

enum LanguageLevel: string
{
    public const BEGINNER = 'beginner';
    public const INTERMEDIATE = 'intermediate';
    public const ADVANCED = 'advanced';

    public static function getValues(): array
    {
        return [
            self::BEGINNER,
            self::INTERMEDIATE,
            self::ADVANCED,
        ];
    }

    public static function name(string $key): string
    {
        return match ($key) {
            self::BEGINNER => "Boshlang'ich",
            self::INTERMEDIATE => "O'rta",
            self::ADVANCED => 'Yuqori',
            default => "Noma'lum",
        };
    }

    public static function localizedName(string $key, string $lang = 'uz'): string
    {
        return match ($lang) {
            'ru' => match ($key) {
                self::BEGINNER => 'Начальный',
                self::INTERMEDIATE => 'Средний',
                self::ADVANCED => 'Продвинутый',
                default => 'Неизвестный',
            },
            'en' => match ($key) {
                self::BEGINNER => 'Beginner',
                self::INTERMEDIATE => 'Intermediate',
                self::ADVANCED => 'Advanced',
                default => 'Unknown',
            },
            default => self::name($key),
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
