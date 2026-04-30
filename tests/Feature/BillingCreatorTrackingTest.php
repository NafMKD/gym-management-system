<?php

use App\Models\Invoice;
use App\Models\Membership;
use App\Models\Package;
use App\Models\User;

test('membership invoice stores creator id when created by admin', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create(['role' => 'member']);
    $package = Package::create([
        'name' => 'Monthly',
        'price' => 900.00,
        'duration' => 30,
        'granted_days' => 30,
        'description' => 'Monthly membership',
    ]);

    $response = $this->actingAs($admin)->post(route('admin.memberships.store'), [
        'user_id' => $member->id,
        'start_date' => '04/15/2026',
        'package_id' => $package->id,
    ]);

    $response->assertRedirect();

    $invoice = Invoice::query()->latest('id')->first();

    expect($invoice)->not->toBeNull();
    expect($invoice?->created_by_user_id)->toBe($admin->id);
});

test('payment and refund store creator id when recorded by admin', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create(['role' => 'member']);

    $membership = Membership::create([
        'user_id' => $member->id,
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-30',
        'remaining_days' => 10,
        'status' => 'active',
        'price' => 100.00,
    ]);

    $invoice = Invoice::create([
        'membership_id' => $membership->id,
        'invoice_number' => 'INV-26-20001',
        'amount' => 100.00,
        'status' => 'unpaid',
        'issued_date' => now(),
        'due_date' => now()->addDays(7),
    ]);

    $paymentResponse = $this->actingAs($admin)->post(route('admin.payments.store'), [
        'invoice_id' => $invoice->id,
        'membership_id' => $membership->id,
        'amount' => 100.00,
        'payment_method' => 'cash',
    ]);

    $paymentResponse->assertRedirect(route('admin.payments.list'));

    $payment = $invoice->fresh()->payments()->latest('id')->first();

    expect($payment)->not->toBeNull();
    expect($payment?->created_by_user_id)->toBe($admin->id);

    $refundResponse = $this->actingAs($admin)->post(route('admin.payments.refund'), [
        'invoice_id' => $invoice->id,
        'amount' => 100.00,
        'payment_method' => 'cash',
        'notes' => 'Full refund',
    ]);

    $refundResponse->assertRedirect(route('admin.invoices.view', $invoice));

    $refund = $invoice->fresh()->payments()->latest('id')->first();

    expect($refund)->not->toBeNull();
    expect($refund?->payment_type)->toBe('refund');
    expect($refund?->created_by_user_id)->toBe($admin->id);
});
