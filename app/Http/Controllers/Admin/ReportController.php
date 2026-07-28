<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TableSession;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$start, $end] = $this->getRange($request->get('range', 'today'));
        $start = $request->filled('start') ? Carbon::parse($request->start)->startOfDay() : $start;
        $end = $request->filled('end') ? Carbon::parse($request->end)->endOfDay() : $end;

        $tableRevenue = TableSession::whereBetween('ended_at', [$start, $end])->where('status', 'completed')->sum('total_amount');
        $productRevenue = Order::whereBetween('paid_at', [$start, $end])->where('status', 'paid')->sum('total');
        $tableSessions = TableSession::whereBetween('ended_at', [$start, $end])->where('status', 'completed')->count();
        $orders = Order::whereBetween('paid_at', [$start, $end])->where('status', 'paid')->count();

        $tableData = TableSession::selectRaw('date(ended_at) as d, sum(total_amount) as amount')
            ->whereBetween('ended_at', [$start, $end])->where('status', 'completed')->groupBy('d')->orderBy('d')->get();
        $orderData = Order::selectRaw('date(paid_at) as d, sum(total) as amount')
            ->whereBetween('paid_at', [$start, $end])->where('status', 'paid')->groupBy('d')->orderBy('d')->get();

        $labels = collect(array_unique(array_merge($tableData->pluck('d')->toArray(), $orderData->pluck('d')->toArray())))->sort()->values();
        $tableAmounts = $labels->map(fn($d) => (float)($tableData->firstWhere('d', $d)?->amount ?? 0));
        $orderAmounts = $labels->map(fn($d) => (float)($orderData->firstWhere('d', $d)?->amount ?? 0));

        return view('admin.reports.index', compact('start', 'end', 'tableRevenue', 'productRevenue', 'tableSessions', 'orders', 'labels', 'tableAmounts', 'orderAmounts'));
    }

    private function getRange($range): array
    {
        return match ($range) {
            'yesterday' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            default => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
        };
    }
}
