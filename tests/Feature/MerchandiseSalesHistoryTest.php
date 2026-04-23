<?php

use App\Models\Invoice;
use App\Models\MerchandiseSaleLine;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

function createSalesHistoryInvoice(string $invoiceNumber, Carbon $issuedAt, array $lines, ?User $customer = null): Invoice
{
    $total = collect($lines)->sum(fn (array $line) => (float) $line['product']->unit_price * (int) $line['quantity']);

    $invoice = Invoice::create([
        'user_id' => $customer?->id,
        'membership_id' => null,
        'invoice_number' => $invoiceNumber,
        'amount' => $total,
        'status' => 'paid',
        'issued_date' => $issuedAt->copy()->utc(),
        'due_date' => $issuedAt->copy()->utc(),
        'invoice_source' => 'merchandise',
    ]);

    foreach ($lines as $line) {
        $product = $line['product'];
        $quantity = (int) $line['quantity'];

        MerchandiseSaleLine::create([
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $product->unit_price,
            'line_total' => (float) $product->unit_price * $quantity,
        ]);
    }

    Payment::create([
        'invoice_id' => $invoice->id,
        'membership_id' => null,
        'amount' => $total,
        'payment_date' => $issuedAt->copy()->utc(),
        'payment_method' => 'cash',
        'status' => 'completed',
    ]);

    return $invoice;
}

test('sales history report defaults to todays sales and merges invoice rows for multi-item invoices', function () {
    Carbon::setTestNow(Carbon::create(2026, 4, 24, 10, 0, 0, 'Africa/Addis_Ababa'));

    $reception = User::factory()->create(['role' => 'reception']);
    $customer = User::factory()->create([
        'role' => 'member',
        'first_name' => 'Abel',
        'last_name' => 'Bekele',
        'phone' => '0917553839',
    ]);

    $protein = Product::factory()->create(['name' => 'Protein Shake', 'sku' => 'SKU-PRO-1', 'unit_price' => 120]);
    $water = Product::factory()->create(['name' => 'Water Bottle', 'sku' => 'SKU-WAT-1', 'unit_price' => 20]);
    $wrap = Product::factory()->create(['name' => 'Energy Wrap', 'sku' => 'SKU-WRP-1', 'unit_price' => 65]);

    createSalesHistoryInvoice('INV-HIST-2401', Carbon::create(2026, 4, 24, 9, 15, 0, 'Africa/Addis_Ababa'), [
        ['product' => $protein, 'quantity' => 1],
        ['product' => $water, 'quantity' => 3],
    ], $customer);

    createSalesHistoryInvoice('INV-HIST-2301', Carbon::create(2026, 4, 23, 18, 0, 0, 'Africa/Addis_Ababa'), [
        ['product' => $wrap, 'quantity' => 2],
    ]);

    $response = $this->actingAs($reception)->get(route('admin.merchandise.history'));

    $response
        ->assertOk()
        ->assertSee('Merchandise Sales Report', false)
        ->assertSee('INV-HIST-2401', false)
        ->assertDontSee('INV-HIST-2301', false)
        ->assertSee('Protein Shake', false)
        ->assertSee('Water Bottle', false)
        ->assertSee('Friday, 24 Apr 2026', false);

    expect($response->getContent() ?? '')->toContain('rowspan="2"');
});

test('sales history report can be filtered to a specific date', function () {
    Carbon::setTestNow(Carbon::create(2026, 4, 24, 10, 0, 0, 'Africa/Addis_Ababa'));

    $reception = User::factory()->create(['role' => 'reception']);
    $product = Product::factory()->create(['name' => 'Recovery Drink', 'sku' => 'SKU-REC-1', 'unit_price' => 90]);

    createSalesHistoryInvoice('INV-HIST-2402', Carbon::create(2026, 4, 24, 11, 0, 0, 'Africa/Addis_Ababa'), [
        ['product' => $product, 'quantity' => 1],
    ]);

    createSalesHistoryInvoice('INV-HIST-2302', Carbon::create(2026, 4, 23, 12, 30, 0, 'Africa/Addis_Ababa'), [
        ['product' => $product, 'quantity' => 2],
    ]);

    $response = $this->actingAs($reception)->get(route('admin.merchandise.history', [
        'date' => '2026-04-23',
    ]));

    $response
        ->assertOk()
        ->assertSee('INV-HIST-2302', false)
        ->assertDontSee('INV-HIST-2402', false)
        ->assertSee('Thursday, 23 Apr 2026', false)
        ->assertSee('value="2026-04-23"', false);
});
