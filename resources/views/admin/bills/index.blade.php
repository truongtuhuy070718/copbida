@extends('layouts.app')

@section('title', 'Quản lý hóa đơn')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Quản lý hóa đơn</h5>
</div>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link {{ $tab=='orders'?'active':'' }}" href="?tab=orders">Hóa đơn bán hàng</a></li>
    <li class="nav-item"><a class="nav-link {{ $tab=='sessions'?'active':'' }}" href="?tab=sessions">Hóa đơn giờ chơi</a></li>
</ul>

@if($tab == 'orders')
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form class="row g-2" method="GET">
                <input type="hidden" name="tab" value="orders">
                <div class="col-md-4"><input type="text" name="q" class="form-control" placeholder="Mã hóa đơn" value="{{ request('q') }}"></div>
                <div class="col-md-3"><input type="date" name="date" class="form-control" value="{{ request('date') }}"></div>
                <div class="col-md-2"><button class="btn btn-outline-primary w-100">Lọc</button></div>
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle bg-white shadow-sm rounded overflow-hidden">
            <thead class="table-light"><tr><th>Mã HD</th><th>Nhân viên</th><th>Tổng</th><th>PTTT</th><th>Thời gian</th><th></th></tr></thead>
            <tbody>
                @forelse($orders as $o)
                <tr>
                    <td>{{ $o->order_code }}</td>
                    <td>{{ $o->staff?->name }}</td>
                    <td>{{ number_format($o->total) }}đ</td>
                    <td>{{ $o->payment_method }}</td>
                    <td>{{ $o->paid_at?->format('d/m/Y H:i') }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#orderModal{{ $o->id }}">Chi tiết</button>
                        <form method="POST" action="{{ route('admin.bills.orders.destroy', $o) }}" class="d-inline" onsubmit="return confirm('Xóa hóa đơn này?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">Chưa có hóa đơn</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $orders->links() }}
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle bg-white shadow-sm rounded overflow-hidden">
            <thead class="table-light"><tr><th>Bàn</th><th>Nhân viên</th><th>Thời gian</th><th>Tiền bàn</th><th>Tiền SP</th><th>Tổng</th><th>Thời gian thanh toán</th></tr></thead>
            <tbody>
                @forelse($sessions as $s)
                <tr>
                    <td>{{ $s->table?->name }}</td>
                    <td>{{ $s->staff?->name }}</td>
                    <td>{{ floor($s->duration_minutes / 60) }}h {{ $s->duration_minutes % 60 }}m</td>
                    <td>{{ number_format($s->table_amount) }}đ</td>
                    <td>{{ number_format($s->products_amount) }}đ</td>
                    <td>{{ number_format($s->total_amount) }}đ</td>
                    <td>{{ $s->ended_at?->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted">Chưa có hóa đơn giờ chơi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $sessions->links() }}
@endif

@foreach($orders as $o)
<div class="modal fade" id="orderModal{{ $o->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">{{ $o->order_code }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <table class="table table-sm">
                    <thead><tr><th>Sản phẩm</th><th>SL</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead>
                    <tbody>
                        @foreach($o->items as $item)
                        <tr><td>{{ $item->product?->name }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->unit_price) }}đ</td><td>{{ number_format($item->total_price) }}đ</td></tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="text-end fw-bold">Tổng: {{ number_format($o->total) }}đ</div>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection
