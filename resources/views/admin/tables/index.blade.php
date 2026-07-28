@extends('layouts.app')

@section('title', 'Quản lý bàn')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Quản lý bàn</h5>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTableModal"><i class="bi bi-plus-lg"></i> Thêm bàn</button>
</div>

@if(session('success'))
    <div class="alert alert-success py-2">{{ session('success') }}</div>
@endif

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form class="row g-2" method="GET">
            <div class="col-md-3">
                <select name="area" class="form-select" onchange="this.form.submit()">
                    <option value="">Tất cả khu vực</option>
                    @foreach($areas as $a)
                        <option value="{{ $a }}" {{ request('area')==$a?'selected':'' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle bg-white shadow-sm rounded overflow-hidden">
        <thead class="table-light"><tr><th>Tên bàn</th><th>Khu vực</th><th>Giá/giờ</th><th>Trạng thái</th><th>Hoạt động</th><th></th></tr></thead>
        <tbody>
            @forelse($tables as $t)
            <tr>
                <td>{{ $t->name }}</td>
                <td>{{ $t->area }}</td>
                <td>{{ number_format($t->price_per_hour) }}đ</td>
                <td>
                    @if($t->status=='available')<span class="badge bg-success">Trống</span>@endif
                    @if($t->status=='playing')<span class="badge bg-warning text-dark">Đang chơi</span>@endif
                    @if($t->status=='maintenance')<span class="badge bg-secondary">Bảo trì</span>@endif
                    @if($t->status=='reserved')<span class="badge bg-info text-dark">Đặt trước</span>@endif
                </td>
                <td><span class="badge {{ $t->active?'bg-success':'bg-danger' }}">{{ $t->active?'Kích hoạt':'Khóa' }}</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editTableModal{{ $t->id }}"><i class="bi bi-pencil"></i></button>
                    <form method="POST" action="{{ route('admin.tables.destroy', $t) }}" class="d-inline" onsubmit="return confirm('Xóa bàn này?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted">Chưa có bàn</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $tables->links() }}

<div class="modal fade" id="addTableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.tables.store') }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Thêm bàn</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Tên bàn</label><input name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Khu vực</label><input name="area" class="form-control" required value="Khu chính"></div>
                    <div class="mb-3"><label class="form-label">Giá/giờ</label><input type="number" name="price_per_hour" class="form-control" required value="50000"></div>
                </div>
                <div class="modal-footer"><button class="btn btn-primary">Lưu</button></div>
            </form>
        </div>
    </div>
</div>

@foreach($tables as $t)
<div class="modal fade" id="editTableModal{{ $t->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.tables.update', $t) }}">
                @csrf @method('PUT')
                <div class="modal-header"><h5 class="modal-title">Sửa bàn</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Tên bàn</label><input name="name" class="form-control" value="{{ $t->name }}" required></div>
                    <div class="mb-3"><label class="form-label">Khu vực</label><input name="area" class="form-control" value="{{ $t->area }}" required></div>
                    <div class="mb-3"><label class="form-label">Giá/giờ</label><input type="number" name="price_per_hour" class="form-control" value="{{ $t->price_per_hour }}" required></div>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="active" value="1" {{ $t->active?'checked':'' }}><label class="form-check-label">Kích hoạt</label></div>
                </div>
                <div class="modal-footer"><button class="btn btn-primary">Cập nhật</button></div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
