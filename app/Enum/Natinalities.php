<?php

namespace App\Enum;

use App\Helpers\CommonHelper;

enum Natinalities: int
{
    case UZBEK = 100;
    case RUS = 200;
    case TAJIK = 300;
    case QIRGIZ = 400;
    case INGLIZ = 500;
    case KOZOQ = 600;
    case ARMAN = 700;

    /**
     * Get the base Uzbek label
     */
    public function label(): string
    {
        return match ($this) {
            self::UZBEK => "O‘zbek",
            self::RUS => "Rus",
            self::TAJIK => "Tojik",
            self::QIRGIZ => "Qirg‘iz",
            self::INGLIZ => "Ingliz",
            self::KOZOQ => "Qozoq",
            self::ARMAN => "Arman",
        };
    }

    /**
     * Get localized name (UZ / RU / EN)
     */
    public function localized(string $lang = 'uz'): string
    {
        return match ($lang) {
            'ru' => match ($this) {
                self::UZBEK => "Узбек",
                self::RUS => "Русский",
                self::TAJIK => "Таджик",
                self::QIRGIZ => "Киргиз",
                self::INGLIZ => "Англичанин",
                self::KOZOQ => "Казах",
                self::ARMAN => "Армянин",
            },
            'en' => match ($this) {
                self::UZBEK => "Uzbek",
                self::RUS => "Russian",
                self::TAJIK => "Tajik",
                self::QIRGIZ => "Kyrgyz",
                self::INGLIZ => "English",
                self::KOZOQ => "Kazakh",
                self::ARMAN => "Armenian",
            },
            default => $this->label(),
        };
    }

    /**
     * Get name by integer value
     */
    public static function name(int $value, string $lang = 'uz'): string
    {
        $case = self::tryFrom($value);
        return $case ? $case->localized($lang) : '—';
    }

    /**
     * Get all values as list with localized names
     */
    public static function getList(?string $lang = null): array
    {
        $lang ??= app()->getLocale();
        $helper = new CommonHelper();

        return collect(self::cases())->mapWithKeys(function ($case) use ($lang, $helper) {
            return [
                $case->value => $helper->getName(
                    $case->localized('uz'),
                    $case->localized('ru'),
                    $case->localized('en'),
                    $lang
                ),
            ];
        })->toArray();
    }
}
