<?php

use App\Models\Invoice;
use App\Models\Membership;
use App\Models\User;
use Carbon\Carbon;

test('invoice list data supports status filtering and returns formatted issue dates', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();

    $membership = Membership::create([
        'user_id' => $member->id,
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-30',
        'remaining_days' => 3,
        'status' => 'active',
        'price' => 1200.00,
    ]);

    Invoice::create([
        'membership_id' => $membership->id,
        'invoice_number' => 'INV-26-10001',
        'amount' => 1200.00,
        'status' => 'unpaid',
        'issued_date' => Carbon::create(2026, 4, 22, 8, 0, 0, 'UTC'),
        'due_date' => Carbon::create(2026, 4, 29, 8, 0, 0, 'UTC'),
    ]);

    $paidInvoice = Invoice::create([
        'membership_id' => $membership->id,
        'invoice_number' => 'INV-26-10002',
        'amount' => 1200.00,
        'status' => 'paid',
        'issued_date' => Carbon::create(2026, 4, 23, 8, 0, 0, 'UTC'),
        'due_date' => Carbon::create(2026, 4, 30, 8, 0, 0, 'UTC'),
    ]);

    $response = $this->actingAs($admin)->get(route('admin.invoices.list.data', [
        'draw' => 1,
        'start' => 0,
        'length' => 10,
        'status' => 'paid',
    ]));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $paidInvoice->id)
        ->assertJsonPath('data.0.issued_date', 'Apr 23, 2026');

    expect($response->json('data.0.status'))->toContain('Paid');
});
