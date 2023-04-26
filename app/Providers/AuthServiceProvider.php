<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\{Consumer, Order, Reactor, Stake, Transaction, User, Wallet};
use App\Policies\{ConsumerPolicy, OrderPolicy, ReactorPolicy, StakePolicy, TransactionPolicy, UserPolicy, WalletPolicy};
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
         Transaction::class => TransactionPolicy::class,
         Order::class => OrderPolicy::class,
         Wallet::class => WalletPolicy::class,
         Reactor::class => ReactorPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
