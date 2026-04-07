<?php

use App\Models\Invoice;
use App\Models\Membership;
use App\Models\User;
use App\Repositories\PaymentRepository;

test('recordRefund creates negative payment and marks invoice unpaid', function () {
    $member = User::factory()->create();
    $membership = Membership::create([
        'user_id' => $member->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-12-31',
        'remaining_days' => 1,
        'status' => 'active',
        'price' => 100.00,
    ]);
    $invoice = Invoice::create([
        'membership_id' => $membership->id,
        'invoice_number' => 'INV-26-99997',
        'amount' => 100.00,
        'status' => 'paid',
        'issued_date' => now(),
        'due_date' => now()->addDays(7),
    ]);

    \App\Models\Payment::create([
        'invoice_id' => $invoice->id,
        'membership_id' => $membership->id,
        'amount' => 100.00,
        'payment_date' => now(),
        'payment_method' => 'cash',
        'payment_bank' => null,
        'bank_transaction_number' => null,
        'status' => 'completed',
        'payment_type' => 'payment',
    ]);

    app(PaymentRepository::class)->recordRefund([
        'invoice_id' => $invoice->id,
        'amount' => 100,
        'payment_method' => 'cash',
        'notes' => 'Test refund',
    ]);

    $invoice->refresh();
    expect($invoice->status)->toBe('unpaid');
    expect((float) $invoice->payments()->where('status', 'completed')->sum('amount'))->toBe(0.0);
    expect($invoice->payments()->where('payment_type', 'refund')->count())->toBe(1);
});
