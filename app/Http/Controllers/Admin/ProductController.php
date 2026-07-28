<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');
        if ($request->filled('q')) $query->where('name', 'like', '%' . $request->q . '%');
        if ($request->filled('category')) $query->where('category_id', $request->category);
        $products = $query->orderBy('name')->paginate(20);
        $categories = Category::where('active', true)->get();
        return view('admin.products.index', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'category_id' => 'nullable|exists:categories,id', 'unit' => 'required|string|max:50', 'price' => 'required|numeric|min:0', 'cost' => 'nullable|numeric|min:0', 'stock' => 'nullable|integer|min:0', 'min_stock' => 'nullable|integer|min:0']);
        Product::create($data);
        return back()->with('success', 'Thêm sản phẩm thành công.');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'category_id' => 'nullable|exists:categories,id', 'unit' => 'required|string|max:50', 'price' => 'required|numeric|min:0', 'cost' => 'nullable|numeric|min:0', 'stock' => 'nullable|integer|min:0', 'min_stock' => 'nullable|integer|min:0', 'active' => 'boolean']);
        $data['active'] = $request->boolean('active', true);
        $product->update($data);
        return back()->with('success', 'Cập nhật sản phẩm thành công.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success', 'Xóa sản phẩm thành công.');
    }
}
