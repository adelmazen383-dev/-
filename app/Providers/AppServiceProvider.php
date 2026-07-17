<?php

namespace App\Providers;

use App\Models\Contract;
use App\Observers\ContractObserver;
use App\Policies\ContractPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register the observer so every status change is automatically logged
        Contract::observe(ContractObserver::class);

        // Register the contract policy
        Gate::policy(Contract::class, ContractPolicy::class);

        // Clear dashboard cache when contracts change
        Contract::saved(function () {
            cache()->forget('dashboard_stats');
        });
    }
}
