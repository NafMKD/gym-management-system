<?php

use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('invoice send email queues mail to member', function () {
    Mail::fake();

    $admin = User::factory()->admin()->create();
    $member = User::factory()->create(['email' => 'member-billing@test.com']);
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
        'invoice_number' => 'INV-26-99999',
        'amount' => 100.00,
        'status' => 'unpaid',
        'issued_date' => now(),
        'due_date' => now()->addDays(7),
    ]);

    $this->actingAs($admin)
        ->from(route('admin.invoices.view', $invoice))
        ->post(route('admin.invoices.send.email', $invoice))
        ->assertRedirect();

    Mail::assertSent(InvoiceMail::class, fn (InvoiceMail $m) => $m->hasTo('member-billing@test.com'));
});
