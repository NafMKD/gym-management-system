<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('12345678'),
            'phone' => '1234567890',
            'role' => 'admin',
            'gender' => 'Male',
        ]);

        User::create([
            'name' => 'Trainer User',
            'email' => 'trainer@example.com',
            'password' => Hash::make('12345678'),
            'phone' => '9876543210',
            'role' => 'trainer',
            'gender' => 'Female',
        ]);

        User::create([
            'name' => 'Reception User',
            'email' => 'reception@example.com',
            'password' => Hash::make('12345678'),
            'phone' => '1122334455',
            'role' => 'reception',
            'gender' => 'Female',
        ]);

        User::create([
            'name' => 'Member User',
            'email' => 'member@example.com',
            'password' => Hash::make('12345678'),
            'phone' => '5566778899',
            'role' => 'member',
            'gender' => 'Male',
        ]);
    }
}
