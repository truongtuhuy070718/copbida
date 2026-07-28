@extends('layouts.app')

@section('title', 'Sản phẩm')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Sản phẩm</h5>
    <div>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm me-2"><i class="bi bi-tags"></i> Danh mục</a>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProductModal"><i class="bi bi-plus-lg"></i> Thêm sản phẩm</button>
    </div>
</div>
@if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form class="row g-2" method="GET">
            <div class="col-md-4"><input type="text" name="q" class="form-control" placeholder="Tìm tên sản phẩm" value="{{ request('q') }}"></div>
            <div class="col-md-3">
                <select name="category" class="form-select" onchange="this.form.submit()">
                    <option value="">Tất cả danh mục</option>
                    @foreach($categories as $c)<option value="{{ $c->id }}" {{ request('category')==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-outline-primary w-100">Lọc</button></div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle bg-white shadow-sm rounded overflow-hidden">
        <thead class="table-light"><tr><th>Tên</th><th>Danh mục</th><th>Đơn vị</th><th>Giá</th><th>Tồn</th><th>Trạng thái</th><th></th></tr></thead>
        <tbody>
            @forelse($products as $p)
            <tr>
                <td>{{ $p->name }}</td>
                <td>{{ $p->category?->name ?? '-' }}</td>
                <td>{{ $p->unit?->name ?? $p->unit }}</td>
                <td>{{ number_format($p->price) }}đ</td>
                <td class="{{ $p->stock <= $p->min_stock ? 'text-danger fw-bold' : '' }}">{{ $p->stock }}</td>
                <td><span class="badge {{ $p->active?'bg-success':'bg-secondary' }}">{{ $p->active?'Kích hoạt':'Khóa' }}</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editProductModal{{ $p->id }}"><i class="bi bi-pencil"></i></button>
                    <form method="POST" action="{{ route('admin.products.destroy', $p) }}" class="d-inline" onsubmit="return confirm('Xóa sản phẩm này?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted">Chưa có sản phẩm</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $products->links() }}

<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.products.store') }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Thêm sản phẩm</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Tên</label><input name="name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Danh mục</label><select name="category_id" class="form-select"><option value="">--</option>@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
                <div class="row g-2">
                    <div class="col-6"><label class="form-label">Giá bán</label><input type="number" name="price" class="form-control" required></div>
                    <div class="col-6"><label class="form-label">Giá vốn</label><input type="number" name="cost" class="form-control" value="0"></div>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-6"><label class="form-label">Tồn kho</label><input type="number" name="stock" class="form-control" value="0"></div>
                    <div class="col-6"><label class="form-label">Tồn tối thiểu</label><input type="number" name="min_stock" class="form-control" value="0"></div>
                </div>
                <div class="mb-3 mt-2"><label class="form-label">Đơn vị</label><select name="unit_id" class="form-select"><option value="">--</option>@foreach($units as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select></div>
            </div>
            <div class="modal-footer"><button class="btn btn-primary">Lưu</button></div>
        </form>
    </div>
</div>

@foreach($products as $p)
<div class="modal fade" id="editProductModal{{ $p->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.products.update', $p) }}" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title">Sửa sản phẩm</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Tên</label><input name="name" class="form-control" value="{{ $p->name }}" required></div>
                <div class="mb-3"><label class="form-label">Danh mục</label><select name="category_id" class="form-select"><option value="">--</option>@foreach($categories as $c)<option value="{{ $c->id }}" {{ $p->category_id==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach</select></div>
                <div class="row g-2">
                    <div class="col-6"><label class="form-label">Giá bán</label><input type="number" name="price" class="form-control" value="{{ $p->price }}" required></div>
                    <div class="col-6"><label class="form-label">Giá vốn</label><input type="number" name="cost" class="form-control" value="{{ $p->cost }}"></div>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-6"><label class="form-label">Tồn kho</label><input type="number" name="stock" class="form-control" value="{{ $p->stock }}"></div>
                    <div class="col-6"><label class="form-label">Tồn tối thiểu</label><input type="number" name="min_stock" class="form-control" value="{{ $p->min_stock }}"></div>
                </div>
                <div class="mb-3 mt-2"><label class="form-label">Đơn vị</label><select name="unit_id" class="form-select"><option value="">--</option>@foreach($units as $u)<option value="{{ $u->id }}" {{ $p->unit_id==$u->id?'selected':'' }}>{{ $u->name }}</option>@endforeach</select></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="active" value="1" {{ $p->active?'checked':'' }}><label class="form-check-label">Kích hoạt</label></div>
            </div>
            <div class="modal-footer"><button class="btn btn-primary">Cập nhật</button></div>
        </form>
    </div>
</div>
@endforeach
@endsection
