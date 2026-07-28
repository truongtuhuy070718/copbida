@extends('layouts.app')

@section('title', 'POS Bán hàng')

@section('content')
<style>
    .pos-wrapper { display: flex; gap: .75rem; height: calc(100vh - 140px); min-height: 500px; }
    .pos-left { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: .75rem; }
    .pos-right { width: 380px; flex-shrink: 0; display: flex; flex-direction: column; }
    @media (max-width: 991.98px) {
        .pos-wrapper { flex-direction: column; height: auto; }
        .pos-right { width: 100%; }
    }
    .pos-product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: .75rem; }
    .pos-product-item { cursor: pointer; transition: transform .1s; }
    .pos-product-item:active { transform: scale(.97); }
    .pos-cart-list { flex: 1; overflow-y: auto; }
    .pos-total-row { display: flex; justify-content: space-between; padding: .5rem 0; border-bottom: 1px dashed #dee2e6; }
    .category-tabs { display: flex; gap: .5rem; overflow-x: auto; padding-bottom: .25rem; }
    .category-tabs::-webkit-scrollbar { height: 4px; }
    .category-tabs .btn { white-space: nowrap; }
</style>

<div class="pos-wrapper">
    <div class="pos-left">
        <div class="card shadow-sm flex-shrink-0">
            <div class="card-body py-2">
                <div class="category-tabs" id="categoryFilter">
                    <button class="btn btn-sm btn-primary" data-cat="all">Tất cả</button>
                    @foreach($categories as $cat)
                        <button class="btn btn-sm btn-outline-secondary" data-cat="{{ $cat->id }}">{{ $cat->name }}</button>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="card shadow-sm flex-grow-1 overflow-hidden">
            <div class="card-body">
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="productSearch" placeholder="Tìm món (F3)">
                </div>
                <div class="pos-product-grid" id="productGrid">
                    @foreach($products as $p)
                    <div class="pos-product-item card border-0 shadow-sm product-item" data-cat="{{ $p->category_id }}" data-name="{{ strtolower($p->name) }}" onclick="addToCart({{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->price }})">
                        <div class="card-body p-2 text-center">
                            <div class="text-primary mb-1"><i class="bi bi-cup-straw fs-2"></i></div>
                            <div class="fw-bold small text-truncate">{{ $p->name }}</div>
                            <div class="small text-primary fw-bold">{{ number_format($p->price) }}đ</div>
                            <div class="small text-muted">Tồn: {{ $p->stock }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="pos-right">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                <h5 class="fw-bold mb-0"><i class="bi bi-cart3"></i> Giỏ hàng</h5>
                <button class="btn btn-sm btn-outline-danger" onclick="clearCart()" id="clearBtn" disabled><i class="bi bi-trash"></i></button>
            </div>
            <div class="card-body d-flex flex-column p-0">
                <div id="cartItems" class="pos-cart-list p-2">
                    <div class="text-muted text-center py-5">Chưa có sản phẩm</div>
                </div>
                <div class="border-top p-2 mt-auto bg-light">
                    <div class="pos-total-row fw-bold fs-5">
                        <span>Tổng tiền</span>
                        <span class="text-primary" id="cartTotal">0đ</span>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Phương thức thanh toán</label>
                        <select id="paymentMethod" class="form-select form-select-sm">
                            <option value="cash">Tiền mặt</option>
                            <option value="transfer">Chuyển khoản</option>
                            <option value="card">Thẻ</option>
                        </select>
                    </div>
                    <button id="checkoutBtn" class="btn btn-primary w-100 btn-lg" disabled onclick="checkout()">Thanh toán (F9)</button>
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
    const clearBtn = document.getElementById('clearBtn');
    if(!cart.length){ container.innerHTML = '<div class="text-muted text-center py-5">Chưa có sản phẩm</div>'; totalEl.textContent='0đ'; btn.disabled=true; clearBtn.disabled=true; return; }
    let total = 0;
    let html = '<ul class="list-group list-group-flush">';
    cart.forEach((item, idx) => { total += item.price * item.quantity; html += `<li class="list-group-item px-0 d-flex justify-content-between align-items-center">
            <div class="text-break" style="min-width:0;">
                <div class="fw-bold small">${item.name}</div>
                <div class="small text-muted">${item.price.toLocaleString()}đ x ${item.quantity}</div>
            </div>
            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                <button class="btn btn-sm btn-outline-secondary py-0" onclick="updateQty(${idx}, -1)">-</button>
                <span class="px-1 small">${item.quantity}</span>
                <button class="btn btn-sm btn-outline-secondary py-0" onclick="updateQty(${idx}, 1)">+</button>
                <button class="btn btn-sm btn-outline-danger py-0" onclick="removeItem(${idx})"><i class="bi bi-trash"></i></button>
            </div>
        </li>`; });
    html += '</ul>';
    container.innerHTML = html;
    totalEl.textContent = total.toLocaleString() + 'đ';
    btn.disabled = false;
    clearBtn.disabled = false;
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
    btn.disabled = false; btn.innerHTML = 'Thanh toán (F9)';
}

document.querySelectorAll('#categoryFilter button').forEach(b => b.addEventListener('click', function(){
    document.querySelectorAll('#categoryFilter button').forEach(x => { x.classList.remove('btn-primary'); x.classList.add('btn-outline-secondary'); });
    this.classList.remove('btn-outline-secondary'); this.classList.add('btn-primary');
    const cat = this.dataset.cat;
    filterProducts();
}));

document.getElementById('productSearch').addEventListener('input', filterProducts);
function filterProducts(){
    const cat = document.querySelector('#categoryFilter .btn-primary')?.dataset.cat || 'all';
    const q = document.getElementById('productSearch').value.trim().toLowerCase();
    document.querySelectorAll('.product-item').forEach(el => {
        const matchCat = cat === 'all' || el.dataset.cat === cat;
        const matchName = el.dataset.name.includes(q);
        el.style.display = (matchCat && matchName) ? 'block' : 'none';
    });
}

document.addEventListener('keydown', (e) => {
    if(e.key === 'F9'){ e.preventDefault(); checkout(); }
    if(e.key === 'F3'){ e.preventDefault(); document.getElementById('productSearch').focus(); }
});

document.addEventListener('DOMContentLoaded', loadCart);
window.addEventListener('storage', (e) => { if(e.key===STORAGE_KEY) loadCart(); });
</script>
@endpush
