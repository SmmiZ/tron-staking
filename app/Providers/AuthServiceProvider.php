<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\{Consumer, Stake};
use App\Policies\{ConsumerPolicy, StakePolicy};
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
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
