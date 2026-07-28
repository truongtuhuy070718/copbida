@extends('layouts.app')

@section('title', 'Quản lý nhân viên')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Nhân viên</h5>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStaffModal"><i class="bi bi-plus-lg"></i> Thêm nhân viên</button>
</div>
@if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif

<div class="table-responsive">
    <table class="table table-hover align-middle bg-white shadow-sm rounded overflow-hidden">
        <thead class="table-light"><tr><th>Tên</th><th>SĐT</th><th>Vai trò</th><th>Trạng thái</th><th></th></tr></thead>
        <tbody>
            @forelse($staff as $s)
            <tr>
                <td>{{ $s->name }}</td>
                <td>{{ $s->phone }}</td>
                <td><span class="badge {{ $s->role=='admin'?'bg-danger':'bg-info' }}">{{ $s->role=='admin'?'Admin':'Nhân viên' }}</span></td>
                <td><span class="badge {{ $s->active?'bg-success':'bg-secondary' }}">{{ $s->active?'Hoạt động':'Khóa' }}</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editStaffModal{{ $s->id }}"><i class="bi bi-pencil"></i></button>
                    <form method="POST" action="{{ route('admin.staff.destroy', $s) }}" class="d-inline" onsubmit="return confirm('Xóa nhân viên này?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted">Chưa có nhân viên</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $staff->links() }}

<div class="modal fade" id="addStaffModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.staff.store') }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Thêm nhân viên</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Tên</label><input name="name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">SĐT / Tên đăng nhập</label><input name="phone" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Mật khẩu</label><input type="password" name="password" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Vai trò</label><select name="role" class="form-select"><option value="staff">Nhân viên</option><option value="admin">Admin</option></select></div>
            </div>
            <div class="modal-footer"><button class="btn btn-primary">Lưu</button></div>
        </form>
    </div>
</div>

@foreach($staff as $s)
<div class="modal fade" id="editStaffModal{{ $s->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.staff.update', $s) }}" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title">Sửa nhân viên</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Tên</label><input name="name" class="form-control" value="{{ $s->name }}" required></div>
                <div class="mb-3"><label class="form-label">SĐT</label><input name="phone" class="form-control" value="{{ $s->phone }}" disabled></div>
                <div class="mb-3"><label class="form-label">Mật khẩu mới (để trống nếu không đổi)</label><input type="password" name="password" class="form-control"></div>
                <div class="mb-3"><label class="form-label">Vai trò</label><select name="role" class="form-select"><option value="staff" {{ $s->role=='staff'?'selected':'' }}>Nhân viên</option><option value="admin" {{ $s->role=='admin'?'selected':'' }}>Admin</option></select></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="active" value="1" {{ $s->active?'checked':'' }}><label class="form-check-label">Hoạt động</label></div>
            </div>
            <div class="modal-footer"><button class="btn btn-primary">Cập nhật</button></div>
        </form>
    </div>
</div>
@endforeach
@endsection
