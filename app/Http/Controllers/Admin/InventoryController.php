<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')->where('active', true)->orderBy('stock')->paginate(20);
        return view('admin.inventory.index', compact('products'));
    }

    public function adjust(Request $request)
    {
        $data = $request->validate(['product_id' => 'required|exists:products,id', 'quantity' => 'required|integer|min:0', 'type' => 'required|in:in,out,adjust']);
        $product = Product::findOrFail($data['product_id']);
        $qty = (int) $data['quantity'];
        if ($data['type'] === 'out') $qty = -$qty;
        if ($data['type'] === 'adjust') {
            $product->update(['stock' => $qty]);
        } else {
            $product->increment('stock', $qty);
        }
        InventoryTransaction::create(['product_id' => $product->id, 'staff_id' => auth()->id(), 'quantity' => $qty, 'type' => $data['type'], 'note' => 'Điều chỉnh kho']);
        return back()->with('success', 'Cập nhật kho thành công.');
    }
}
