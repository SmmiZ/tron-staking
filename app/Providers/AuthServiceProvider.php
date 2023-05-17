<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\{Consumer, Order, Reactor, ResourceConsumption, Stake, TronTx, User, Wallet, Withdrawal};
use App\Policies\{ConsumerPolicy,
    OrderPolicy,
    ReactorPolicy,
    ResourceConsumptionPolicy,
    StakePolicy,
    TronTxPolicy,
    UserPolicy,
    WalletPolicy,
    WithdrawalPolicy};
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
         Consumer::class => ConsumerPolicy::class,
         Stake::class => StakePolicy::class,
         User::class => UserPolicy::class,
         TronTx::class => TronTxPolicy::class,
         Order::class => OrderPolicy::class,
         Wallet::class => WalletPolicy::class,
         Reactor::class => ReactorPolicy::class,
         ResourceConsumption::class => ResourceConsumptionPolicy::class,
         Withdrawal::class => WithdrawalPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
