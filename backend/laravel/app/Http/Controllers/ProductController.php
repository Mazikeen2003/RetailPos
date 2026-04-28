<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private function ensureAdmin(Request $request): void
    {
        abort_unless(optional($request->user()->role)->name === 'Admin', 403, 'Admin access required.');
    }

    public function index(Request $request)
    {
        $search = $request->query('search', $request->query('q'));

        $products = Product::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%");
        })->latest()->get();

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'barcode' => 'required|string|unique:products,barcode',
            'name' => 'required|string',
            'category' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'active' => 'boolean',
        ]);

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product
        ], 201);
    }

    public function show(string $id)
    {
        $product = Product::findOrFail($id);

        return response()->json($product);
    }

    public function barcode(string $barcode)
    {
        $product = Product::where('barcode', $barcode)->firstOrFail();

        return response()->json($product);
    }

    public function update(Request $request, string $id)
    {
        $this->ensureAdmin($request);

        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'barcode' => 'sometimes|required|string|unique:products,barcode,' . $id,
            'name' => 'sometimes|required|string',
            'category' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'active' => 'boolean',
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product
        ]);
    }

    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->update(['active' => false]);

        return response()->json([
            'message' => 'Product deactivated successfully',
            'product' => $product,
        ]);
    }
}
