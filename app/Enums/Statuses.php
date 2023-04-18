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

    public const CLOSED_STATUSES = [
        self::completed,
        self::declined,
    ];

    public function translate(): string
    {
        return match ($this) {
            self::new => 'Новый',
            self::pending => 'В обработке',
            self::completed => 'Выполнен',
            self::declined => 'Отклонен',
        };
    }
}
