<?php

namespace App\Enums;

enum Statuses: int
{
    case new = 1;
    case pending = 2;
    case closed = 3;

    public const OPEN_STATUSES = [
        self::new,
        self::pending,
    ];

    public function translate(): string
    {
        return match ($this) {
            self::new => 'Новый',
            self::pending => 'Выполняется',
            self::closed => 'Закрыт',
        };
    }
}
