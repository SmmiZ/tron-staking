<?php

namespace App\Enums;

enum TransactionTypes: int
{
    case reward = 1;
    case vote = 2;
    case staking = 3;
    case unstake = 4;
    case transfer = 5;

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
            self::staking => 'Заморозка',
            self::unstake => 'Разморозка',
            self::transfer => 'Перевод',
        };
    }
}
