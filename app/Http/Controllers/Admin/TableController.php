<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameTable;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index(Request $request)
    {
        $query = GameTable::query();
        if ($request->filled('area')) $query->where('area', $request->area);
        if ($request->has('active')) $query->where('active', $request->active);
        $tables = $query->orderBy('area')->orderBy('name')->paginate(20);
        $areas = GameTable::select('area')->distinct()->pluck('area');
        return view('admin.tables.index', compact('tables', 'areas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'area' => 'required|string|max:255', 'price_per_hour' => 'required|numeric|min:0']);
        GameTable::create($data);
        return back()->with('success', 'Thêm bàn thành công.');
    }

    public function update(Request $request, GameTable $table)
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'area' => 'required|string|max:255', 'price_per_hour' => 'required|numeric|min:0', 'active' => 'boolean']);
        $data['active'] = $request->boolean('active', true);
        $table->update($data);
        return back()->with('success', 'Cập nhật bàn thành công.');
    }

    public function destroy(GameTable $table)
    {
        $table->delete();
        return back()->with('success', 'Xóa bàn thành công.');
    }
}
