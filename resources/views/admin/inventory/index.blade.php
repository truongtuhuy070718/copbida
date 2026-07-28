@extends('layouts.app')

@section('title', 'Quản lý kho')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Quản lý kho</h5>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#adjustModal"><i class="bi bi-plus-lg"></i> Điều chỉnh kho</button>
</div>
@if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif

<div class="table-responsive">
    <table class="table table-hover align-middle bg-white shadow-sm rounded overflow-hidden">
        <thead class="table-light"><tr><th>Tên</th><th>Danh mục</th><th>Tồn kho</th><th>Tối thiểu</th><th>Trạng thái</th></tr></thead>
        <tbody>
            @forelse($products as $p)
            <tr class="{{ $p->stock <= $p->min_stock ? 'table-danger' : '' }}">
                <td>{{ $p->name }}</td>
                <td>{{ $p->category?->name ?? '-' }}</td>
                <td>{{ $p->stock }}</td>
                <td>{{ $p->min_stock }}</td>
                <td>
                    @if($p->stock <= $p->min_stock)<span class="badge bg-danger">Sắp hết</span>@else<span class="badge bg-success">OK</span>@endif
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted">Chưa có sản phẩm</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $products->links() }}

<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.inventory.adjust') }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Điều chỉnh kho</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Sản phẩm</label><select name="product_id" class="form-select" required>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }} (tồn: {{ $p->stock }})</option>@endforeach</select></div>
                <div class="mb-3"><label class="form-label">Loại</label><select name="type" class="form-select" required><option value="in">Nhập kho</option><option value="out">Xuất kho</option><option value="adjust">Điều chỉnh tuyệt đối</option></select></div>
                <div class="mb-3"><label class="form-label">Số lượng</label><input type="number" name="quantity" class="form-control" required min="0"></div>
            </div>
            <div class="modal-footer"><button class="btn btn-primary">Lưu</button></div>
        </form>
    </div>
</div>
@endsection
