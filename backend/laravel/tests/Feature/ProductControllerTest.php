<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_can_be_found_by_exact_barcode(): void
    {
        $product = Product::create([
            'barcode' => '480001',
            'name' => 'Instant Noodles',
            'category' => 'Grocery',
            'price' => 18.50,
            'stock' => 120,
            'active' => true,
        ]);

        $this->getJson('/api/products/barcode/480001')
            ->assertOk()
            ->assertJsonPath('id', $product->id)
            ->assertJsonPath('barcode', '480001')
            ->assertJsonPath('name', 'Instant Noodles');
    }

    public function test_unknown_barcode_returns_not_found(): void
    {
        $this->getJson('/api/products/barcode/NOPE')
            ->assertNotFound();
    }
}
