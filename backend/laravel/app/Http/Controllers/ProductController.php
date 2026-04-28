<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q', $request->query('search', null));
        $products = Product::when($q, function ($query, $q) {
            $query->where('name', 'like', "%{$q}%")
                ->orWhere('barcode', 'like', "%{$q}%")
                ->orWhere('category', 'like', "%{$q}%");
        })->orderByDesc('active')->orderBy('name')->get();

        return response()->json($products);
    }

    public function barcode(string $barcode)
    {
        $product = Product::where('barcode', trim($barcode))->firstOrFail();

        return response()->json($product);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'barcode' => ['required', 'string', 'max:255', 'unique:products,barcode'],
            'name' => 'required|string',
            'category' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product = Product::create($data + ['active' => true]);

        return response()->json($product, 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $data = $request->validate([
            'barcode' => ['required', 'string', 'max:255', Rule::unique('products', 'barcode')->ignore($product->id)],
            'name' => 'required|string',
            'category' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'active' => 'sometimes|boolean',
        ]);

        $product->update($data);

        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->active = false;
        $product->save();

        return response()->json(['message' => 'Product deactivated']);
    }
}
