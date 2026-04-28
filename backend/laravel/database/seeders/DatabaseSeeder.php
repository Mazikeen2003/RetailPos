<?php

namespace Database\Seeders;

use App\Models\Product;
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

        $products = [
            ['barcode' => '480001', 'name' => 'Instant Noodles', 'category' => 'Grocery', 'price' => 18.50, 'stock' => 120, 'active' => true],
            ['barcode' => '480002', 'name' => 'Bottled Water', 'category' => 'Beverage', 'price' => 20.00, 'stock' => 75, 'active' => true],
            ['barcode' => '480003', 'name' => 'Shampoo Sachet', 'category' => 'Personal Care', 'price' => 8.00, 'stock' => 220, 'active' => true],
            ['barcode' => '480004', 'name' => 'Canned Sardines', 'category' => 'Canned Goods', 'price' => 27.75, 'stock' => 43, 'active' => true],
            ['barcode' => '480005', 'name' => 'Chocolate Bar', 'category' => 'Snacks', 'price' => 35.00, 'stock' => 0, 'active' => false],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['barcode' => $product['barcode']],
                $product
            );
        }
    }
}
