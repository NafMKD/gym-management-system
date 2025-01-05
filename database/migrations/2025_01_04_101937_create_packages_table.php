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
        Schema::create('packages', function (Blueprint $table) {
            $table->id(); 
            $table->string('name', 255); 
            $table->integer('duration'); // Full duration of the package (e.g., 30 days).
            $table->integer('granted_days'); // Days granted within the package (e.g., 15 days).
            $table->decimal('price', 8, 2); // 'price' column (up to 999,999.99)
            $table->text('description')->nullable(); 
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
