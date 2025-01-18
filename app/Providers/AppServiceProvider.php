<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\Membership;
use App\Models\Package;
use App\Models\User;
use App\Observers\AuditTrailObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Attendance::observe(AuditTrailObserver::class);
        Membership::observe(AuditTrailObserver::class);
        Package::observe(AuditTrailObserver::class);
        User::observe(AuditTrailObserver::class);
    }
}
