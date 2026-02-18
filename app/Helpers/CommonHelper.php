<?php

namespace App\Helpers;

class CommonHelper
{
    /**
     * Return localized name by app locale or provided language
     */
    public static function getName(string $uz, string $ru, string $en, ?string $lang = null): string
    {
        $list = [
            'uz' => $uz,
            'ru' => $ru,
            'en' => $en,
        ];

        $lang = $lang ?: app()->getLocale();
        return $list[$lang] ?? $uz;
    }

}
