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

        $users = [
            ['name' => 'Maria Cruz', 'email' => 'maria@example.com', 'password' => '1234', 'role_id' => $cashier->id],
            ['name' => 'Daniel Reyes', 'email' => 'daniel@example.com', 'password' => '1234', 'role_id' => $supervisor->id],
            ['name' => 'Angela Santos', 'email' => 'angela@example.com', 'password' => '1234', 'role_id' => $admin->id],
            ['name' => 'Admin User', 'email' => 'admin@test.com', 'password' => 'password123', 'role_id' => $admin->id],
            ['name' => 'Cashier User', 'email' => 'cashier@test.com', 'password' => 'password123', 'role_id' => $cashier->id],
            ['name' => 'Supervisor User', 'email' => 'supervisor@test.com', 'password' => 'password123', 'role_id' => $supervisor->id],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(['email' => $user['email']], [
                'name' => $user['name'],
                'password' => Hash::make($user['password']),
                'role_id' => $user['role_id'],
                'is_active' => true,
            ]);
        }

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
