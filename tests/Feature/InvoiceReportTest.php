<?php

use App\Models\Invoice;
use App\Models\Membership;
use App\Models\User;
use Carbon\Carbon;

test('invoice report filters by issue date, source, status, and creator', function () {
    $accountant = User::factory()->accountant()->create();
    $adminCreator = User::factory()->admin()->create([
        'first_name' => 'Admin',
        'last_name' => 'Creator',
    ]);
    $receptionCreator = User::factory()->create([
        'role' => 'reception',
        'first_name' => 'Desk',
        'last_name' => 'Creator',
    ]);
    $member = User::factory()->create(['role' => 'member']);

    $membership = Membership::create([
        'user_id' => $member->id,
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-30',
        'remaining_days' => 12,
        'status' => 'active',
        'price' => 1200.00,
    ]);

    $matchingInvoice = Invoice::create([
        'membership_id' => $membership->id,
        'created_by_user_id' => $adminCreator->id,
        'invoice_number' => 'INV-26-30001',
        'amount' => 1200.00,
        'status' => 'paid',
        'issued_date' => Carbon::create(2026, 4, 10, 9, 0, 0, 'UTC'),
        'due_date' => Carbon::create(2026, 4, 17, 9, 0, 0, 'UTC'),
        'invoice_source' => 'membership',
    ]);

    Invoice::create([
        'user_id' => $member->id,
        'created_by_user_id' => $receptionCreator->id,
        'membership_id' => null,
        'invoice_number' => 'INV-26-30002',
        'amount' => 500.00,
        'status' => 'unpaid',
        'issued_date' => Carbon::create(2026, 4, 11, 9, 0, 0, 'UTC'),
        'due_date' => Carbon::create(2026, 4, 18, 9, 0, 0, 'UTC'),
        'invoice_source' => 'merchandise',
    ]);

    $response = $this->actingAs($accountant)->get(route('admin.invoices.list.data', [
        'draw' => 1,
        'start' => 0,
        'length' => 10,
        'issued_from' => '2026-04-10',
        'issued_to' => '2026-04-10',
        'status' => 'paid',
        'invoice_source' => 'membership',
        'created_by_user_id' => $adminCreator->id,
    ]));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.invoice_number', '<span class="font-weight-bold">INV-26-30001</span>')
        ->assertJsonPath('data.0.created_by', 'Admin Creator');

    expect($response->json('data.0.status'))->toContain('Paid');
    expect($response->json('data.0.action'))->toContain(route('admin.invoices.view', $matchingInvoice));
});

test('invoice report export and print respect active filters', function () {
    $accountant = User::factory()->accountant()->create();
    $creator = User::factory()->admin()->create([
        'first_name' => 'Filter',
        'last_name' => 'Owner',
    ]);
    $member = User::factory()->create(['role' => 'member']);

    Invoice::create([
        'user_id' => $member->id,
        'created_by_user_id' => $creator->id,
        'membership_id' => null,
        'invoice_number' => 'INV-26-31001',
        'amount' => 250.00,
        'status' => 'paid',
        'issued_date' => Carbon::create(2026, 4, 15, 8, 30, 0, 'UTC'),
        'due_date' => Carbon::create(2026, 4, 22, 8, 30, 0, 'UTC'),
        'invoice_source' => 'merchandise',
    ]);

    Invoice::create([
        'user_id' => $member->id,
        'created_by_user_id' => $creator->id,
        'membership_id' => null,
        'invoice_number' => 'INV-26-31002',
        'amount' => 450.00,
        'status' => 'paid',
        'issued_date' => Carbon::create(2026, 4, 20, 8, 30, 0, 'UTC'),
        'due_date' => Carbon::create(2026, 4, 27, 8, 30, 0, 'UTC'),
        'invoice_source' => 'merchandise',
    ]);

    $export = $this->actingAs($accountant)->get(route('admin.invoices.export.csv', [
        'issued_from' => '2026-04-15',
        'issued_to' => '2026-04-15',
        'invoice_source' => 'merchandise',
        'columns' => ['invoice_number', 'source', 'amount'],
    ]));

    $export->assertOk();
    $csv = $export->streamedContent();

    expect($csv)->toContain('INV-26-31001');
    expect($csv)->not->toContain('INV-26-31002');
    expect($csv)->toContain('Source');

    $print = $this->actingAs($accountant)->get(route('admin.invoices.print', [
        'issued_from' => '2026-04-15',
        'issued_to' => '2026-04-15',
        'invoice_source' => 'merchandise',
    ]));

    $print->assertOk()
        ->assertSee('INV-26-31001')
        ->assertDontSee('INV-26-31002')
        ->assertSee(__('Total invoices'));
});
