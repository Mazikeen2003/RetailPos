<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q', $request->query('search', null));
        $products = Product::when($q, function ($query, $q) {
            $query->where('name', 'like', "%{$q}%")->orWhere('barcode', 'like', "%{$q}%");
        })->get();

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'barcode' => 'required|string',
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
            'barcode' => 'required|string',
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
