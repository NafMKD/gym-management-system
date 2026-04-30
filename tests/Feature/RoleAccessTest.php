<?php

use App\Models\User;

test('admin can open dashboard and invoices', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('admin.home'))->assertOk();
    $this->actingAs($admin)->get(route('admin.invoices.list'))->assertOk();
});

test('reception can open desk routes but not financial admin routes', function () {
    $reception = User::factory()->create(['role' => 'reception']);

    $this->actingAs($reception)->get(route('reception.home'))->assertOk()->assertSee(__('Front desk'));
    $this->actingAs($reception)->get(route('admin.users.list'))->assertOk()->assertSee(__('Front desk'));
    $this->actingAs($reception)->get(route('admin.merchandise.checkout'))->assertOk();
    $this->actingAs($reception)->get(route('admin.merchandise.history'))->assertOk();

    $this->actingAs($reception)->get(route('admin.home'))->assertRedirect(route('reception.home'));
    $this->actingAs($reception)->get(route('admin.invoices.list'))->assertRedirect(route('reception.home'));
    $this->actingAs($reception)->get(route('admin.payments.list'))->assertRedirect(route('reception.home'));
});

test('accountant can open reporting routes but not write routes', function () {
    $accountant = User::factory()->accountant()->create();
    $member = User::factory()->create();

    $this->actingAs($accountant)->get(route('dashboard'))->assertRedirect(route('accountant.home'));
    $this->actingAs($accountant)->get(route('accountant.home'))->assertOk()->assertSee(__('Accountant reports'));
    $this->actingAs($accountant)->get(route('admin.users.list'))->assertOk()->assertSee(__('User Reports'));
    $this->actingAs($accountant)->get(route('admin.users.view', $member))->assertOk();
    $this->actingAs($accountant)->get(route('admin.invoices.list'))->assertOk();
    $this->actingAs($accountant)->get(route('admin.invoices.print'))->assertOk();
    $this->actingAs($accountant)->get(route('admin.invoices.export.csv'))->assertOk();
    $this->actingAs($accountant)->get(route('admin.payments.list'))->assertOk();
    $this->actingAs($accountant)->get(route('admin.payments.print'))->assertOk();
    $this->actingAs($accountant)->get(route('admin.payments.revenue.list'))->assertOk();
    $this->actingAs($accountant)->get(route('admin.payments.revenue.print'))->assertOk();
    $this->actingAs($accountant)->get(route('admin.products.list'))->assertOk();
    $this->actingAs($accountant)->get(route('admin.products.print'))->assertOk();
    $this->actingAs($accountant)->get(route('admin.merchandise.history'))->assertOk();
    $this->actingAs($accountant)->get(route('admin.merchandise.history.print'))->assertOk();

    $this->actingAs($accountant)->get(route('admin.home'))->assertRedirect(route('accountant.home'));
    $this->actingAs($accountant)->get(route('admin.users.add'))->assertRedirect(route('accountant.home'));
    $this->actingAs($accountant)->get(route('admin.products.add'))->assertRedirect(route('accountant.home'));
    $this->actingAs($accountant)->get(route('admin.merchandise.checkout'))->assertRedirect(route('accountant.home'));
    $this->actingAs($accountant)->get(route('admin.staffs.list'))->assertRedirect(route('accountant.home'));
});

test('trainer and member can open their portal homes', function () {
    $trainer = User::factory()->create(['role' => 'trainer']);
    $member = User::factory()->create(['role' => 'member']);

    $this->actingAs($trainer)->get(route('trainer.home'))->assertOk();
    $this->actingAs($trainer)->get(route('trainer.profile.edit'))->assertOk();

    $this->actingAs($member)->get(route('member.home'))->assertOk();
});
