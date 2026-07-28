@extends('layouts.app')

@section('title', 'Sơ đồ bàn')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Sơ đồ bàn</h5>
    <span class="text-muted small">Tự động cập nhật</span>
</div>

@if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif

<div id="tableMap" hx-get="{{ route('staff.tables') }}" hx-trigger="every 10s" hx-select="#tableMap">
    @foreach($tables as $area => $items)
    <div class="mb-4">
        <h6 class="fw-bold text-uppercase text-muted">{{ $area }}</h6>
        <div class="row g-3">
            @foreach($items as $t)
            @php $session = $sessions->get($t->id); @endphp
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card card-table border-0 shadow-sm h-100 {{ $t->status=='playing' ? 'border-warning border-3' : '' }}">
                    <div class="card-body text-center">
                        <i class="bi bi-table fs-1 {{ $t->status=='playing' ? 'text-warning' : 'text-success' }}"></i>
                        <h6 class="fw-bold mt-2">{{ $t->name }}</h6>
                        @if($t->status=='playing' && $session)
                            <div class="text-warning fw-bold timer" data-start="{{ $session->started_at->timestamp }}">
                                {{ floor($session->started_at->diffInMinutes(now()) / 60) }}:{{ str_pad($session->started_at->diffInMinutes(now()) % 60, 2, '0', STR_PAD_LEFT) }}
                            </div>
                            <div class="small text-muted">{{ number_format($t->price_per_hour) }}đ/giờ</div>
                            <div class="d-flex flex-wrap gap-1 justify-content-center mt-2">
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#orderModal{{ $t->id }}"><i class="bi bi-cart-plus"></i> Gọi món</button>
                                <form method="POST" action="{{ route('staff.tables.close', $t) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-cash-coin"></i> Tính tiền</button>
                                </form>
                            </div>
                        @else
                            <div class="text-success small fw-bold">Trống</div>
                            <div class="small text-muted">{{ number_format($t->price_per_hour) }}đ/giờ</div>
                            <form method="POST" action="{{ route('staff.tables.start', $t) }}" class="mt-2">
                                @csrf
                                <button class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-play-fill"></i> Mở bàn</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            @if($t->status=='playing' && $session)
            <div class="modal fade" id="orderModal{{ $t->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route('staff.tables.order', $t) }}" class="modal-content">
                        @csrf
                        <div class="modal-header"><h5 class="modal-title">Gọi món - {{ $t->name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Sản phẩm</label>
                                <select name="product_id" class="form-select" required>
                                    @foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }} - {{ number_format($p->price) }}đ</option>@endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Số lượng</label>
                                <input type="number" name="quantity" class="form-control" value="1" min="1" required>
                            </div>
                        </div>
                        <div class="modal-footer"><button class="btn btn-primary">Thêm</button></div>
                    </form>
                </div>
            </div>
            @endif
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
function updateTimers(){
    document.querySelectorAll('.timer').forEach(el => {
        const start = parseInt(el.dataset.start) * 1000;
        const diff = Math.max(0, Math.floor((Date.now() - start) / 60000));
        const h = Math.floor(diff / 60), m = diff % 60;
        el.textContent = h + ':' + String(m).padStart(2, '0');
    });
}
setInterval(updateTimers, 60000);
</script>
@endpush
