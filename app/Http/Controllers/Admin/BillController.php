<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TableSession;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'orders');
        $query = Order::with('staff', 'items.product')->orderBy('created_at', 'desc');
        if ($request->filled('q')) {
            $query->where('order_code', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('date')) {
            $query->whereDate('paid_at', $request->date);
        }
        $orders = $query->paginate(15);

        $sessions = TableSession::with('table', 'staff')
            ->where('status', 'completed')
            ->orderBy('ended_at', 'desc')
            ->paginate(15, ['*'], 'sessions_page');

        return view('admin.bills.index', compact('orders', 'sessions', 'tab'));
    }

    public function order(Order $order)
    {
        return view('admin.bills.order', compact('order'));
    }

    public function session(TableSession $session)
    {
        return view('admin.bills.session', compact('session'));
    }

    public function destroyOrder(Order $order)
    {
        $order->items()->delete();
        $order->payments()->delete();
        $order->delete();
        return back()->with('success', 'Xóa hóa đơn thành công.');
    }
}
