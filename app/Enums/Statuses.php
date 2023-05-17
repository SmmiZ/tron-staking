<?php

namespace App\Enums;

enum Statuses: int
{
    case new = 1;
    case pending = 2;
    case completed = 3;
    case declined = 4;

    public const OPEN_STATUSES = [
        self::new,
        self::pending,
    ];

    public function translate(): string
    {
        return match ($this) {
            self::new => 'Новый',
            self::pending => 'Выполняется',
            self::completed => 'Завершен',
            self::declined => 'Отклонен',
        };
    }
}
