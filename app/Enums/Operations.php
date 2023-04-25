<?php

namespace App\Enums;

enum Operations: int
{
    case AccountCreateContract = 0;
    case TransferContract = 1;
    case TransferAssetContract = 2;
    case VoteAssetContract = 3;
    case VoteWitnessContract = 4;
    case WitnessCreateContract = 5;
    case AssetIssueContract = 6;
    case WitnessUpdateContract = 8;
    case ParticipateAssetIssueContract = 9;
    case AccountUpdateContract = 10;
    case FreezeBalanceContract = 11;
    case UnfreezeBalanceContract = 12;
    case WithdrawBalanceContract = 13;
    case UnfreezeAssetContract = 14;
    case UpdateAssetContract = 15;
    case ProposalCreateContract = 16;
    case ProposalApproveContract = 17;
    case ProposalDeleteContract = 18;
    case SetAccountIdContract = 19;
    case CustomContract = 20;
    case CreateSmartContract = 30;
    case TriggerSmartContract = 31;
    case GetContract = 32;
    case UpdateSettingContract = 33;
    case ExchangeCreateContract = 41;
    case ExchangeInjectContract = 42;
    case ExchangeWithdrawContract = 43;
    case ExchangeTransactionContract = 44;
    case UpdateEnergyLimitContract = 45;
    case AccountPermissionUpdateContract = 46;
    case ClearABIContract = 48;
    case UpdateBrokerageContract = 49;
    case ShieldedTransferContract = 51;
    case MarketSellAssetContract = 52;
    case MarketCancelOrderContract = 53;
    case FreezeBalanceV2Contract = 54;
    case UnfreezeBalanceV2Contract = 55;
    case WithdrawExpireUnfreezeContract = 56;
    case DelegateResourceContract = 57;
    case UnDelegateResourceContract = 58;
    /** Для неизвестных операций */
    case Unknown = 999;

    public static function fromName(string $name): self
    {
        foreach (self::cases() as $operation) {
            if ($name === $operation->name) {
                return $operation;
            }
        }

        return self::Unknown;
    }

    public static function requiredIndexes(): array
    {
        return [
            self::VoteWitnessContract->value,
            self::FreezeBalanceV2Contract->value,
            self::UnfreezeBalanceV2Contract->value,
            self::WithdrawBalanceContract->value,
            self::DelegateResourceContract->value,
            self::UnDelegateResourceContract->value,
            self::WithdrawExpireUnfreezeContract->value,
        ];
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function translate(): string
    {
        return match ($this) {
            self::WithdrawBalanceContract => 'Вывод награды',
            self::VoteWitnessContract => 'Голосование',
            self::FreezeBalanceV2Contract => 'Заморозка',
            self::UnfreezeBalanceV2Contract => 'Разморозка',
            self::DelegateResourceContract => 'Делегирование',
            self::UnDelegateResourceContract => 'Отзыв делегирования',
            self::WithdrawExpireUnfreezeContract => 'Вывод TRX',
            default => $this->name,
        };
    }
}
