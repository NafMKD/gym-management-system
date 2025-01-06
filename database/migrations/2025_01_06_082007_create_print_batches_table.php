<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('print_batches', function (Blueprint $table) {
            $table->id();
            $table->integer('position'); // Position (1-8) on the A4 sheet
            $table->unsignedBigInteger('membership_id')->constrained('memberships')->onDelete('cascade'); // Foreign key to Membership
            $table->boolean('is_printed')->default(false); // Whether the card has been printed
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_batches');
    }
};
