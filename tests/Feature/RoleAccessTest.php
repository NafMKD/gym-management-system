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
    $this->actingAs($reception)->get(route('admin.users.list'))->assertOk();
    $this->actingAs($reception)->get(route('admin.merchandise.checkout'))->assertOk();

    $this->actingAs($reception)->get(route('admin.home'))->assertRedirect(route('reception.home'));
    $this->actingAs($reception)->get(route('admin.invoices.list'))->assertRedirect(route('reception.home'));
    $this->actingAs($reception)->get(route('admin.payments.list'))->assertRedirect(route('reception.home'));
});

test('trainer and member can open their portal homes', function () {
    $trainer = User::factory()->create(['role' => 'trainer']);
    $member = User::factory()->create(['role' => 'member']);

    $this->actingAs($trainer)->get(route('trainer.home'))->assertOk();
    $this->actingAs($trainer)->get(route('trainer.profile.edit'))->assertOk();

    $this->actingAs($member)->get(route('member.home'))->assertOk();
});
