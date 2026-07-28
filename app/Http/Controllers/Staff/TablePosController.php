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
        return view('staff.pos', compact('tables', 'sessions', 'categories', 'products'));
    }

    public function start(GameTable $table)
    {
        if ($table->status != 'available') return back()->withErrors(['Bàn không khả dụng.']);

        $serviceProduct = Product::where('name', 'Tiền giờ')->first();
        if (!$serviceProduct) return back()->withErrors(['Chưa có sản phẩm "Tiền giờ" trong hệ thống.']);

        DB::transaction(function () use ($table, $serviceProduct) {
            $table->update(['status' => 'playing']);
            $session = TableSession::create([
                'table_id' => $table->id,
                'staff_id' => auth()->id(),
                'started_at' => now(),
                'hourly_rate' => $serviceProduct->price,
                'status' => 'playing',
            ]);

            $order = Order::create([
                'table_session_id' => $session->id,
                'order_code' => 'ORD-' . now()->format('YmdHisu'),
                'staff_id' => auth()->id(),
                'subtotal' => 0,
                'total' => 0,
                'status' => 'pending',
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $serviceProduct->id,
                'quantity' => 0,
                'unit_price' => $serviceProduct->price / 60,
                'total_price' => 0,
                'note' => 'Tiền giờ bàn ' . $table->name,
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
        $serviceProduct = Product::where('name', 'Tiền giờ')->first();
        $pricePerMinute = $serviceProduct ? ($serviceProduct->price / 60) : ($session->hourly_rate / 60);
        $tableAmount = round($minutes * $pricePerMinute);
        $total = $tableAmount + $session->products_amount;

        DB::transaction(function () use ($session, $ended, $minutes, $tableAmount, $total, $paymentMethod, $table, $serviceProduct, $pricePerMinute) {
            $order = Order::where('table_session_id', $session->id)->where('status', 'pending')->first();

            if ($order && $serviceProduct) {
                $serviceItem = $order->items()->where('product_id', $serviceProduct->id)->first();
                if ($serviceItem) {
                    $serviceItem->update(['quantity' => $minutes, 'unit_price' => $pricePerMinute, 'total_price' => $tableAmount]);
                } else {
                    $order->items()->create([
                        'product_id' => $serviceProduct->id,
                        'quantity' => $minutes,
                        'unit_price' => $pricePerMinute,
                        'total_price' => $tableAmount,
                        'note' => 'Tiền giờ bàn ' . $table->name,
                    ]);
                }
                $order->update(['subtotal' => $order->items()->sum('total_price'), 'total' => $order->items()->sum('total_price')]);
            }

            $session->update(['ended_at' => $ended, 'duration_minutes' => $minutes, 'table_amount' => $tableAmount, 'total_amount' => $total, 'status' => 'completed']);
            $table->update(['status' => 'available']);
            Payment::create(['payable_id' => $session->id, 'payable_type' => TableSession::class, 'staff_id' => auth()->id(), 'amount' => $total, 'method' => $paymentMethod, 'paid_at' => now()]);
            if ($order) $order->update(['status' => 'paid', 'paid_at' => now(), 'payment_method' => $paymentMethod]);
        });

        return redirect()->route('staff.pos.bill', $session->id)->with('success', 'Đã kết thúc bàn. Tổng: ' . number_format($total) . 'đ');
    }

    public function bill(TableSession $session)
    {
        $session->load('table', 'orders.items.product', 'staff', 'payments');
        return view('staff.bill', compact('session'));
    }
}
