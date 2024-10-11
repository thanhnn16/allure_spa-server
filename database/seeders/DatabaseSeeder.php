<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'id' => Str::uuid(),
            'phone_number' => '0123456789',
            'email' => 'admin@example.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'full_name' => 'Admin User',
        ]);

        User::create([
            'id' => Str::uuid(),
            'phone_number' => '0987654321',
            'email' => 'user@example.com',
            'password' => Hash::make('12345678'),
            'role' => 'user',
            'full_name' => 'Normal User',
        ]);

        User::create([
            'id' => Str::uuid(),
            'phone_number' => '0369852741',
            'email' => 'staff@example.com',
            'password' => Hash::make('12345678'),
            'role' => 'staff',
            'full_name' => 'Staff User',
        ]);
    }
}
