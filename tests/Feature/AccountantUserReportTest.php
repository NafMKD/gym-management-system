<?php

use App\Models\User;
use Carbon\Carbon;

test('accountant user report is read only and filters by creation date', function () {
    $accountant = User::factory()->accountant()->create();

    $inRangeUser = User::factory()->create([
        'first_name' => 'Filter',
        'last_name' => 'Match',
        'role' => 'member',
        'email' => 'filter-match@example.com',
        'phone' => '0912345678',
    ]);
    $inRangeUser->forceFill([
        'created_at' => Carbon::create(2026, 4, 15, 8, 0, 0, 'Africa/Addis_Ababa'),
        'updated_at' => Carbon::create(2026, 4, 15, 8, 0, 0, 'Africa/Addis_Ababa'),
    ])->saveQuietly();

    $outOfRangeUser = User::factory()->create([
        'first_name' => 'Outside',
        'last_name' => 'Range',
        'role' => 'member',
        'email' => 'outside-range@example.com',
        'phone' => '0998765432',
    ]);
    $outOfRangeUser->forceFill([
        'created_at' => Carbon::create(2026, 4, 20, 10, 0, 0, 'Africa/Addis_Ababa'),
        'updated_at' => Carbon::create(2026, 4, 20, 10, 0, 0, 'Africa/Addis_Ababa'),
    ])->saveQuietly();

    $page = $this->actingAs($accountant)->get(route('admin.users.list'));
    $page->assertOk()
        ->assertDontSee(__('Add User'));

    $response = $this->actingAs($accountant)->get(route('admin.users.list.data', [
        'draw' => 1,
        'start' => 0,
        'length' => 10,
        'created_from' => '2026-04-15',
        'created_to' => '2026-04-15',
        'search' => 'Filter',
    ]));

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $inRangeUser->id);

    expect($response->json('data.0.action'))->toContain('View');
    expect($response->json('data.0.action'))->not->toContain('Edit');
    expect($response->json('data.0.action'))->not->toContain('Delete');

    $detail = $this->actingAs($accountant)->get(route('admin.users.view', $inRangeUser));
    $detail->assertOk()
        ->assertDontSee('Membership History')
        ->assertDontSee('Attendance History');
});

test('accountant user report export and print respect active filters', function () {
    $accountant = User::factory()->accountant()->create();

    $matchingUser = User::factory()->create([
        'first_name' => 'Export',
        'last_name' => 'Target',
        'role' => 'member',
        'email' => 'export-target@example.com',
        'phone' => '0977777777',
    ]);
    $matchingUser->forceFill([
        'created_at' => Carbon::create(2026, 4, 10, 9, 30, 0, 'Africa/Addis_Ababa'),
        'updated_at' => Carbon::create(2026, 4, 10, 9, 30, 0, 'Africa/Addis_Ababa'),
    ])->saveQuietly();

    $otherUser = User::factory()->create([
        'first_name' => 'Hidden',
        'last_name' => 'User',
        'role' => 'member',
        'email' => 'hidden-user@example.com',
        'phone' => '0966666666',
    ]);
    $otherUser->forceFill([
        'created_at' => Carbon::create(2026, 4, 18, 11, 0, 0, 'Africa/Addis_Ababa'),
        'updated_at' => Carbon::create(2026, 4, 18, 11, 0, 0, 'Africa/Addis_Ababa'),
    ])->saveQuietly();

    $export = $this->actingAs($accountant)->get(route('admin.users.export.csv', [
        'created_from' => '2026-04-10',
        'created_to' => '2026-04-10',
        'search' => 'Export',
    ]));

    $export->assertOk();
    $csv = $export->streamedContent();

    expect($csv)->toContain('Export Target');
    expect($csv)->not->toContain('Hidden User');

    $print = $this->actingAs($accountant)->get(route('admin.users.print', [
        'created_from' => '2026-04-10',
        'created_to' => '2026-04-10',
        'search' => 'Export',
    ]));

    $print->assertOk()
        ->assertSee('Export Target')
        ->assertDontSee('Hidden User')
        ->assertSee(__('Total users'));
});
