<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\ClassBooking;
use App\Models\ClassSchedule;
use App\Models\GymClass;
use App\Models\Invoice;
use App\Models\Membership;
use App\Models\MembershipExtensionRequest;
use App\Models\MerchandiseSaleLine;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\TrainerCommissionEntry;
use App\Models\TrainerProfile;
use App\Models\TrainerSessionFeedback;
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
        Invoice::observe(AuditTrailObserver::class);
        Membership::observe(AuditTrailObserver::class);
        Package::observe(AuditTrailObserver::class);
        Payment::observe(AuditTrailObserver::class);
        User::observe(AuditTrailObserver::class);
        MembershipExtensionRequest::observe(AuditTrailObserver::class);
        GymClass::observe(AuditTrailObserver::class);
        ClassSchedule::observe(AuditTrailObserver::class);
        ClassBooking::observe(AuditTrailObserver::class);
        Product::observe(AuditTrailObserver::class);
        MerchandiseSaleLine::observe(AuditTrailObserver::class);
        StockMovement::observe(AuditTrailObserver::class);
        TrainerProfile::observe(AuditTrailObserver::class);
        TrainerCommissionEntry::observe(AuditTrailObserver::class);
        TrainerSessionFeedback::observe(AuditTrailObserver::class);
    }
}
