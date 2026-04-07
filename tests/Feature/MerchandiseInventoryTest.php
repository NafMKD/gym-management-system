<?php

use App\Mail\LowStockProductsMail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('merchandise checkout creates invoice, payment, and decrements stock', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create(['role' => 'member']);

    $product = Product::factory()->create([
        'stock_quantity' => 10,
        'unit_price' => 50.00,
        'low_stock_threshold' => 5,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->post(route('admin.merchandise.checkout.store'), [
        'user_id' => $member->id,
        'payment_method' => 'cash',
        'lines' => [
            ['product_id' => $product->id, 'quantity' => 2],
        ],
    ]);

    $response->assertRedirect();
    $product->refresh();

    expect($product->stock_quantity)->toBe(8);

    $this->assertDatabaseHas('invoices', [
        'user_id' => $member->id,
        'invoice_source' => 'merchandise',
        'status' => 'paid',
    ]);

    $this->assertDatabaseHas('merchandise_sale_lines', [
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'quantity_change' => -2,
        'reason' => 'sale',
    ]);
});

test('inventory notify low stock command sends mail when products are low', function () {
    config(['inventory.low_stock_notify_enabled' => true]);
    config(['inventory.low_stock_mail_to' => 'stock@example.com']);
    config(['mail.from.address' => 'hello@example.com']);

    Mail::fake();

    Product::factory()->create([
        'name' => 'Low item',
        'stock_quantity' => 2,
        'low_stock_threshold' => 5,
        'is_active' => true,
    ]);

    $this->artisan('inventory:notify-low-stock')->assertSuccessful();

    Mail::assertSent(LowStockProductsMail::class, function (LowStockProductsMail $mail) {
        return $mail->hasTo('stock@example.com') && $mail->products->count() >= 1;
    });
});
