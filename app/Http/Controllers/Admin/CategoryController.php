<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'type' => 'required|in:product,service']);
        Category::create($data);
        return back()->with('success', 'Thêm danh mục thành công.');
    }
}
