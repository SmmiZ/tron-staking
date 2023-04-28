<?php

namespace App\Enums;

enum InternalTxTypes: int
{
    /** 100-108 для наград за достижение уровня */
    case levelReward1 = 101;
    case levelReward2 = 102;
    case levelReward3 = 103;
    case levelReward4 = 104;
    case levelReward5 = 105;
    case levelReward6 = 106;
    case levelReward7 = 107;
    case levelReward8 = 108;
    /** 109 для бонусов от сервиса за стейк */
    case stakeProfit = 109;

    /** 150-170 для реферальных начислений по линиям */
    case lineBonus1 = 151;
    case lineBonus2 = 152;
    case lineBonus3 = 153;
    case lineBonus4 = 154;
    case lineBonus5 = 155;
    case lineBonus6 = 156;
    case lineBonus7 = 157;
    case lineBonus8 = 158;
    case lineBonus9 = 159;
    case lineBonus10 = 160;
    case lineBonus11 = 161;
    case lineBonus12 = 162;
    case lineBonus13 = 163;
    case lineBonus14 = 164;
    case lineBonus15 = 165;
    case lineBonus16 = 166;
    case lineBonus17 = 167;
    case lineBonus18 = 168;
    case lineBonus19 = 169;
    case lineBonus20 = 170;

    /** 200+ для операций списания */
    case withdrawal = 201;
    /** 999 для сбоев */
    case unknown = 999;

    public static function fromName(string $name): self
    {
        foreach (self::cases() as $operation) {
            if ($name === $operation->name) {
                return $operation;
            }
        }

        return self::unknown;
    }

    /**
     * Возвращает пояснение на русском языке
     *
     * @return string
     */
    public function translate(): string
    {
        return match ($this) {
            self::levelReward1 => 'Достижение уровня 1',
            self::levelReward2 => 'Достижение уровня 2',
            self::levelReward3 => 'Достижение уровня 3',
            self::levelReward4 => 'Достижение уровня 4',
            self::levelReward5 => 'Достижение уровня 5',
            self::levelReward6 => 'Достижение уровня 6',
            self::levelReward7 => 'Достижение уровня 7',
            self::levelReward8 => 'Достижение уровня 8',
            self::stakeProfit => 'Начисление за стейк',
            self::lineBonus1 => 'Реферальное начисление по линии 1',
            self::lineBonus2 => 'Реферальное начисление по линии 2',
            self::lineBonus3 => 'Реферальное начисление по линии 3',
            self::lineBonus4 => 'Реферальное начисление по линии 4',
            self::lineBonus5 => 'Реферальное начисление по линии 5',
            self::lineBonus6 => 'Реферальное начисление по линии 6',
            self::lineBonus7 => 'Реферальное начисление по линии 7',
            self::lineBonus8 => 'Реферальное начисление по линии 8',
            self::lineBonus9 => 'Реферальное начисление по линии 9',
            self::lineBonus10 => 'Реферальное начисление по линии 10',
            self::lineBonus11 => 'Реферальное начисление по линии 11',
            self::lineBonus12 => 'Реферальное начисление по линии 12',
            self::lineBonus13 => 'Реферальное начисление по линии 13',
            self::lineBonus14 => 'Реферальное начисление по линии 14',
            self::lineBonus15 => 'Реферальное начисление по линии 15',
            self::lineBonus16 => 'Реферальное начисление по линии 16',
            self::lineBonus17 => 'Реферальное начисление по линии 17',
            self::lineBonus18 => 'Реферальное начисление по линии 18',
            self::lineBonus19 => 'Реферальное начисление по линии 19',
            self::lineBonus20 => 'Реферальное начисление по линии 20',
            self::withdrawal => 'Вывод средств',
            self::unknown => 'Неизвестная операция',
        };
    }
}
