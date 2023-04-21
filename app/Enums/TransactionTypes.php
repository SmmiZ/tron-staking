<?php

namespace App\Enums;

enum TransactionTypes: int
{
    case stake = 1;
    case vote = 2;
    case delegate = 3;
    case undelegate = 4;
    case reward = 5;
    case unstake = 6;
    case transfer = 7;

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
            self::undelegate => 'Отозвать делегирование',
        };
    }
}
