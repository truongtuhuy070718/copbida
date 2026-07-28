<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where('active', true)->orderBy('name')->get();
        $products = Product::with('category')->where('active', true)->where('stock', '>', 0)->orderBy('name')->get();
        return view('staff.pos', compact('categories', 'products'));
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,transfer,card',
        ]);

        return DB::transaction(function () use ($data) {
            $subtotal = 0;
            $orderCode = 'ORD-' . now()->format('YmdHisu');
            $order = Order::create(['order_code' => $orderCode, 'staff_id' => auth()->id(), 'status' => 'pending', 'subtotal' => 0, 'total' => 0, 'payment_method' => $data['payment_method']]);

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];
                $total = $product->price * $qty;
                $subtotal += $total;
                OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => $qty, 'unit_price' => $product->price, 'total_price' => $total]);
                if ($product->track_stock && $qty > 0) {
                    $product->decrement('stock', $qty);
                    \App\Models\InventoryTransaction::create(['product_id' => $product->id, 'staff_id' => auth()->id(), 'quantity' => -$qty, 'type' => 'out', 'note' => 'Bán qua POS']);
                }
            }

            $order->update(['subtotal' => $subtotal, 'total' => $subtotal, 'status' => 'paid', 'paid_at' => now()]);
            Payment::create(['payable_id' => $order->id, 'payable_type' => Order::class, 'staff_id' => auth()->id(), 'amount' => $subtotal, 'method' => $data['payment_method'], 'paid_at' => now()]);

            return response()->json(['success' => true, 'order_code' => $order->order_code, 'total' => $subtotal]);
        });
    }
}
