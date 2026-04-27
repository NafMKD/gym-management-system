<?php

use App\Models\Package;
use App\Models\User;

test('package detail page shows granted days and description', function () {
    $admin = User::factory()->admin()->create();
    $package = Package::create([
        'name' => 'Semi Annual',
        'price' => 2400.00,
        'duration' => 180,
        'granted_days' => 150,
        'description' => 'Best for longer training plans.',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.packages.view', $package));

    $response->assertOk();
    $response->assertSee('Granted Days');
    $response->assertSee((string) $package->granted_days);
    $response->assertSee($package->description);
});
