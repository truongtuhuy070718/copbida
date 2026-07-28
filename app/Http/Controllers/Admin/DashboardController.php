<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameTable;
use App\Models\Order;
use App\Models\Product;
use App\Models\TableSession;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $revenueTable = TableSession::whereDate('ended_at', $today)->where('status', 'completed')->sum('total_amount');
        $revenueProduct = Order::whereDate('paid_at', $today)->where('status', 'paid')->sum('total');
        $playing = GameTable::where('status', 'playing')->count();
        $lowStock = Product::whereColumn('stock', '<=', 'min_stock')->where('active', true)->count();
        return view('admin.dashboard', compact('revenueTable', 'revenueProduct', 'playing', 'lowStock'));
    }
}
