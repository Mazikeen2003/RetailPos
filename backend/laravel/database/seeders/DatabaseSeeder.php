<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create demo users with roles for quick testing (idempotent)
        User::updateOrCreate(
            ['email' => 'maria@example.com'],
            ['name' => 'Maria Cruz', 'password' => bcrypt('1234'), 'role' => 'Cashier']
        );

        User::updateOrCreate(
            ['email' => 'daniel@example.com'],
            ['name' => 'Daniel Reyes', 'password' => bcrypt('1234'), 'role' => 'Supervisor']
        );

        User::updateOrCreate(
            ['email' => 'angela@example.com'],
            ['name' => 'Angela Santos', 'password' => bcrypt('1234'), 'role' => 'Administrator']
        );
    }
}
