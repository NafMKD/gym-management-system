<?php

use App\Models\Invoice;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;

test('product movement report filters by date, product, reason, and actor', function () {
    $accountant = User::factory()->accountant()->create();
    $salesman = User::factory()->create([
        'role' => 'reception',
        'first_name' => 'Sales',
        'last_name' => 'One',
    ]);
    $otherSalesman = User::factory()->create([
        'role' => 'reception',
        'first_name' => 'Sales',
        'last_name' => 'Two',
    ]);
    $member = User::factory()->create(['role' => 'member']);

    $product = Product::factory()->create([
        'name' => 'Protein Tub',
        'sku' => 'PRO-001',
        'stock_quantity' => 15,
        'unit_price' => 450.00,
    ]);
    $otherProduct = Product::factory()->create([
        'name' => 'Shaker Bottle',
        'sku' => 'SHA-002',
        'stock_quantity' => 12,
        'unit_price' => 120.00,
    ]);

    $invoice = Invoice::create([
        'user_id' => $member->id,
        'membership_id' => null,
        'created_by_user_id' => $salesman->id,
        'invoice_number' => 'INV-26-34001',
        'amount' => 450.00,
        'status' => 'paid',
        'issued_date' => Carbon::create(2026, 4, 18, 8, 0, 0, 'UTC'),
        'due_date' => Carbon::create(2026, 4, 25, 8, 0, 0, 'UTC'),
        'invoice_source' => 'merchandise',
    ]);

    $matchingMovement = StockMovement::create([
        'product_id' => $product->id,
        'quantity_change' => -1,
        'reason' => 'sale',
        'invoice_id' => $invoice->id,
        'user_id' => $salesman->id,
        'notes' => 'Counter sale',
    ]);
    $matchingMovement->forceFill([
        'created_at' => Carbon::create(2026, 4, 18, 8, 10, 0, 'UTC'),
        'updated_at' => Carbon::create(2026, 4, 18, 8, 10, 0, 'UTC'),
    ])->saveQuietly();

    $otherMovement = StockMovement::create([
        'product_id' => $otherProduct->id,
        'quantity_change' => 4,
        'reason' => 'restock',
        'invoice_id' => null,
        'user_id' => $otherSalesman->id,
        'notes' => 'Morning stock',
    ]);
    $otherMovement->forceFill([
        'created_at' => Carbon::create(2026, 4, 19, 8, 10, 0, 'UTC'),
        'updated_at' => Carbon::create(2026, 4, 19, 8, 10, 0, 'UTC'),
    ])->saveQuietly();

    $response = $this->actingAs($accountant)->get(route('admin.products.movement.data', [
        'draw' => 1,
        'start' => 0,
        'length' => 10,
        'start_date' => '2026-04-18',
        'end_date' => '2026-04-18',
        'product_id' => $product->id,
        'reason' => 'sale',
        'actor_id' => $salesman->id,
    ]));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.product_name', 'Protein Tub')
        ->assertJsonPath('data.0.actor', 'Sales One')
        ->assertJsonPath('data.0.invoice_number', 'INV-26-34001');

    expect($response->json('data.0.quantity_change'))->toContain('-1');
    expect($response->json('data.0.notes'))->toBe('Counter sale');
});

test('product movement export and print respect active filters', function () {
    $accountant = User::factory()->accountant()->create();
    $actor = User::factory()->create(['role' => 'reception']);
    $product = Product::factory()->create([
        'name' => 'Creatine',
        'sku' => 'CRT-010',
        'stock_quantity' => 20,
        'unit_price' => 300.00,
    ]);

    $movement = StockMovement::create([
        'product_id' => $product->id,
        'quantity_change' => 5,
        'reason' => 'restock',
        'invoice_id' => null,
        'user_id' => $actor->id,
        'notes' => 'Weekly delivery',
    ]);
    $movement->forceFill([
        'created_at' => Carbon::create(2026, 4, 20, 11, 0, 0, 'UTC'),
        'updated_at' => Carbon::create(2026, 4, 20, 11, 0, 0, 'UTC'),
    ])->saveQuietly();

    $export = $this->actingAs($accountant)->get(route('admin.products.export.csv', [
        'start_date' => '2026-04-20',
        'end_date' => '2026-04-20',
        'product_id' => $product->id,
        'columns' => ['movement_date', 'product_name', 'reason', 'quantity_change'],
    ]));

    $export->assertOk();
    $csv = $export->streamedContent();

    expect($csv)->toContain('Creatine');
    expect($csv)->toContain('Restock');
    expect($csv)->toContain('Quantity Change');

    $print = $this->actingAs($accountant)->get(route('admin.products.print', [
        'start_date' => '2026-04-20',
        'end_date' => '2026-04-20',
        'product_id' => $product->id,
    ]));

    $print->assertOk()
        ->assertSee('Product Movement Report')
        ->assertSee('Creatine')
        ->assertSee(__('Quantity Log'));
});
