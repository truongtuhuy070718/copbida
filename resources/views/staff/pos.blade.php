@extends('layouts.app')

@section('title', 'POS Bán hàng')

@section('content')
<div class="row g-3" style="min-height:80vh;">
    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2 mb-3" id="categoryFilter">
                    <button class="btn btn-sm btn-primary" data-cat="all">Tất cả</button>
                    @foreach($categories as $cat)
                        <button class="btn btn-sm btn-outline-secondary" data-cat="{{ $cat->id }}">{{ $cat->name }}</button>
                    @endforeach
                </div>
                <div class="row g-2" id="productGrid">
                    @foreach($products as $p)
                    <div class="col-4 col-md-3 col-xl-2 product-item" data-cat="{{ $p->category_id }}">
                        <div class="card card-table border-0 shadow-sm text-center h-100" onclick="addToCart({{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->price }})">
                            <div class="card-body p-2">
                                <div class="fw-bold text-truncate">{{ $p->name }}</div>
                                <div class="small text-primary">{{ number_format($p->price) }}đ</div>
                                <div class="small text-muted">Tồn: {{ $p->stock }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <h5 class="fw-bold"><i class="bi bi-cart3"></i> Giỏ hàng</h5>
                <div id="cartItems" class="flex-grow-1 overflow-auto" style="max-height:55vh;">
                    <div class="text-muted text-center py-5">Chưa có sản phẩm</div>
                </div>
                <div class="border-top pt-3 mt-auto">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tổng</span>
                        <span class="fw-bold fs-5" id="cartTotal">0đ</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Phương thức thanh toán</label>
                        <select id="paymentMethod" class="form-select">
                            <option value="cash">Tiền mặt</option>
                            <option value="transfer">Chuyển khoản</option>
                            <option value="card">Thẻ</option>
                        </select>
                    </div>
                    <button id="checkoutBtn" class="btn btn-success w-100 btn-lg" disabled onclick="checkout()">Thanh toán</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const STORAGE_KEY = 'pos_cart';
let cart = [];

function loadCart(){
    try { cart = JSON.parse(localStorage.getItem(STORAGE_KEY)) || []; } catch(e){ cart = []; }
    renderCart();
}
function saveCart(){ localStorage.setItem(STORAGE_KEY, JSON.stringify(cart)); renderCart(); }
function addToCart(id, name, price){
    const existing = cart.find(i => i.product_id === id);
    if(existing){ existing.quantity += 1; }
    else { cart.push({product_id:id, name, price, quantity:1}); }
    saveCart();
}
function updateQty(index, delta){
    cart[index].quantity += delta;
    if(cart[index].quantity <= 0) cart.splice(index, 1);
    saveCart();
}
function removeItem(index){ cart.splice(index, 1); saveCart(); }
function clearCart(){ cart = []; saveCart(); }
function renderCart(){
    const container = document.getElementById('cartItems');
    const totalEl = document.getElementById('cartTotal');
    const btn = document.getElementById('checkoutBtn');
    if(!cart.length){ container.innerHTML = '<div class="text-muted text-center py-5">Chưa có sản phẩm</div>'; totalEl.textContent='0đ'; btn.disabled=true; return; }
    let total = 0;
    let html = '<ul class="list-group list-group-flush">';
    cart.forEach((item, idx) => { total += item.price * item.quantity; html += `<li class="list-group-item px-0 d-flex justify-content-between align-items-center">
            <div>
                <div class="fw-bold">${item.name}</div>
                <div class="small text-muted">${item.price.toLocaleString()}đ x ${item.quantity}</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary" onclick="updateQty(${idx}, -1)">-</button>
                <span>${item.quantity}</span>
                <button class="btn btn-sm btn-outline-secondary" onclick="updateQty(${idx}, 1)">+</button>
                <button class="btn btn-sm btn-outline-danger" onclick="removeItem(${idx})"><i class="bi bi-trash"></i></button>
            </div>
        </li>`; });
    html += '</ul>';
    container.innerHTML = html;
    totalEl.textContent = total.toLocaleString() + 'đ';
    btn.disabled = false;
}
async function checkout(){
    if(!cart.length) return;
    const btn = document.getElementById('checkoutBtn');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý';
    try {
        const res = await fetch('{{ route('staff.pos.checkout') }}', {
            method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
            body: JSON.stringify({items: cart.map(i => ({product_id: i.product_id, quantity: i.quantity})), payment_method: document.getElementById('paymentMethod').value})
        });
        const data = await res.json();
        if(data.success){ clearCart(); alert('Thanh toán thành công. Mã đơn: ' + data.order_code); }
        else { alert('Có lỗi xảy ra.'); }
    } catch(e){ alert('Lỗi kết nối.'); }
    btn.disabled = false; btn.innerHTML = 'Thanh toán';
}

document.querySelectorAll('#categoryFilter button').forEach(b => b.addEventListener('click', function(){
    document.querySelectorAll('#categoryFilter button').forEach(x => x.classList.replace('btn-primary','btn-outline-secondary'));
    this.classList.replace('btn-outline-secondary','btn-primary');
    const cat = this.dataset.cat;
    document.querySelectorAll('.product-item').forEach(el => el.style.display = (cat==='all' || el.dataset.cat===cat) ? 'block' : 'none');
}));

document.addEventListener('DOMContentLoaded', loadCart);
window.addEventListener('storage', (e) => { if(e.key===STORAGE_KEY) loadCart(); });
</script>
@endpush
