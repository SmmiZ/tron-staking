<?php

namespace App\Enums;

enum InternalTxTypes: int
{
    /** 100+ для операций пополнения */
    case levelReward1 = 101;
    case levelReward2 = 102;
    case levelReward3 = 103;
    case levelReward4 = 104;
    case levelReward5 = 105;
    case levelReward6 = 106;
    case levelReward7 = 107;
    case levelReward8 = 108;

    /** 200+ для операций списания */
    case withdrawal = 201;

    /**
     * Возвращает пояснение на русском языке
     *
     * @return string
     */
    public function translate(): string
    {
        return match ($this) {
            self::levelReward1 => 'Начисление за 1 уровень',
            self::levelReward2 => 'Начисление за 2 уровень',
            self::levelReward3 => 'Начисление за 3 уровень',
            self::levelReward4 => 'Начисление за 4 уровень',
            self::levelReward5 => 'Начисление за 5 уровень',
            self::levelReward6 => 'Начисление за 6 уровень',
            self::levelReward7 => 'Начисление за 7 уровень',
            self::levelReward8 => 'Начисление за 8 уровень',
            self::withdrawal => 'Вывод средств',
        };
    }
}
