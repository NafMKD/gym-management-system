<?php

use App\Models\Invoice;
use App\Models\Membership;
use App\Models\User;

test('invoice pdf download returns pdf response', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();
    $membership = Membership::create([
        'user_id' => $member->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-12-31',
        'remaining_days' => 1,
        'status' => 'active',
        'price' => 50.00,
    ]);
    $invoice = Invoice::create([
        'membership_id' => $membership->id,
        'invoice_number' => 'INV-26-99998',
        'amount' => 50.00,
        'status' => 'unpaid',
        'issued_date' => now(),
        'due_date' => now()->addDays(7),
    ]);

    $response = $this->actingAs($admin)->get(route('admin.invoices.pdf', $invoice));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});
