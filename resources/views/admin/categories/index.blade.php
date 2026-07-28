@extends('layouts.app')

@section('title', 'Danh mục')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Danh mục</h5>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal"><i class="bi bi-plus-lg"></i> Thêm danh mục</button>
</div>
@if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif

<div class="table-responsive">
    <table class="table table-hover align-middle bg-white shadow-sm rounded overflow-hidden">
        <thead class="table-light"><tr><th>Tên</th><th>Loại</th><th></th></tr></thead>
        <tbody>
            @forelse($categories as $c)
            <tr>
                <td>{{ $c->name }}</td>
                <td>{{ $c->type == 'product' ? 'Sản phẩm' : 'Dịch vụ' }}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $c->id }}"><i class="bi bi-pencil"></i></button>
                    <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" class="d-inline" onsubmit="return confirm('Xóa danh mục này?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-center text-muted">Chưa có danh mục</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $categories->links() }}

<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.categories.store') }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Thêm danh mục</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Tên</label><input name="name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Loại</label><select name="type" class="form-select"><option value="product">Sản phẩm</option><option value="service">Dịch vụ</option></select></div>
            </div>
            <div class="modal-footer"><button class="btn btn-primary">Lưu</button></div>
        </form>
    </div>
</div>

@foreach($categories as $c)
<div class="modal fade" id="editCategoryModal{{ $c->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.categories.update', $c) }}" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title">Sửa danh mục</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Tên</label><input name="name" class="form-control" value="{{ $c->name }}" required></div>
                <div class="mb-3"><label class="form-label">Loại</label><select name="type" class="form-select"><option value="product" {{ $c->type=='product'?'selected':'' }}>Sản phẩm</option><option value="service" {{ $c->type=='service'?'selected':'' }}>Dịch vụ</option></select></div>
            </div>
            <div class="modal-footer"><button class="btn btn-primary">Cập nhật</button></div>
        </form>
    </div>
</div>
@endforeach
@endsection
