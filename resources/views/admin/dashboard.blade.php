@extends('layouts.app')

@section('title', 'Tổng quan')

@section('content')
<div class="row g-3">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Doanh thu giờ chơi hôm nay</h6>
                <h4 class="fw-bold text-primary">{{ number_format($revenueTable) }}đ</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Doanh thu bán hàng hôm nay</h6>
                <h4 class="fw-bold text-success">{{ number_format($revenueProduct) }}đ</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Bàn đang chơi</h6>
                <h4 class="fw-bold text-warning">{{ $playing }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Sản phẩm sắp hết</h6>
                <h4 class="fw-bold text-danger">{{ $lowStock }}</h4>
            </div>
        </div>
    </div>
</div>
@endsection
