@extends('layouts.app')

@section('title', 'Tổng quan')

@section('content')
<div class="row g-3">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted small">Doanh thu giờ chơi hôm nay</h6>
                <h4 class="fw-bold text-primary text-truncate">{{ number_format($revenueTable) }}đ</h4>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted small">Doanh thu bán hàng hôm nay</h6>
                <h4 class="fw-bold text-success text-truncate">{{ number_format($revenueProduct) }}đ</h4>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted small">Bàn đang chơi</h6>
                <h4 class="fw-bold text-warning">{{ $playing }}</h4>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted small">Sản phẩm sắp hết</h6>
                <h4 class="fw-bold text-danger">{{ $lowStock }}</h4>
            </div>
        </div>
    </div>
</div>
@endsection
