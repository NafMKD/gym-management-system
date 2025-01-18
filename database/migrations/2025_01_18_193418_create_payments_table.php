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
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('membership_id')->constrained('memberships')->onDelete('cascade');
            $table->decimal('amount', 10, 2); 
            $table->dateTime('payment_date'); 
            $table->enum('payment_method', ['cash', 'bank']); 
            $table->enum('payment_bank', ['telebirr', 'cbe', 'boa'])->nullable(); 
            $table->string('bank_transaction_number', 50)->nullable(); 
            $table->enum('status', ['pending', 'completed', 'failed']); 
            $table->timestamps(); 
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
