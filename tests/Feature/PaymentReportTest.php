<?php

use App\Models\Invoice;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;

test('payment report filters by date, creator, method, bank, type, and source', function () {
    $accountant = User::factory()->accountant()->create();
    $creator = User::factory()->admin()->create([
        'first_name' => 'Cashier',
        'last_name' => 'One',
    ]);
    $otherCreator = User::factory()->create([
        'role' => 'reception',
        'first_name' => 'Cashier',
        'last_name' => 'Two',
    ]);
    $member = User::factory()->create(['role' => 'member']);

    $membership = Membership::create([
        'user_id' => $member->id,
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-30',
        'remaining_days' => 18,
        'status' => 'active',
        'price' => 900.00,
    ]);

    $invoice = Invoice::create([
        'membership_id' => $membership->id,
        'created_by_user_id' => $creator->id,
        'invoice_number' => 'INV-26-32001',
        'amount' => 900.00,
        'status' => 'paid',
        'issued_date' => Carbon::create(2026, 4, 10, 9, 0, 0, 'UTC'),
        'due_date' => Carbon::create(2026, 4, 17, 9, 0, 0, 'UTC'),
        'invoice_source' => 'membership',
    ]);

    Payment::create([
        'invoice_id' => $invoice->id,
        'membership_id' => $membership->id,
        'created_by_user_id' => $creator->id,
        'amount' => 900.00,
        'payment_date' => Carbon::create(2026, 4, 10, 10, 15, 0, 'UTC'),
        'payment_method' => 'bank',
        'payment_bank' => 'cbe',
        'bank_transaction_number' => 'TXN-900',
        'status' => 'completed',
        'payment_type' => 'payment',
    ]);

    Payment::create([
        'invoice_id' => $invoice->id,
        'membership_id' => $membership->id,
        'created_by_user_id' => $otherCreator->id,
        'amount' => -100.00,
        'payment_date' => Carbon::create(2026, 4, 11, 10, 15, 0, 'UTC'),
        'payment_method' => 'cash',
        'payment_bank' => null,
        'status' => 'completed',
        'payment_type' => 'refund',
    ]);

    $response = $this->actingAs($accountant)->get(route('admin.payments.list.data', [
        'draw' => 1,
        'start' => 0,
        'length' => 10,
        'start_date' => '2026-04-10',
        'end_date' => '2026-04-10',
        'payment_method' => 'bank',
        'payment_bank' => 'cbe',
        'status' => 'completed',
        'created_by_user_id' => $creator->id,
        'payment_type' => 'payment',
        'invoice_source' => 'membership',
    ]));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.invoice', 'INV-26-32001')
        ->assertJsonPath('data.0.created_by', 'Cashier One')
        ->assertJsonPath('data.0.payment_bank', 'CBE');
});

test('revenue totals and payment export and print respect active filters', function () {
    $accountant = User::factory()->accountant()->create();
    $creator = User::factory()->admin()->create();
    $member = User::factory()->create(['role' => 'member']);

    $invoice = Invoice::create([
        'user_id' => $member->id,
        'membership_id' => null,
        'created_by_user_id' => $creator->id,
        'invoice_number' => 'INV-26-33001',
        'amount' => 300.00,
        'status' => 'paid',
        'issued_date' => Carbon::create(2026, 4, 15, 8, 0, 0, 'UTC'),
        'due_date' => Carbon::create(2026, 4, 22, 8, 0, 0, 'UTC'),
        'invoice_source' => 'merchandise',
    ]);

    Payment::create([
        'invoice_id' => $invoice->id,
        'membership_id' => null,
        'created_by_user_id' => $creator->id,
        'amount' => 300.00,
        'payment_date' => Carbon::create(2026, 4, 15, 8, 15, 0, 'UTC'),
        'payment_method' => 'cash',
        'status' => 'completed',
        'payment_type' => 'payment',
    ]);

    Payment::create([
        'invoice_id' => $invoice->id,
        'membership_id' => null,
        'created_by_user_id' => $creator->id,
        'amount' => -50.00,
        'payment_date' => Carbon::create(2026, 4, 15, 9, 0, 0, 'UTC'),
        'payment_method' => 'cash',
        'status' => 'completed',
        'payment_type' => 'refund',
    ]);

    $totals = $this->actingAs($accountant)->get(route('admin.payments.revenue.total', [
        'start_date' => '2026-04-15',
        'end_date' => '2026-04-15',
        'invoice_source' => 'merchandise',
    ]));

    $totals->assertOk()
        ->assertJsonPath('totalRevenue', '250.00')
        ->assertJsonPath('totalTransactions', 2)
        ->assertJsonPath('netPayments', 1)
        ->assertJsonPath('refundTransactions', 1);

    $export = $this->actingAs($accountant)->get(route('admin.payments.export.csv', [
        'start_date' => '2026-04-15',
        'end_date' => '2026-04-15',
        'invoice_source' => 'merchandise',
        'columns' => ['payment_id', 'invoice', 'amount', 'payment_type'],
    ]));

    $export->assertOk();
    $csv = $export->streamedContent();
    expect($csv)->toContain('INV-26-33001');
    expect($csv)->toContain('Refund');

    $print = $this->actingAs($accountant)->get(route('admin.payments.revenue.print', [
        'start_date' => '2026-04-15',
        'end_date' => '2026-04-15',
        'invoice_source' => 'merchandise',
    ]));

    $print->assertOk()
        ->assertSee('Revenue Report')
        ->assertSee('INV-26-33001')
        ->assertSee('250.00');
});
