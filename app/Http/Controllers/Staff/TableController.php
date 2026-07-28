<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\GameTable;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\TableSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TableController extends Controller
{
    public function index(Request $request)
    {
        $tables = GameTable::where('active', true)->orderBy('area')->orderBy('name')->get()->groupBy('area');
        $sessions = TableSession::where('status', 'playing')->with('table')->get()->keyBy('table_id');
        $products = Product::where('active', true)->orderBy('name')->get();
        return view('staff.tables', compact('tables', 'sessions', 'products'));
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

    public function order(Request $request, GameTable $table)
    {
        $data = $request->validate(['product_id' => 'required|exists:products,id', 'quantity' => 'required|integer|min:1']);
        $product = Product::findOrFail($data['product_id']);
        $session = $table->activeSession();
        if (!$session) return back()->withErrors(['Bàn chưa được mở.']);

        $amount = $product->price * $data['quantity'];
        $session->increment('products_amount', $amount);
        $session->increment('total_amount', $amount);

        $order = Order::firstOrCreate(
            ['table_session_id' => $session->id, 'status' => 'pending'],
            ['order_code' => 'ORD-' . now()->format('YmdHisu'), 'staff_id' => auth()->id(), 'subtotal' => 0, 'total' => 0]
        );
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $data['quantity'],
            'unit_price' => $product->price,
            'total_price' => $amount,
        ]);
        $order->increment('subtotal', $amount);
        $order->increment('total', $amount);

        if ($product->track_stock && $data['quantity'] > 0) {
            $product->decrement('stock', $data['quantity']);
            InventoryTransaction::create(['product_id' => $product->id, 'staff_id' => auth()->id(), 'quantity' => -$data['quantity'], 'type' => 'out', 'note' => 'Bán tại bàn ' . $table->name]);
        }

        return back()->with('success', 'Đã thêm món.');
    }

    public function close(Request $request, GameTable $table)
    {
        $session = $table->activeSession();
        if (!$session) return back()->withErrors(['Bàn chưa được mở.']);

        $paymentMethod = $request->input('payment_method', 'cash');
        $ended = now();
        $minutes = $session->started_at->diffInMinutes($ended);
        $hours = ceil($minutes / 60);
        $tableAmount = $hours * $session->hourly_rate;
        $total = $tableAmount + $session->products_amount;

        DB::transaction(function () use ($session, $ended, $minutes, $tableAmount, $total, $paymentMethod) {
            $session->update(['ended_at' => $ended, 'duration_minutes' => $minutes, 'table_amount' => $tableAmount, 'total_amount' => $total, 'status' => 'completed']);
            $session->table->update(['status' => 'available']);
            Payment::create(['payable_id' => $session->id, 'payable_type' => TableSession::class, 'staff_id' => auth()->id(), 'amount' => $total, 'method' => $paymentMethod, 'paid_at' => now()]);
        });

        return back()->with('success', 'Đã kết thúc bàn. Tổng: ' . number_format($total) . 'đ');
    }
}
