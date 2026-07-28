@extends('layouts.app')

@section('title', 'Bán hàng - Bàn')

@section('content')
<style>
    .table-pos-wrap { display: flex; gap: .75rem; height: calc(100vh - 120px); min-height: 500px; }
    .tp-left { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: .75rem; }
    .tp-right { width: 380px; flex-shrink: 0; display: flex; flex-direction: column; }
    @media (max-width: 991.98px) {
        .table-pos-wrap { flex-direction: column; height: auto; }
        .tp-right { width: 100%; }
    }
    .area-tabs { display: flex; gap: .5rem; overflow-x: auto; padding-bottom: .25rem; }
    .area-tabs .btn { white-space: nowrap; }
    .table-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: .75rem; padding: .75rem; overflow-y: auto; }
    .table-card { border: 2px solid #e9ecef; border-radius: .75rem; padding: 1rem .5rem; text-align: center; cursor: pointer; transition: all .15s; background: #fff; }
    .table-card:hover { border-color: #0d6efd; transform: translateY(-2px); }
    .table-card.active { border-color: #0d6efd; background: #e7f1ff; }
    .table-card.playing { border-color: #198754; background: #e6f4ea; }
    .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: .75rem; padding: .75rem; overflow-y: auto; }
    .product-card { border: 1px solid #e9ecef; border-radius: .5rem; padding: .5rem; text-align: center; cursor: pointer; transition: all .15s; background: #fff; }
    .product-card:hover { border-color: #0d6efd; box-shadow: 0 .25rem .5rem rgba(0,0,0,.1); }
    .cart-list { flex: 1; overflow-y: auto; padding: .75rem; }
</style>

<div class="table-pos-wrap">
    <div class="tp-left">
        <!-- Table area tabs -->
        <div class="card shadow-sm">
            <div class="card-body py-2">
                <div class="area-tabs" id="areaTabs">
                    <button class="btn btn-sm btn-primary" data-area="all" onclick="filterArea('all')">Tất cả</button>
                    @foreach($tables->keys() as $area)
                        <button class="btn btn-sm btn-outline-secondary" data-area="{{ $area }}" onclick="filterArea('{{ $area }}')">{{ $area }}</button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Tables grid -->
        <div class="card shadow-sm flex-grow-1 overflow-hidden">
            <div class="card-body d-flex flex-column">
                <h6 class="fw-bold mb-2">Chọn bàn</h6>
                <div class="table-grid flex-grow-1" id="tableGrid">
                    <!-- Take away card -->
                    <div class="table-card active" data-id="0" data-area="" data-name="Mang về" onclick="selectTable(0, 'Mang về', 0, false, null)">
                        <div class="text-primary mb-1"><i class="bi bi-bag fs-2"></i></div>
                        <div class="fw-bold small">Mang về</div>
                    </div>
                    @foreach($tables as $area => $items)
                        @foreach($items as $t)
                        @php $session = $sessions->get($t->id); @endphp
                        <div class="table-card {{ $t->status=='playing' ? 'playing' : '' }}" data-area="{{ $area }}" data-id="{{ $t->id }}" onclick="selectTable({{ $t->id }}, '{{ addslashes($t->name) }}', {{ $t->price_per_hour }}, {{ $t->status=='playing' ? 'true' : 'false' }}, {{ $session ? '{started_at:"' . $session->started_at->format('Y-m-d H:i:s') . '"}' : 'null' }})">
                            <div class="text-muted mb-1"><i class="bi bi-table fs-2"></i></div>
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
        </div>

        <!-- Products -->
        <div class="card shadow-sm flex-grow-1 overflow-hidden">
            <div class="card-body d-flex flex-column">
                <div class="d-flex gap-2 overflow-auto mb-2" id="categoryTabs">
                    <button class="btn btn-sm btn-primary" data-cat="all" onclick="filterCategory('all')">Tất cả</button>
                    @foreach($categories as $cat)
                        <button class="btn btn-sm btn-outline-secondary" data-cat="{{ $cat->id }}" onclick="filterCategory({{ $cat->id }})">{{ $cat->name }}</button>
                    @endforeach
                </div>
                <div class="input-group mb-2">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="productSearch" placeholder="Tìm món (F3)" onkeyup="filterProducts()">
                </div>
                <div class="product-grid flex-grow-1 overflow-y-auto" id="productGrid">
                    @foreach($products as $p)
                    <div class="product-card" data-cat="{{ $p->category_id }}" data-name="{{ strtolower($p->name) }}" onclick="addToCart({{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->price }})">
                        <div class="text-primary mb-1"><i class="bi bi-cup-straw fs-2"></i></div>
                        <div class="fw-bold small text-truncate">{{ $p->name }}</div>
                        <div class="small text-primary fw-bold">{{ number_format($p->price) }}đ</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="tp-right">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                <div>
                    <h6 class="fw-bold mb-0" id="selectedTableName">Mang về</h6>
                    <small class="text-muted" id="tableInfo">Đơn hàng mang về</small>
                </div>
                <span id="tableStatus" class="badge bg-secondary">Mang về</span>
            </div>
            <div class="card-body d-flex flex-column p-0">
                <div id="cartItems" class="cart-list">
                    <div class="text-muted text-center py-5">Chưa có sản phẩm</div>
                </div>
                <div class="border-top p-2 bg-light mt-auto">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold">Tổng tiền</span>
                        <span class="fw-bold fs-5 text-primary" id="cartTotal">0đ</span>
                    </div>
                    <div class="mb-2">
                        <select id="paymentMethod" class="form-select form-select-sm">
                            <option value="cash">Tiền mặt</option>
                            <option value="transfer">Chuyển khoản</option>
                            <option value="card">Thẻ</option>
                        </select>
                    </div>
                    <div class="d-grid gap-2">
                        <button id="startBtn" class="btn btn-primary btn-sm" onclick="startTable()" style="display:none"><i class="bi bi-play-fill"></i> Mở bàn</button>
                        <button id="closeBtn" class="btn btn-success btn-sm" onclick="closeTable()" disabled><i class="bi bi-cash-coin"></i> Thanh toán (F9)</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedTable = {id:0, name:'Mang về', price:0, playing:false, session:null};
let cart = [];
const STORAGE_KEY = 'table_pos_cart';

document.addEventListener('DOMContentLoaded', () => {
    loadCart();
    updateTimers();
    setInterval(updateTimers, 60000);
});

function selectTable(id, name, price, playing, session){
    selectedTable = {id, name, price, playing, session};
    document.querySelectorAll('.table-card').forEach(el => el.classList.remove('active'));
    document.querySelector(`.table-card[data-id="${id}"]`)?.classList.add('active');
    document.getElementById('selectedTableName').textContent = name;
    document.getElementById('tableStatus').textContent = id === 0 ? 'Mang về' : (playing ? 'Đang chơi' : 'Trống');
    document.getElementById('tableStatus').className = id === 0 ? 'badge bg-info' : (playing ? 'badge bg-success' : 'badge bg-secondary');

    const startBtn = document.getElementById('startBtn');
    const closeBtn = document.getElementById('closeBtn');
    if(id === 0){
        startBtn.style.display = 'none';
        closeBtn.disabled = false;
        document.getElementById('tableInfo').textContent = 'Đơn hàng mang về';
    } else if(playing){
        startBtn.style.display = 'none';
        closeBtn.disabled = false;
        const start = new Date(session.started_at);
        document.getElementById('tableInfo').innerHTML = `Giờ vào: ${start.toLocaleTimeString()}`;
    } else {
        startBtn.style.display = 'block';
        closeBtn.disabled = true;
        document.getElementById('tableInfo').textContent = 'Bàn trống - nhấn Mở bàn';
    }
    loadCart();
}

function loadCart(){
    const all = JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
    cart = all[selectedTable.id] || [];
    renderCart();
}
function saveCart(){
    const all = JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
    all[selectedTable.id] = cart;
    localStorage.setItem(STORAGE_KEY, JSON.stringify(all));
    renderCart();
}
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
    if(!selectedTable || selectedTable.id === 0) return;
    await fetchPost(`{{ url('staff/table-pos') }}/${selectedTable.id}/start`);
    window.location.reload();
}
async function closeTable(){
    if(!selectedTable) return;
    if(selectedTable.id !== 0 && !selectedTable.playing && !confirm('Bàn chưa mở, có muốn thanh toán không?')) return;
    if(cart.length){
        await fetchPost(`{{ url('staff/table-pos') }}/${selectedTable.id}/order`, {items: cart.map(i => ({product_id:i.product_id, quantity:i.quantity})), payment_method: document.getElementById('paymentMethod').value});
    }
    if(selectedTable.id !== 0 && selectedTable.playing){
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('staff/table-pos') }}/${selectedTable.id}/close`;
        form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="payment_method" value="${document.getElementById('paymentMethod').value}">`;
        document.body.appendChild(form);
        form.submit();
    } else {
        clearCart();
        alert('Thanh toán thành công');
        window.location.reload();
    }
}

async function fetchPost(url, body){
    const res = await fetch(url, {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body: body ? JSON.stringify(body) : ''});
    return res.json();
}

function filterArea(area){
    document.querySelectorAll('#areaTabs button').forEach(b => { b.classList.remove('btn-primary'); b.classList.add('btn-outline-secondary'); });
    document.querySelector(`#areaTabs button[data-area="${area}"]`)?.classList.remove('btn-outline-secondary');
    document.querySelector(`#areaTabs button[data-area="${area}"]`)?.classList.add('btn-primary');
    document.querySelectorAll('.table-card').forEach(el => el.style.display = (area === 'all' || el.dataset.area === area) ? 'block' : 'none');
}
function filterCategory(cat){
    document.querySelectorAll('#categoryTabs button').forEach(b => { b.classList.remove('btn-primary'); b.classList.add('btn-outline-secondary'); });
    document.querySelector(`#categoryTabs button[data-cat="${cat}"]`)?.classList.remove('btn-outline-secondary');
    document.querySelector(`#categoryTabs button[data-cat="${cat}"]`)?.classList.add('btn-primary');
    const q = document.getElementById('productSearch').value.trim().toLowerCase();
    document.querySelectorAll('#productGrid .product-card').forEach(el => {
        el.style.display = (cat === 'all' || el.dataset.cat == cat) && el.dataset.name.includes(q) ? 'block' : 'none';
    });
}
function filterProducts(){
    const q = document.getElementById('productSearch').value.trim().toLowerCase();
    const cat = document.querySelector('#categoryTabs .btn-primary')?.dataset.cat || 'all';
    document.querySelectorAll('#productGrid .product-card').forEach(el => {
        el.style.display = (cat === 'all' || el.dataset.cat == cat) && el.dataset.name.includes(q) ? 'block' : 'none';
    });
}

document.addEventListener('keydown', (e) => {
    if(e.key === 'F9'){ e.preventDefault(); closeTable(); }
    if(e.key === 'F3'){ e.preventDefault(); document.getElementById('productSearch').focus(); }
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
