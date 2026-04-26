<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::updateOrCreate(['name' => 'Admin']);
        $cashier = Role::updateOrCreate(['name' => 'Cashier']);
        $supervisor = Role::updateOrCreate(['name' => 'Supervisor']);

        User::updateOrCreate(['email' => 'admin@test.com'], [
            'name' => 'Admin User',
            'password' => Hash::make('password123'),
            'role_id' => $admin->id,
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'cashier@test.com'], [
            'name' => 'Cashier User',
            'password' => Hash::make('password123'),
            'role_id' => $cashier->id,
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'supervisor@test.com'], [
            'name' => 'Supervisor User',
            'password' => Hash::make('password123'),
            'role_id' => $supervisor->id,
            'is_active' => true,
        ]);

        $products = [
            ['barcode' => '480001', 'name' => 'Instant Noodles', 'category' => 'Grocery', 'price' => 18.50, 'stock' => 120, 'active' => true],
            ['barcode' => '480002', 'name' => 'Bottled Water', 'category' => 'Beverage', 'price' => 20.00, 'stock' => 75, 'active' => true],
            ['barcode' => '480003', 'name' => 'Shampoo Sachet', 'category' => 'Personal Care', 'price' => 8.00, 'stock' => 220, 'active' => true],
            ['barcode' => '480004', 'name' => 'Canned Sardines', 'category' => 'Canned Goods', 'price' => 27.75, 'stock' => 8, 'active' => true],
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
