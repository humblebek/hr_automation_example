<?php

namespace App\Enum;

enum ApplicantStatus: int
{
    case APPLIED = 100;
    case ACCEPTED = 200;
    case REJECTED = 300;

    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function name(int $key): string
    {
        return match ($key) {
            self::APPLIED->value  => "Ariza topshirilgan",
            self::ACCEPTED->value => "Qabul qilindi",
            self::REJECTED->value => "Rad etildi",
        };
    }

    public static function getList(): array
    {
        $list = [];
        foreach (self::cases() as $case) {
            $list[$case->value] = self::name($case->value);
        }
        return $list;
    }
}

