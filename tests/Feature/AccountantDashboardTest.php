<?php

use App\Models\Invoice;
use App\Models\Membership;
use App\Models\MerchandiseSaleLine;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;

test('accountant dashboard shows finance stock and activity insights', function () {
    Carbon::setTestNow(Carbon::create(2026, 4, 30, 12, 0, 0, 'Africa/Addis_Ababa'));

    $accountant = User::factory()->accountant()->create();
    $creator = User::factory()->admin()->create([
        'first_name' => 'Cashier',
        'last_name' => 'One',
    ]);
    $member = User::factory()->create([
        'role' => 'member',
        'first_name' => 'Marta',
        'last_name' => 'Member',
    ]);
    $delinquentMember = User::factory()->create([
        'role' => 'member',
        'first_name' => 'Late',
        'last_name' => 'Member',
    ]);

    $membership = Membership::create([
        'user_id' => $member->id,
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-30',
        'remaining_days' => 0,
        'status' => 'active',
        'price' => 1000.00,
    ]);

    $overdueMembership = Membership::create([
        'user_id' => $delinquentMember->id,
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-30',
        'remaining_days' => 0,
        'status' => 'inactive',
        'price' => 800.00,
    ]);

    $proteinTub = Product::factory()->create([
        'name' => 'Protein Tub',
        'sku' => 'PRO-100',
        'stock_quantity' => 5,
        'low_stock_threshold' => 5,
        'unit_price' => 300.00,
    ]);

    $shakerBottle = Product::factory()->create([
        'name' => 'Shaker Bottle',
        'sku' => 'SHA-200',
        'stock_quantity' => 6,
        'low_stock_threshold' => 3,
        'unit_price' => 150.00,
    ]);

    $membershipInvoice = Invoice::create([
        'membership_id' => $membership->id,
        'user_id' => null,
        'created_by_user_id' => $creator->id,
        'invoice_number' => 'INV-ACCT-1001',
        'amount' => 1000.00,
        'status' => 'paid',
        'issued_date' => Carbon::create(2026, 4, 5, 9, 0, 0, 'UTC'),
        'due_date' => Carbon::create(2026, 5, 5, 9, 0, 0, 'UTC'),
        'invoice_source' => 'membership',
    ]);

    $overdueInvoice = Invoice::create([
        'membership_id' => $overdueMembership->id,
        'user_id' => null,
        'created_by_user_id' => $creator->id,
        'invoice_number' => 'INV-ACCT-1002',
        'amount' => 800.00,
        'status' => 'unpaid',
        'issued_date' => Carbon::create(2026, 4, 9, 9, 0, 0, 'UTC'),
        'due_date' => Carbon::create(2026, 4, 20, 9, 0, 0, 'UTC'),
        'invoice_source' => 'membership',
    ]);

    $merchandiseInvoice = Invoice::create([
        'membership_id' => null,
        'user_id' => $member->id,
        'created_by_user_id' => $creator->id,
        'invoice_number' => 'INV-ACCT-1003',
        'amount' => 600.00,
        'status' => 'paid',
        'issued_date' => Carbon::create(2026, 4, 12, 10, 0, 0, 'UTC'),
        'due_date' => Carbon::create(2026, 5, 12, 10, 0, 0, 'UTC'),
        'invoice_source' => 'merchandise',
    ]);

    Payment::create([
        'invoice_id' => $membershipInvoice->id,
        'membership_id' => $membership->id,
        'created_by_user_id' => $creator->id,
        'amount' => 1000.00,
        'payment_date' => Carbon::create(2026, 4, 5, 9, 15, 0, 'UTC'),
        'payment_method' => 'bank',
        'payment_bank' => 'cbe',
        'bank_transaction_number' => 'TXN-ACCT-1001',
        'status' => 'completed',
        'payment_type' => 'payment',
    ]);

    Payment::create([
        'invoice_id' => $merchandiseInvoice->id,
        'membership_id' => null,
        'created_by_user_id' => $creator->id,
        'amount' => 600.00,
        'payment_date' => Carbon::create(2026, 4, 12, 10, 20, 0, 'UTC'),
        'payment_method' => 'cash',
        'payment_bank' => null,
        'bank_transaction_number' => null,
        'status' => 'completed',
        'payment_type' => 'payment',
    ]);

    Payment::create([
        'invoice_id' => $merchandiseInvoice->id,
        'membership_id' => null,
        'created_by_user_id' => $creator->id,
        'amount' => -100.00,
        'payment_date' => Carbon::create(2026, 4, 15, 11, 0, 0, 'UTC'),
        'payment_method' => 'cash',
        'payment_bank' => null,
        'bank_transaction_number' => null,
        'status' => 'completed',
        'payment_type' => 'refund',
    ]);

    MerchandiseSaleLine::create([
        'invoice_id' => $merchandiseInvoice->id,
        'product_id' => $proteinTub->id,
        'quantity' => 2,
        'unit_price' => 300.00,
        'line_total' => 600.00,
    ]);

    $restockMovement = StockMovement::create([
        'product_id' => $proteinTub->id,
        'quantity_change' => 8,
        'reason' => 'restock',
        'invoice_id' => null,
        'user_id' => $creator->id,
        'notes' => 'Weekly stock delivery',
    ]);
    $restockMovement->forceFill([
        'created_at' => Carbon::create(2026, 4, 3, 8, 0, 0, 'UTC'),
        'updated_at' => Carbon::create(2026, 4, 3, 8, 0, 0, 'UTC'),
    ])->saveQuietly();

    $saleMovement = StockMovement::create([
        'product_id' => $proteinTub->id,
        'quantity_change' => -2,
        'reason' => 'sale',
        'invoice_id' => $merchandiseInvoice->id,
        'user_id' => $creator->id,
        'notes' => 'Merchandise sale',
    ]);
    $saleMovement->forceFill([
        'created_at' => Carbon::create(2026, 4, 12, 10, 15, 0, 'UTC'),
        'updated_at' => Carbon::create(2026, 4, 12, 10, 15, 0, 'UTC'),
    ])->saveQuietly();

    $adjustmentMovement = StockMovement::create([
        'product_id' => $shakerBottle->id,
        'quantity_change' => -1,
        'reason' => 'adjustment',
        'invoice_id' => null,
        'user_id' => $creator->id,
        'notes' => 'Damaged bottle removed',
    ]);
    $adjustmentMovement->forceFill([
        'created_at' => Carbon::create(2026, 4, 18, 14, 0, 0, 'UTC'),
        'updated_at' => Carbon::create(2026, 4, 18, 14, 0, 0, 'UTC'),
    ])->saveQuietly();

    $response = $this->actingAs($accountant)->get(route('accountant.home'));

    $response->assertOk()
        ->assertSee(__('Accountant reports'))
        ->assertSee('Finance, receivables, merchandise, and stock health in one place.')
        ->assertSee('Receivables Watchlist')
        ->assertSee('Top Merchandise Products This Month')
        ->assertSee('Recent Financial Activity')
        ->assertSee('Birr 1,500.00')
        ->assertSee('Birr 900.00')
        ->assertSee('Birr 2,400.00')
        ->assertSee('Protein Tub')
        ->assertSee('INV-ACCT-1002')
        ->assertSee('Cashier One')
        ->assertSee('revenueTrendChart', false)
        ->assertSee('paymentMixChart', false)
        ->assertSee('stockMovementChart', false)
        ->assertSee('stockHealthChart', false);
});
