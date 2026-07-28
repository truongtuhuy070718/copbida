<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index()
    {
        $staff = User::orderBy('role')->orderBy('name')->paginate(20);
        return view('admin.staff.index', compact('staff'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'phone' => 'required|string|unique:users,phone', 'password' => 'required|string|min:4', 'role' => 'required|in:admin,staff']);
        $data['password'] = Hash::make($data['password']);
        User::create($data);
        return back()->with('success', 'Thêm nhân viên thành công.');
    }

    public function update(Request $request, User $user)
    {
        $rules = ['name' => 'required|string|max:255', 'role' => 'required|in:admin,staff', 'active' => 'boolean'];
        if ($request->filled('password')) $rules['password'] = 'string|min:4';
        $data = $request->validate($rules);
        if ($request->filled('password')) $data['password'] = Hash::make($data['password']);
        $data['active'] = $request->boolean('active', true);
        $user->update($data);
        return back()->with('success', 'Cập nhật nhân viên thành công.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) return back()->withErrors(['Không thể xóa chính mình.']);
        $user->delete();
        return back()->with('success', 'Xóa nhân viên thành công.');
    }
}
