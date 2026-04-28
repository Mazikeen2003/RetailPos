<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SaleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_cannot_sell_more_than_available_stock(): void
    {
        Sanctum::actingAs(User::factory()->create());

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
        Sanctum::actingAs(User::factory()->create());

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
            ->assertJsonPath('vatable_sales', 142.86)
            ->assertJsonPath('vat_amount', 17.14)
            ->assertJsonPath('total', 160);

        $this->assertSame(3, $product->fresh()->stock);
    }
}
