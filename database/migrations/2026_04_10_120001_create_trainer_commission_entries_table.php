<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainer_commission_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained('users')->cascadeOnDelete();
            $table->string('source', 20);
            $table->foreignId('class_booking_id')->nullable()->constrained('class_bookings')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('earned_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['class_booking_id'], 'trainer_commission_entries_booking_unique');
            $table->index(['trainer_id', 'earned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainer_commission_entries');
    }
};
