<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Trip\Models\Trip;
use Modules\Trip\Policies\TripPolicy;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \Modules\Core\Services\SettlementEngine::class,
            \Modules\Core\Services\SettlementEngine::class
        );
        $this->app->bind(
            \Modules\Core\Services\ExpenseSplitService::class,
            \Modules\Core\Services\ExpenseSplitService::class
        );
        $this->app->bind(
            \Modules\Core\Services\AuditLogService::class,
            \Modules\Core\Services\AuditLogService::class
        );
    }

    public function boot(): void
    {
        Gate::policy(Trip::class, TripPolicy::class);
    }
}
