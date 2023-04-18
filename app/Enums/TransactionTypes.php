<?php

namespace App\Enums;

enum TransactionTypes: int
{
    case reward = 1;
    case vote = 2;
    case stake = 3;
    case unstake = 4;
    case transfer = 5;
    case delegate = 6;

    /**
     * Возвращает пояснение на русском языке
     *
     * @return string
     */
    public function translate(): string
    {
        return match ($this) {
            self::reward => 'Награда',
            self::vote => 'Голосование',
            self::stake => 'Заморозка',
            self::unstake => 'Разморозка',
            self::transfer => 'Перевод',
            self::delegate => 'Делегирование',
        };
    }
}
