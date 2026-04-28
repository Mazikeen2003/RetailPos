<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SaleControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingCashier(): User
    {
        $cashier = $this->userWithRole('Cashier');

        Sanctum::actingAs($cashier);

        return $cashier;
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::create(['name' => $roleName]);

        return User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    public function test_sale_cannot_sell_more_than_available_stock(): void
    {
        $this->actingCashier();

        $product = Product::create([
            'barcode' => 'SALE-001',
            'name' => 'Limited Item',
            'category' => 'Test',
            'price' => 50,
            'stock' => 2,
            'active' => true,
        ]);

        $response = $this->postJson('/api/sales', [
            'items' => [
                ['id' => $product->id, 'qty' => 3, 'price' => 1],
            ],
            'discount' => 'none',
        ]);

        $response->assertUnprocessable();
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_sale_uses_database_price_and_deducts_stock(): void
    {
        $this->actingCashier();

        $product = Product::create([
            'barcode' => 'SALE-002',
            'name' => 'Priced Item',
            'category' => 'Test',
            'price' => 100,
            'stock' => 5,
            'active' => true,
        ]);

        $response = $this->postJson('/api/sales', [
            'items' => [
                ['id' => $product->id, 'qty' => 2, 'price' => 1],
            ],
            'discount' => 'senior',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('subtotal', 200)
            ->assertJsonPath('discount_amount', 40)
            ->assertJsonPath('total', 160);

        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_non_cashier_cannot_create_sale(): void
    {
        $admin = $this->userWithRole('Admin');

        Sanctum::actingAs($admin);

        $product = Product::create([
            'barcode' => 'SALE-003',
            'name' => 'Role Locked Item',
            'category' => 'Test',
            'price' => 25,
            'stock' => 10,
            'active' => true,
        ]);

        $this->postJson('/api/sales', [
            'items' => [
                ['id' => $product->id, 'qty' => 1],
            ],
            'discount' => 'none',
        ])->assertForbidden();
    }

    public function test_supervisor_can_void_completed_receipt_and_restore_inventory(): void
    {
        $cashier = $this->userWithRole('Cashier');
        $supervisor = $this->userWithRole('Supervisor');

        $product = Product::create([
            'barcode' => 'VOID-001',
            'name' => 'Voidable Item',
            'category' => 'Test',
            'price' => 40,
            'stock' => 3,
            'active' => true,
        ]);

        $sale = Sale::create([
            'cashier_id' => $cashier->id,
            'subtotal' => 80,
            'discount_type' => 'none',
            'discount_rate' => 0,
            'discount_amount' => 0,
            'vatable_sales' => 71.43,
            'vat_amount' => 8.57,
            'total' => 80,
            'status' => 'completed',
        ]);

        $sale->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'barcode' => $product->barcode,
            'price' => $product->price,
            'quantity' => 2,
            'line_total' => 80,
        ]);

        Sanctum::actingAs($supervisor);

        $this->postJson("/api/sales/{$sale->id}/void", [
            'reason' => 'Customer returned full receipt.',
        ])
            ->assertOk()
            ->assertJsonPath('sale.status', 'voided')
            ->assertJsonPath('sale.voided_by_id', $supervisor->id);

        $this->assertSame(5, $product->fresh()->stock);
        $this->assertSame('voided', $sale->fresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $supervisor->id,
            'action' => 'receipt_voided',
        ]);
    }

    public function test_receipt_cannot_be_voided_twice(): void
    {
        $cashier = $this->userWithRole('Cashier');
        $supervisor = $this->userWithRole('Supervisor');

        $product = Product::create([
            'barcode' => 'VOID-002',
            'name' => 'Single Restore Item',
            'category' => 'Test',
            'price' => 10,
            'stock' => 1,
            'active' => true,
        ]);

        $sale = Sale::create([
            'cashier_id' => $cashier->id,
            'subtotal' => 10,
            'discount_type' => 'none',
            'discount_rate' => 0,
            'discount_amount' => 0,
            'vatable_sales' => 8.93,
            'vat_amount' => 1.07,
            'total' => 10,
            'status' => 'completed',
        ]);

        $sale->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'barcode' => $product->barcode,
            'price' => $product->price,
            'quantity' => 1,
            'line_total' => 10,
        ]);

        Sanctum::actingAs($supervisor);

        $this->postJson("/api/sales/{$sale->id}/void", [
            'reason' => 'Initial valid void.',
        ])->assertOk();

        $this->postJson("/api/sales/{$sale->id}/void", [
            'reason' => 'Trying to void twice.',
        ])->assertUnprocessable();

        $this->assertSame(2, $product->fresh()->stock);
    }
}
