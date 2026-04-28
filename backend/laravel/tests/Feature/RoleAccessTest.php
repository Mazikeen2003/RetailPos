<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_products_but_cashier_cannot(): void
    {
        $admin = $this->userWithRole('Admin');
        $cashier = $this->userWithRole('Cashier');

        Sanctum::actingAs($cashier);

        $this->postJson('/api/products', [
            'barcode' => 'ROLE-001',
            'name' => 'Cashier Blocked Product',
            'category' => 'Test',
            'price' => 12.5,
            'stock' => 5,
            'active' => true,
        ])->assertForbidden();

        Sanctum::actingAs($admin);

        $this->postJson('/api/products', [
            'barcode' => 'ROLE-002',
            'name' => 'Admin Product',
            'category' => 'Test',
            'price' => 12.5,
            'stock' => 5,
            'active' => true,
        ])->assertCreated();
    }

    public function test_supervisor_can_view_audit_logs_but_cashier_cannot(): void
    {
        $cashier = $this->userWithRole('Cashier');
        $supervisor = $this->userWithRole('Supervisor');

        Sanctum::actingAs($cashier);
        $this->getJson('/api/audit-logs')->assertForbidden();

        Sanctum::actingAs($supervisor);
        $this->getJson('/api/audit-logs')->assertOk();
    }

    public function test_cashier_can_search_products(): void
    {
        $cashier = $this->userWithRole('Cashier');

        Product::create([
            'barcode' => '480999',
            'name' => 'Quick Search Rice',
            'category' => 'Grocery',
            'price' => 75,
            'stock' => 8,
            'active' => true,
        ]);

        Sanctum::actingAs($cashier);

        $this->getJson('/api/products?search=rice')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Quick Search Rice']);
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::create(['name' => $roleName]);

        return User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }
}
