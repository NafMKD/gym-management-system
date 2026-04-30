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
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'phone' => '0911111111',
            'role' => 'admin',
            'gender' => 'Male',
        ]);

        User::create([
            'first_name' => 'Trainer',
            'last_name' => 'User',
            'email' => 'trainer@gmail.com',
            'password' => Hash::make('12345678'),
            'phone' => '0922222222',
            'role' => 'trainer',
            'gender' => 'Female',
        ]);

        User::create([
            'first_name' => 'Accountant',
            'last_name' => 'User',
            'email' => 'accountant@gmail.com',
            'password' => Hash::make('12345678'),
            'phone' => '0955555555',
            'role' => 'accountant',
            'gender' => 'Female',
        ]);

        User::create([
            'first_name' => 'Reception',
            'last_name' => 'User',
            'email' => 'reception@gmail.com',
            'password' => Hash::make('12345678'),
            'phone' => '0933333333',
            'role' => 'reception',
            'gender' => 'Female',
        ]);

        User::create([
            'first_name' => 'Member',
            'last_name' => 'User',
            'email' => 'member@gmail.com',
            'password' => Hash::make('12345678'),
            'phone' => '0944444444',
            'role' => 'member',
            'gender' => 'Male',
        ]);
    }
}
