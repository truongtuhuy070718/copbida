@extends('layouts.app')

@section('title', 'Báo cáo doanh thu')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Báo cáo doanh thu</h5>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-md-3">
                <label class="form-label small">Nhanh</label>
                <select name="range" class="form-select" onchange="this.form.submit()">
                    <option value="today" {{ request('range','today')=='today'?'selected':'' }}>Hôm nay</option>
                    <option value="yesterday" {{ request('range')=='yesterday'?'selected':'' }}>Hôm qua</option>
                    <option value="week" {{ request('range')=='week'?'selected':'' }}>Tuần này</option>
                    <option value="month" {{ request('range')=='month'?'selected':'' }}>Tháng này</option>
                    <option value="year" {{ request('range')=='year'?'selected':'' }}>Năm nay</option>
                </select>
            </div>
            <div class="col-md-3"><label class="form-label small">Từ</label><input type="date" name="start" class="form-control" value="{{ request('start', $start->format('Y-m-d')) }}"></div>
            <div class="col-md-3"><label class="form-label small">Đến</label><input type="date" name="end" class="form-control" value="{{ request('end', $end->format('Y-m-d')) }}"></div>
            <div class="col-md-3"><button class="btn btn-primary w-100">Xem</button></div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm"><div class="card-body"><h6 class="text-muted">Tiền giờ chơi</h6><h4 class="fw-bold text-primary">{{ number_format($tableRevenue) }}đ</h4></div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm"><div class="card-body"><h6 class="text-muted">Bán hàng</h6><h4 class="fw-bold text-success">{{ number_format($productRevenue) }}đ</div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm"><div class="card-body"><h6 class="text-muted">Tổng doanh thu</h6><h4 class="fw-bold text-info">{{ number_format($tableRevenue + $productRevenue) }}đ</h4></div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm"><div class="card-body"><h6 class="text-muted">Đơn / Ca</h6><h4 class="fw-bold text-warning">{{ $orders }} / {{ $tableSessions }}</h4></div></div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <canvas id="revenueChart" height="100"></canvas>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($labels) !!},
        datasets: [
            { label: 'Tiền giờ chơi', data: {!! json_encode($tableAmounts) !!}, backgroundColor: 'rgba(13, 110, 253, 0.7)' },
            { label: 'Bán hàng', data: {!! json_encode($orderAmounts) !!}, backgroundColor: 'rgba(25, 135, 84, 0.7)' }
        ]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});
</script>
@endpush
