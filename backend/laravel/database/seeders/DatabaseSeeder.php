<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::create(['name' => 'Admin']);
        $cashier = Role::create(['name' => 'Cashier']);
        $supervisor = Role::create(['name' => 'Supervisor']);

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role_id' => $admin->id,
        ]);

        User::create([
            'name' => 'Cashier User',
            'email' => 'cashier@test.com',
            'password' => Hash::make('password123'),
            'role_id' => $cashier->id,
        ]);

        User::create([
            'name' => 'Supervisor User',
            'email' => 'supervisor@test.com',
            'password' => Hash::make('password123'),
            'role_id' => $supervisor->id,
        ]);
    }
}