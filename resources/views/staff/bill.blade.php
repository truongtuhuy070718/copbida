@extends('layouts.app')

@section('title', 'Hóa đơn')

@section('content')
<style>
    @media print {
        .no-print { display: none !important; }
        body { background: #fff; }
        .bill-container { box-shadow: none; border: none; }
    }
    .bill-container { max-width: 400px; margin: 0 auto; background: #fff; border: 1px dashed #aaa; padding: 20px; }
    .bill-header { text-align: center; border-bottom: 1px dashed #aaa; padding-bottom: 10px; margin-bottom: 15px; }
    .bill-item { display: flex; justify-content: space-between; margin-bottom: 5px; }
    .bill-total { border-top: 1px dashed #aaa; margin-top: 10px; padding-top: 10px; font-weight: bold; }
</style>

<div class="container py-4">
    <div class="bill-container">
        <div class="bill-header">
            <h5 class="fw-bold mb-1">BIDA MANAGER</h5>
            <p class="small text-muted mb-0">Hóa đơn thanh toán</p>
        </div>

        <div class="small mb-3">
            <div class="bill-item"><span>Mã HĐ:</span><span>{{ $session->orders->first()?->order_code ?? 'ORD-'.$session->id }}</span></div>
            <div class="bill-item"><span>Bàn:</span><span>{{ $session->table->name }}</span></div>
            <div class="bill-item"><span>Giờ vào:</span><span>{{ $session->started_at->format('H:i d/m/Y') }}</span></div>
            <div class="bill-item"><span>Giờ ra:</span><span>{{ $session->ended_at ? $session->ended_at->format('H:i d/m/Y') : now()->format('H:i d/m/Y') }}</span></div>
            <div class="bill-item"><span>Thu ngân:</span><span>{{ $session->staff->name }}</span></div>
        </div>

        <table class="table table-sm table-borderless small">
            <thead class="border-bottom">
                <tr>
                    <th>Mặt hàng</th>
                    <th class="text-end">SL</th>
                    <th class="text-end">Đơn giá</th>
                    <th class="text-end">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($session->orders as $order)
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td class="text-end">{{ $item->quantity }}</td>
                        <td class="text-end">{{ number_format($item->unit_price) }}đ</td>
                        <td class="text-end">{{ number_format($item->total_price) }}đ</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <div class="bill-total">
            <div class="bill-item"><span>Tổng tiền:</span><span>{{ number_format($session->total_amount) }}đ</span></div>
            <div class="bill-item"><span>Phương thức:</span><span>{{ $session->payments->first()?->method === 'cash' ? 'Tiền mặt' : ($session->payments->first()?->method === 'transfer' ? 'Chuyển khoản' : 'Thẻ') }}</span></div>
        </div>

        <div class="text-center mt-4 small text-muted">
            Cảm ơn quý khách!<br>
            Hẹn gặp lại
        </div>
    </div>

    <div class="text-center mt-4 no-print">
        <button class="btn btn-primary" onclick="window.print()">In hóa đơn</button>
        <a href="{{ route('staff.pos') }}" class="btn btn-outline-secondary">Quay lại POS</a>
    </div>
</div>
@endsection
