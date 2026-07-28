@extends('layouts.app')

@section('title', 'Bàn & POS')

@section('content')
<style>
    .table-pos-wrap { display: flex; gap: .75rem; height: calc(100vh - 120px); min-height: 500px; }
    .tp-section { background: #fff; border-radius: .5rem; box-shadow: 0 .125rem .25rem rgba(0,0,0,.075); display: flex; flex-direction: column; overflow: hidden; }
    .tp-tables { width: 220px; flex-shrink: 0; }
    .tp-products { flex: 1; min-width: 0; }
    .tp-cart { width: 360px; flex-shrink: 0; }
    @media (max-width: 1199.98px) {
        .table-pos-wrap { flex-direction: column; height: auto; }
        .tp-tables, .tp-cart { width: 100%; }
    }
    .table-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: .5rem; padding: .75rem; overflow-y: auto; }
    .table-item { border: 2px solid #e9ecef; border-radius: .5rem; padding: .75rem .5rem; text-align: center; cursor: pointer; transition: all .15s; }
    .table-item:hover { border-color: #0d6efd; }
    .table-item.active { border-color: #0d6efd; background: #e7f1ff; }
    .table-item.playing { border-color: #198754; background: #e6f4ea; }
    .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: .75rem; padding: .75rem; overflow-y: auto; }
    .product-item { cursor: pointer; border: 1px solid #e9ecef; border-radius: .5rem; padding: .5rem; text-align: center; transition: all .15s; }
    .product-item:hover { border-color: #0d6efd; box-shadow: 0 .25rem .5rem rgba(0,0,0,.1); }
    .cart-list { flex: 1; overflow-y: auto; padding: .75rem; }
    .area-tab { white-space: nowrap; }
</style>

<div class="table-pos-wrap">
    <!-- Left: Tables -->
    <div class="tp-section tp-tables">
        <div class="p-2 border-bottom bg-light">
            <h6 class="fw-bold mb-0">Sơ đồ bàn</h6>
        </div>
        <div class="p-2 border-bottom">
            <select class="form-select form-select-sm" id="areaFilter" onchange="filterArea(this.value)">
                <option value="all">Tất cả khu</option>
                @foreach($tables->keys() as $area)
                    <option value="{{ $area }}">{{ $area }}</option>
                @endforeach
            </select>
        </div>
        <div class="table-grid flex-grow-1" id="tableGrid">
            @foreach($tables as $area => $items)
                @foreach($items as $t)
                @php $session = $sessions->get($t->id); @endphp
                <div class="table-item {{ $t->status=='playing' ? 'playing' : '' }}" data-area="{{ $area }}" data-id="{{ $t->id }}" data-playing="{{ $t->status=='playing' ? '1' : '0' }}" onclick="selectTable({{ $t->id }}, '{{ addslashes($t->name) }}', {{ $t->price_per_hour }}, {{ $t->status=='playing' ? 'true' : 'false' }}, {{ $session ? '{started_at:"' . $session->started_at->format('Y-m-d H:i:s') . '"}' : 'null' }})">
                    <div class="fw-bold small">{{ $t->name }}</div>
                    <div class="small text-muted">{{ number_format($t->price_per_hour) }}đ/h</div>
                    @if($session)
                        <div class="small text-success timer" data-start="{{ $session->started_at->timestamp }}">00:00</div>
                    @endif
                </div>
                @endforeach
            @endforeach
        </div>
    </div>

    <!-- Center: Products -->
    <div class="tp-section tp-products">
        <div class="p-2 border-bottom bg-light d-flex gap-2 overflow-auto">
            <button class="btn btn-sm btn-primary area-tab" data-cat="all" onclick="filterProductCategory('all')">Tất cả</button>
            @foreach($categories as $cat)
                <button class="btn btn-sm btn-outline-secondary area-tab" data-cat="{{ $cat->id }}" onclick="filterProductCategory({{ $cat->id }})">{{ $cat->name }}</button>
            @endforeach
        </div>
        <div class="p-2">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" id="productSearch" placeholder="Tìm món..." onkeyup="filterProducts()">
            </div>
        </div>
        <div class="product-grid flex-grow-1" id="productGrid">
            @foreach($products as $p)
            <div class="product-item" data-cat="{{ $p->category_id }}" data-name="{{ strtolower($p->name) }}" onclick="addToCart({{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->price }})">
                <div class="text-primary mb-1"><i class="bi bi-cup-straw fs-4"></i></div>
                <div class="fw-bold small text-truncate">{{ $p->name }}</div>
                <div class="small text-primary fw-bold">{{ number_format($p->price) }}đ</div>
                <div class="small text-muted">Tồn: {{ $p->stock }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Right: Cart / Table details -->
    <div class="tp-section tp-cart">
        <div class="p-2 border-bottom bg-light d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0" id="selectedTableName">Chưa chọn bàn</h6>
            <span id="tableStatus" class="badge bg-secondary">Trống</span>
        </div>
        <div class="p-2 border-bottom bg-white">
            <div id="tableInfo" class="small text-muted">Chọn một bàn để bắt đầu</div>
        </div>
        <div id="cartItems" class="cart-list">
            <div class="text-muted text-center py-5">Chưa có sản phẩm</div>
        </div>
        <div class="border-top p-2 bg-light mt-auto">
            <div class="d-flex justify-content-between fw-bold fs-5 mb-2">
                <span>Tổng tiền</span>
                <span class="text-primary" id="cartTotal">0đ</span>
            </div>
            <div class="mb-2">
                <select id="paymentMethod" class="form-select form-select-sm">
                    <option value="cash">Tiền mặt</option>
                    <option value="transfer">Chuyển khoản</option>
                    <option value="card">Thẻ</option>
                </select>
            </div>
            <div class="d-grid gap-2">
                <button id="startBtn" class="btn btn-primary btn-sm" onclick="startTable()" disabled><i class="bi bi-play-fill"></i> Mở bàn</button>
                <button id="closeBtn" class="btn btn-success btn-sm" onclick="closeTable()" disabled><i class="bi bi-cash-coin"></i> Tính tiền</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedTable = null;
let cart = [];
const STORAGE_KEY = 'table_pos_cart';

function selectTable(id, name, price, playing, session){
    selectedTable = {id, name, price, playing, session};
    document.querySelectorAll('.table-item').forEach(el => el.classList.remove('active'));
    document.querySelector(`.table-item[data-id="${id}"]`)?.classList.add('active');
    document.getElementById('selectedTableName').textContent = name;
    document.getElementById('tableStatus').textContent = playing ? 'Đang chơi' : 'Trống';
    document.getElementById('tableStatus').className = playing ? 'badge bg-success' : 'badge bg-secondary';
    document.getElementById('startBtn').disabled = playing;
    document.getElementById('closeBtn').disabled = !playing;

    if(playing && session){
        const start = new Date(session.started_at);
        const diffMin = Math.floor((Date.now() - start) / 60000);
        const h = Math.floor(diffMin / 60), m = diffMin % 60;
        document.getElementById('tableInfo').innerHTML = `Giờ bắt đầu: ${start.toLocaleTimeString()}<br>Thời gian: ${h}h ${m}m`;
    } else {
        document.getElementById('tableInfo').textContent = 'Bàn trống - nhấn Mở bàn để bắt đầu';
    }

    loadCart();
}

function loadCart(){
    const all = JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
    cart = selectedTable ? (all[selectedTable.id] || []) : [];
    renderCart();
}
function saveCart(){
    const all = JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
    if(selectedTable) all[selectedTable.id] = cart;
    localStorage.setItem(STORAGE_KEY, JSON.stringify(all));
    renderCart();
}
function addToCart(id, name, price){
    if(!selectedTable) return alert('Vui lòng chọn bàn trước');
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
    if(!cart.length){ container.innerHTML = '<div class="text-muted text-center py-5">Chưa có sản phẩm</div>'; totalEl.textContent='0đ'; return; }
    let total = 0;
    let html = '<ul class="list-group list-group-flush">';
    cart.forEach((item, idx) => { total += item.price * item.quantity; html += `<li class="list-group-item px-0 d-flex justify-content-between align-items-center">
            <div style="min-width:0;"><div class="fw-bold small text-truncate">${item.name}</div><div class="small text-muted">${item.price.toLocaleString()}đ x ${item.quantity}</div></div>
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
}

async function startTable(){
    if(!selectedTable) return;
    await fetchPost(`{{ url('staff/table-pos') }}/${selectedTable.id}/start`);
    window.location.reload();
}
async function closeTable(){
    if(!selectedTable || !cart.length){
        if(!confirm('Giỏ hàng trống, vẫn tính tiền giờ chơi?')) return;
    }
    if(cart.length){
        await fetchPost(`{{ url('staff/table-pos') }}/${selectedTable.id}/order`, {items: cart.map(i => ({product_id:i.product_id, quantity:i.quantity})), payment_method: document.getElementById('paymentMethod').value});
    }
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `{{ url('staff/table-pos') }}/${selectedTable.id}/close`;
    form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="payment_method" value="${document.getElementById('paymentMethod').value}">`;
    document.body.appendChild(form);
    form.submit();
}

async function fetchPost(url, body){
    const res = await fetch(url, {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body: body ? JSON.stringify(body) : ''});
    return res.json();
}

function filterArea(area){
    document.querySelectorAll('.table-item').forEach(el => el.style.display = (area === 'all' || el.dataset.area === area) ? 'block' : 'none');
}
function filterProductCategory(cat){
    document.querySelectorAll('#productGrid .product-item').forEach(el => el.style.display = (cat === 'all' || el.dataset.cat == cat) ? 'block' : 'none');
}
function filterProducts(){
    const q = document.getElementById('productSearch').value.trim().toLowerCase();
    document.querySelectorAll('#productGrid .product-item').forEach(el => el.style.display = el.dataset.name.includes(q) ? 'block' : 'none');
}

document.addEventListener('DOMContentLoaded', () => {
    updateTimers();
    setInterval(updateTimers, 60000);
});
function updateTimers(){
    document.querySelectorAll('.timer').forEach(el => {
        const start = parseInt(el.dataset.start) * 1000;
        const diff = Math.floor((Date.now() - start) / 60000);
        const h = Math.floor(diff / 60), m = diff % 60;
        el.textContent = `${h}h ${m}m`;
    });
}
</script>
@endpush
