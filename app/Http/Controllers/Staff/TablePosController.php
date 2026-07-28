<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\GameTable;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\TableSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TablePosController extends Controller
{
    public function index(Request $request)
    {
        $tables = GameTable::where('active', true)->orderBy('area')->orderBy('name')->get()->groupBy('area');
        $sessions = TableSession::where('status', 'playing')->with('table', 'orders.items.product')->get()->keyBy('table_id');
        $categories = Category::where('active', true)->orderBy('name')->get();
        $products = Product::with('category')->where('active', true)->where('stock', '>', 0)->orderBy('name')->get();
        return view('staff.table_pos', compact('tables', 'sessions', 'categories', 'products'));
    }

    public function start(GameTable $table)
    {
        if ($table->status != 'available') return back()->withErrors(['Bàn không khả dụng.']);
        DB::transaction(function () use ($table) {
            $table->update(['status' => 'playing']);
            TableSession::create([
                'table_id' => $table->id,
                'staff_id' => auth()->id(),
                'started_at' => now(),
                'hourly_rate' => $table->price_per_hour,
                'status' => 'playing',
            ]);
        });
        return back()->with('success', 'Mở bàn thành công.');
    }

    public function addOrder(Request $request, GameTable $table)
    {
        $data = $request->validate(['items' => 'required|array|min:1', 'items.*.product_id' => 'required|exists:products,id', 'items.*.quantity' => 'required|integer|min:1']);
        $session = TableSession::where('table_id', $table->id)->where('status', 'playing')->first();
        if (!$session) return response()->json(['success' => false, 'message' => 'Bàn chưa được mở.']);

        return DB::transaction(function () use ($data, $session, $table) {
            $order = Order::firstOrCreate(
                ['table_session_id' => $session->id, 'status' => 'pending'],
                ['order_code' => 'ORD-' . now()->format('YmdHisu'), 'staff_id' => auth()->id(), 'subtotal' => 0, 'total' => 0]
            );
            $amount = 0;
            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];
                $total = $product->price * $qty;
                $amount += $total;
                OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => $qty, 'unit_price' => $product->price, 'total_price' => $total]);
                if ($product->track_stock && $qty > 0) {
                    $product->decrement('stock', $qty);
                    \App\Models\InventoryTransaction::create(['product_id' => $product->id, 'staff_id' => auth()->id(), 'quantity' => -$qty, 'type' => 'out', 'note' => 'Bàn ' . $table->name]);
                }
            }
            $order->increment('subtotal', $amount);
            $order->increment('total', $amount);
            $session->increment('products_amount', $amount);
            $session->increment('total_amount', $amount);
            return response()->json(['success' => true, 'total' => $session->total_amount]);
        });
    }

    public function close(Request $request, GameTable $table)
    {
        $session = TableSession::where('table_id', $table->id)->where('status', 'playing')->first();
        if (!$session) return back()->withErrors(['Bàn chưa được mở.']);

        $paymentMethod = $request->input('payment_method', 'cash');
        $ended = now();
        $minutes = $session->started_at->diffInMinutes($ended);
        $hours = ceil($minutes / 60);
        $tableAmount = $hours * $session->hourly_rate;
        $total = $tableAmount + $session->products_amount;

        DB::transaction(function () use ($session, $ended, $minutes, $tableAmount, $total, $paymentMethod, $table) {
            $session->update(['ended_at' => $ended, 'duration_minutes' => $minutes, 'table_amount' => $tableAmount, 'total_amount' => $total, 'status' => 'completed']);
            $table->update(['status' => 'available']);
            Payment::create(['payable_id' => $session->id, 'payable_type' => TableSession::class, 'staff_id' => auth()->id(), 'amount' => $total, 'method' => $paymentMethod, 'paid_at' => now()]);
            $order = Order::where('table_session_id', $session->id)->where('status', 'pending')->first();
            if ($order) $order->update(['status' => 'paid', 'paid_at' => now(), 'payment_method' => $paymentMethod]);
        });

        return back()->with('success', 'Đã kết thúc bàn. Tổng: ' . number_format($total) . 'đ');
    }
}
