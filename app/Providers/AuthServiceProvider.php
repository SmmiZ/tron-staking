<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\{Consumer, Stake, Transaction, User};
use App\Policies\{ConsumerPolicy, StakePolicy, TransactionPolicy, UserPolicy};
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
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
