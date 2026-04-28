<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
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
            ['barcode' => '480002', 'name' => 'Bottled Water 500ml', 'category' => 'Beverage', 'price' => 20.00, 'stock' => 75, 'active' => true],
            ['barcode' => '480003', 'name' => 'Shampoo Sachet', 'category' => 'Personal Care', 'price' => 8.00, 'stock' => 220, 'active' => true],
            ['barcode' => '480004', 'name' => 'Canned Sardines', 'category' => 'Canned Goods', 'price' => 27.75, 'stock' => 8, 'active' => true],
            ['barcode' => '480005', 'name' => 'Chocolate Bar', 'category' => 'Snacks', 'price' => 35.00, 'stock' => 0, 'active' => false],
            ['barcode' => '480006', 'name' => 'Premium Rice 1kg', 'category' => 'Grocery', 'price' => 62.00, 'stock' => 55, 'active' => true],
            ['barcode' => '480007', 'name' => 'White Sugar 1kg', 'category' => 'Grocery', 'price' => 78.00, 'stock' => 34, 'active' => true],
            ['barcode' => '480008', 'name' => 'Brown Sugar 1kg', 'category' => 'Grocery', 'price' => 72.00, 'stock' => 24, 'active' => true],
            ['barcode' => '480009', 'name' => 'Cooking Oil 1L', 'category' => 'Grocery', 'price' => 128.00, 'stock' => 42, 'active' => true],
            ['barcode' => '480010', 'name' => 'Soy Sauce 1L', 'category' => 'Condiments', 'price' => 49.50, 'stock' => 63, 'active' => true],
            ['barcode' => '480011', 'name' => 'Vinegar 1L', 'category' => 'Condiments', 'price' => 43.25, 'stock' => 59, 'active' => true],
            ['barcode' => '480012', 'name' => 'Banana Ketchup', 'category' => 'Condiments', 'price' => 38.00, 'stock' => 48, 'active' => true],
            ['barcode' => '480013', 'name' => 'Corned Beef', 'category' => 'Canned Goods', 'price' => 54.75, 'stock' => 32, 'active' => true],
            ['barcode' => '480014', 'name' => 'Tuna Flakes', 'category' => 'Canned Goods', 'price' => 47.50, 'stock' => 28, 'active' => true],
            ['barcode' => '480015', 'name' => 'Evaporated Milk', 'category' => 'Dairy', 'price' => 41.00, 'stock' => 46, 'active' => true],
            ['barcode' => '480016', 'name' => 'Powdered Milk 300g', 'category' => 'Dairy', 'price' => 159.00, 'stock' => 18, 'active' => true],
            ['barcode' => '480017', 'name' => 'Orange Juice 1L', 'category' => 'Beverage', 'price' => 88.00, 'stock' => 27, 'active' => true],
            ['barcode' => '480018', 'name' => 'Iced Tea 1L', 'category' => 'Beverage', 'price' => 64.00, 'stock' => 35, 'active' => true],
            ['barcode' => '480019', 'name' => 'Potato Chips', 'category' => 'Snacks', 'price' => 42.00, 'stock' => 66, 'active' => true],
            ['barcode' => '480020', 'name' => 'Cheese Crackers', 'category' => 'Snacks', 'price' => 28.00, 'stock' => 91, 'active' => true],
            ['barcode' => '480021', 'name' => 'Laundry Detergent 1kg', 'category' => 'Household', 'price' => 145.00, 'stock' => 22, 'active' => true],
            ['barcode' => '480022', 'name' => 'Dishwashing Liquid', 'category' => 'Household', 'price' => 74.00, 'stock' => 38, 'active' => true],
            ['barcode' => '480023', 'name' => 'Bath Soap', 'category' => 'Personal Care', 'price' => 31.50, 'stock' => 73, 'active' => true],
            ['barcode' => '480024', 'name' => 'Toothpaste 100ml', 'category' => 'Personal Care', 'price' => 78.00, 'stock' => 44, 'active' => true],
            ['barcode' => '480025', 'name' => 'Toilet Tissue 4-roll', 'category' => 'Household', 'price' => 95.00, 'stock' => 17, 'active' => true],
            ['barcode' => '480026', 'name' => 'Coffee Sachet 10s', 'category' => 'Beverage', 'price' => 65.00, 'stock' => 85, 'active' => true],
            ['barcode' => '480027', 'name' => 'Creamer Sachet 10s', 'category' => 'Beverage', 'price' => 58.00, 'stock' => 61, 'active' => true],
            ['barcode' => '480028', 'name' => 'Pancit Canton', 'category' => 'Grocery', 'price' => 17.50, 'stock' => 140, 'active' => true],
            ['barcode' => '480029', 'name' => 'Frozen Hotdog 500g', 'category' => 'Frozen', 'price' => 128.50, 'stock' => 15, 'active' => true],
            ['barcode' => '480030', 'name' => 'Frozen Tocino 500g', 'category' => 'Frozen', 'price' => 155.00, 'stock' => 12, 'active' => true],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['barcode' => $product['barcode']],
                $product
            );
        }

        if (Sale::query()->count() === 0) {
            $this->seedSales();
        }

        if (AuditLog::query()->count() === 0) {
            $cashierUser = User::where('email', 'maria@example.com')->first();
            $supervisorUser = User::where('email', 'daniel@example.com')->first();
            $adminUser = User::where('email', 'angela@example.com')->first();

            $logs = [
                ['user_id' => optional($cashierUser)->id, 'action' => 'login', 'details' => 'Maria Cruz logged in for demo shift.', 'logged_at' => now()->subHours(3)],
                ['user_id' => optional($cashierUser)->id, 'action' => 'reprint_receipt', 'details' => 'Receipt for sale #1 was reprinted.', 'logged_at' => now()->subHours(2)],
                ['user_id' => optional($supervisorUser)->id, 'action' => 'supervisor_authorization', 'details' => 'Daniel Reyes approved a cancellation check.', 'logged_at' => now()->subHour()],
                ['user_id' => optional($adminUser)->id, 'action' => 'product_update', 'details' => 'Angela Santos updated demo product inventory.', 'logged_at' => now()->subMinutes(35)],
            ];

            foreach ($logs as $log) {
                AuditLog::create($log);
            }
        }
    }

    private function seedSales(): void
    {
        $cashier = User::where('email', 'maria@example.com')->first();

        if (!$cashier) {
            return;
        }

        $sales = [
            [
                'created_at' => now()->subDays(1)->setTime(10, 15),
                'discount_type' => 'none',
                'items' => [
                    ['barcode' => '480001', 'quantity' => 3],
                    ['barcode' => '480002', 'quantity' => 2],
                    ['barcode' => '480019', 'quantity' => 1],
                ],
            ],
            [
                'created_at' => now()->subHours(6),
                'discount_type' => 'senior',
                'items' => [
                    ['barcode' => '480006', 'quantity' => 1],
                    ['barcode' => '480013', 'quantity' => 2],
                    ['barcode' => '480023', 'quantity' => 2],
                ],
            ],
            [
                'created_at' => now()->subHours(2),
                'discount_type' => 'pwd',
                'items' => [
                    ['barcode' => '480026', 'quantity' => 1],
                    ['barcode' => '480028', 'quantity' => 4],
                    ['barcode' => '480015', 'quantity' => 1],
                ],
            ],
        ];

        foreach ($sales as $saleData) {
            $products = Product::whereIn('barcode', collect($saleData['items'])->pluck('barcode'))->get()->keyBy('barcode');
            $subtotal = 0;
            $lineItems = [];

            foreach ($saleData['items'] as $item) {
                $product = $products->get($item['barcode']);

                if (!$product) {
                    continue;
                }

                $lineTotal = $product->price * $item['quantity'];
                $subtotal += $lineTotal;
                $lineItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'barcode' => $product->barcode,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'line_total' => $lineTotal,
                ];
            }

            if (!$lineItems) {
                continue;
            }

            $discountRate = match ($saleData['discount_type']) {
                'senior', 'pwd' => 0.20,
                'athlete', 'solo' => 0.10,
                default => 0,
            };
            $discountAmount = round($subtotal * $discountRate, 2);
            $total = round($subtotal - $discountAmount, 2);
            $vatableSales = round($total / 1.12, 2);
            $vatAmount = round($total - $vatableSales, 2);

            $sale = Sale::create([
                'cashier_id' => $cashier->id,
                'subtotal' => $subtotal,
                'discount_type' => $saleData['discount_type'],
                'discount_rate' => $discountRate,
                'discount_amount' => $discountAmount,
                'vatable_sales' => $vatableSales,
                'vat_amount' => $vatAmount,
                'total' => $total,
                'status' => 'completed',
                'reprinted' => $saleData['discount_type'] === 'none',
            ]);

            $sale->forceFill([
                'created_at' => $saleData['created_at'],
                'updated_at' => $saleData['created_at'],
            ])->save();

            foreach ($lineItems as $lineItem) {
                $sale->items()->create($lineItem);
            }
        }
    }
}
