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
    .kiot-nav { background: #0d6efd; padding: .5rem; border-radius: .5rem; display: flex; gap: .5rem; align-items: center; }
    .kiot-tab { border: none; background: transparent; color: rgba(255,255,255,.75); font-weight: 500; padding: .5rem 1rem; border-radius: 2rem; white-space: nowrap; }
    .kiot-tab.active { background: rgba(255,255,255,.2); color: #fff; }
    .search-bar { background: rgba(255,255,255,.2); border-radius: 2rem; padding: .25rem .75rem; color: #fff; display: flex; align-items: center; gap: .5rem; flex-grow: 1; }
    .search-bar input { background: transparent; border: none; color: #fff; outline: none; width: 100%; }
    .search-bar input::placeholder { color: rgba(255,255,255,.75); }
    .search-bar i { color: #fff; }
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
        <!-- KiotViet style top nav -->
        <div class="kiot-nav" id="kiotNav">
            <button class="kiot-tab active" id="tabTables" data-tab="tables">
                <i class="bi bi-grid"></i> Phòng bàn
            </button>
            <button class="kiot-tab" id="tabMenu" data-tab="menu">
                <i class="bi bi-journal-text"></i> Thực đơn
            </button>
            <div class="search-bar" id="searchBar">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control border-0 p-0" id="productSearch" placeholder="Tìm món (F3)">
            </div>
        </div>

        <!-- Tables section -->
        <div id="tablesSection">
            <div class="card shadow-sm">
                <div class="card-body py-2">
                    <div class="area-tabs" id="areaTabs">
                        <button class="btn btn-sm btn-primary" data-area="all">Tất cả</button>
                        @foreach($tables->keys() as $area)
                            <button class="btn btn-sm btn-outline-secondary" data-area="{{ $area }}">{{ $area }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card shadow-sm flex-grow-1 overflow-hidden">
                <div class="card-body d-flex flex-column">
                    <h6 class="fw-bold mb-2">Chọn bàn</h6>
                    <div class="table-grid flex-grow-1" id="tableGrid">
                        <div class="table-card active" data-id="0" data-area="" data-name="Mang về" data-price="0" data-playing="false">
                            <div class="text-primary mb-1"><i class="bi bi-bag fs-2"></i></div>
                            <div class="fw-bold small">Mang về</div>
                        </div>
                        @foreach($tables as $area => $items)
                            @foreach($items as $t)
                            @php $session = $sessions->get($t->id); @endphp
                            <div class="table-card {{ $t->status=='playing' ? 'playing' : '' }}" data-area="{{ $area }}" data-id="{{ $t->id }}" data-name="{{ $t->name }}" data-price="{{ $t->price_per_hour }}" data-playing="{{ $t->status=='playing' ? 'true' : 'false' }}" data-session-start="{{ $session ? $session->started_at->format('Y-m-d H:i:s') : '' }}">
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
        </div>

        <!-- Products section -->
        <div id="menuSection" class="flex-grow-1 overflow-hidden" style="display:none">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="input-group mb-2">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="productSearch2" placeholder="Tìm món">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" id="categoryDropdownBtn">Tất cả</button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" data-cat="all">Tất cả</a></li>
                            @foreach($categories as $cat)
                            <li><a class="dropdown-item" href="#" data-cat="{{ $cat->id }}">{{ $cat->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="product-grid flex-grow-1 overflow-y-auto d-none" id="productGrid">
                        @foreach($products as $p)
                        <div class="product-card" data-cat="{{ $p->category_id }}" data-name="{{ strtolower($p->name) }}" data-id="{{ $p->id }}" data-price="{{ $p->price }}">
                            <div class="text-primary mb-1"><i class="bi bi-cup-straw fs-2"></i></div>
                            <div class="fw-bold small text-truncate">{{ $p->name }}</div>
                            <div class="small text-primary fw-bold">{{ number_format($p->price) }}đ</div>
                        </div>
                        @endforeach
                    </div>
                    <div id="emptySearch" class="flex-grow-1 d-flex flex-column align-items-center justify-content-center text-muted">
                        <i class="bi bi-search fs-1 mb-3 text-muted opacity-50"></i>
                        <p>Nhập tên món hoặc chọn danh mục để tìm kiếm</p>
                    </div>
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
                        <button id="startBtn" class="btn btn-primary btn-sm" disabled><i class="bi bi-play-circle"></i> Mở bàn (F4)</button>
                        <button id="closeBtn" class="btn btn-success btn-sm" disabled><i class="bi bi-cash-coin"></i> Thanh toán (F9)</button>
                        <button id="cancelBtn" class="btn btn-outline-warning btn-sm" disabled><i class="bi bi-x-circle"></i> Hủy</button>
                        <button id="transferBtn" class="btn btn-outline-info btn-sm" disabled><i class="bi bi-arrow-left-right"></i> Chuyển bàn</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transfer Modal -->
<div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chuyển bàn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Chọn bàn đích</label>
                <select id="transferToTable" class="form-select">
                    <option value="">-- Chọn bàn --</option>
                    @foreach($tables as $area => $items)
                        <optgroup label="{{ $area }}">
                            @foreach($items as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" onclick="transferTable()">Xác nhận</button>
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
    bindEvents();
    loadCart();
    updateTimers();
    setInterval(updateTimers, 60000);
});

function bindEvents(){
    document.getElementById('tabTables').addEventListener('click', () => switchTab('tables'));
    document.getElementById('tabMenu').addEventListener('click', () => switchTab('menu'));
    document.getElementById('searchBar').addEventListener('click', () => document.getElementById('productSearch').focus());
    document.getElementById('productSearch').addEventListener('keyup', (e) => { filterProducts(); if(e.target.value.trim()) switchTab('menu'); });
    document.getElementById('productSearch2').addEventListener('keyup', filterProducts2);

    document.querySelectorAll('#areaTabs button').forEach(btn => btn.addEventListener('click', () => filterArea(btn.dataset.area)));
    document.querySelectorAll('#tableGrid .table-card').forEach(card => card.addEventListener('click', () => {
        const sessionStart = card.dataset.sessionStart;
        selectTable(parseInt(card.dataset.id), card.dataset.name, parseFloat(card.dataset.price), card.dataset.playing === 'true', sessionStart ? {started_at: sessionStart} : null);
    }));

    document.querySelectorAll('.dropdown-item').forEach(item => item.addEventListener('click', (e) => { e.preventDefault(); filterCategory(item.dataset.cat); }));
    document.querySelectorAll('#productGrid .product-card').forEach(card => card.addEventListener('click', () => addToCart(parseInt(card.dataset.id), card.dataset.name, parseFloat(card.dataset.price))));

    document.getElementById('startBtn').addEventListener('click', startTable);
    document.getElementById('closeBtn').addEventListener('click', closeTable);
    document.getElementById('cancelBtn').addEventListener('click', cancelTable);
    document.getElementById('transferBtn').addEventListener('click', showTransferModal);
}

function switchTab(tab){
    document.getElementById('tabTables').classList.toggle('active', tab === 'tables');
    document.getElementById('tabMenu').classList.toggle('active', tab === 'menu');
    document.getElementById('tablesSection').classList.toggle('d-none', tab !== 'tables');
    const menuSection = document.getElementById('menuSection');
    if(tab === 'menu'){
        menuSection.style.display = 'flex';
        menuSection.classList.remove('d-none');
        filterCategory('all');
    } else {
        menuSection.style.display = 'none';
        menuSection.classList.add('d-none');
    }
}

function selectTable(id, name, price, playing, session){
    selectedTable = {id, name, price, playing, session};
    document.querySelectorAll('.table-card').forEach(el => el.classList.remove('active'));
    document.querySelector(`.table-card[data-id="${id}"]`)?.classList.add('active');
    document.getElementById('selectedTableName').textContent = name;
    document.getElementById('tableStatus').textContent = id === 0 ? 'Mang về' : (playing ? 'Đang chơi' : 'Trống');
    document.getElementById('tableStatus').className = id === 0 ? 'badge bg-info' : (playing ? 'badge bg-success' : 'badge bg-secondary');

    const startBtn = document.getElementById('startBtn');
    const closeBtn = document.getElementById('closeBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const transferBtn = document.getElementById('transferBtn');
    if(id === 0){
        startBtn.disabled = true;
        closeBtn.disabled = false;
        cancelBtn.disabled = true;
        transferBtn.disabled = true;
        document.getElementById('tableInfo').textContent = 'Đơn hàng mang về';
    } else if(playing){
        startBtn.disabled = true;
        closeBtn.disabled = false;
        cancelBtn.disabled = false;
        transferBtn.disabled = false;
        const start = new Date(session.started_at);
        document.getElementById('tableInfo').innerHTML = `Giờ vào: ${start.toLocaleTimeString()}`;
    } else {
        startBtn.disabled = false;
        closeBtn.disabled = true;
        cancelBtn.disabled = true;
        transferBtn.disabled = true;
        document.getElementById('tableInfo').textContent = 'Bàn trống - bấm Mở bàn';
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
function clearCart(){ 
    cart = []; 
    const all = JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
    if(selectedTable) delete all[selectedTable.id];
    localStorage.setItem(STORAGE_KEY, JSON.stringify(all));
    renderCart(); 
}
function renderCart(){
    const container = document.getElementById('cartItems');
    const totalEl = document.getElementById('cartTotal');
    if(!cart.length){ container.innerHTML = '<div class="text-muted text-center py-5">Chưa có sản phẩm</div>'; totalEl.textContent='0đ'; return; }
    let total = 0;
    let html = '<ul class="list-group list-group-flush">';
    cart.forEach((item, idx) => { total += item.price * item.quantity; html += `<li class="list-group-item px-0 d-flex justify-content-between align-items-center">
            <div style="min-width:0;"><div class="fw-bold small text-truncate">${item.name}</div><div class="small text-muted">${item.price.toLocaleString()}đ x ${item.quantity}</div></div>
            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                <button class="btn btn-sm btn-outline-secondary py-0 qty-btn" data-d="-1" data-idx="${idx}">-</button>
                <span class="px-1 small">${item.quantity}</span>
                <button class="btn btn-sm btn-outline-secondary py-0 qty-btn" data-d="1" data-idx="${idx}">+</button>
                <button class="btn btn-sm btn-outline-danger py-0 del-btn" data-idx="${idx}"><i class="bi bi-trash"></i></button>
            </div>
        </li>`; });
    html += '</ul>';
    container.innerHTML = html;
    totalEl.textContent = total.toLocaleString() + 'đ';

    document.querySelectorAll('.qty-btn').forEach(btn => btn.addEventListener('click', () => updateQty(parseInt(btn.dataset.idx), parseInt(btn.dataset.d))));
    document.querySelectorAll('.del-btn').forEach(btn => btn.addEventListener('click', () => removeItem(parseInt(btn.dataset.idx))));
}

async function startTable(){
    if(!selectedTable || selectedTable.id === 0) return;
    const res = await fetchPost(`{{ url('staff/pos') }}/${selectedTable.id}/start`);
    if(res.success){
        const card = document.querySelector(`.table-card[data-id="${selectedTable.id}"]`);
        if(card){
            card.classList.add('playing');
            card.dataset.playing = 'true';
            if(!card.dataset.sessionStart) card.dataset.sessionStart = new Date().toISOString();
        }
        selectedTable.playing = true;
        selectedTable.session = {started_at: card?.dataset.sessionStart || new Date().toISOString()};
        selectTable(selectedTable.id, selectedTable.name, selectedTable.price, true, selectedTable.session);
    } else {
        alert(res.message || 'Không thể mở bàn');
    }
}
async function closeTable(){
    if(!selectedTable) return;
    if(cart.length){
        await fetchPost(`{{ url('staff/pos') }}/${selectedTable.id}/order`, {items: cart.map(i => ({product_id:i.product_id, quantity:i.quantity})), payment_method: document.getElementById('paymentMethod').value});
    }
    if(selectedTable.id !== 0 && selectedTable.playing){
        clearCart();
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('staff/pos') }}/${selectedTable.id}/close`;
        form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="payment_method" value="${document.getElementById('paymentMethod').value}">`;
        document.body.appendChild(form);
        form.submit();
    } else {
        clearCart();
        alert('Thanh toán thành công');
        window.location.reload();
    }
}
async function cancelTable(){
    if(!selectedTable || selectedTable.id === 0) return;
    if(!confirm('Hủy bàn này?')) return;
    await fetchPost(`{{ url('staff/pos') }}/${selectedTable.id}/cancel`);
    clearCart();
    window.location.reload();
}
function showTransferModal(){
    if(!selectedTable || selectedTable.id === 0) return;
    const modal = new bootstrap.Modal(document.getElementById('transferModal'));
    modal.show();
}
async function transferTable(){
    const toTableId = document.getElementById('transferToTable').value;
    if(!toTableId) return alert('Vui lòng chọn bàn đích');
    await fetchPost(`{{ url('staff/pos') }}/${selectedTable.id}/transfer`, {to_table_id: parseInt(toTableId)});
    window.location.reload();
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
    const item = document.querySelector(`.dropdown-item[data-cat="${cat}"]`);
    document.getElementById('categoryDropdownBtn').textContent = item ? item.textContent : 'Tất cả';
    const q = document.getElementById('productSearch2').value.trim().toLowerCase();
    showProductsGrid(true);
    document.querySelectorAll('#productGrid .product-card').forEach(el => {
        el.style.display = (cat === 'all' || el.dataset.cat == cat) && el.dataset.name.includes(q) ? 'block' : 'none';
    });
}
function filterProducts(){
    const q = document.getElementById('productSearch').value.trim().toLowerCase();
    document.querySelectorAll('#productGrid .product-card').forEach(el => {
        el.style.display = el.dataset.name.includes(q) ? 'block' : 'none';
    });
    showProductsGrid(q !== '');
}
function filterProducts2(){
    const q = document.getElementById('productSearch2').value.trim().toLowerCase();
    if(q === ''){ showProductsGrid(false); return; }
    showProductsGrid(true);
    document.querySelectorAll('#productGrid .product-card').forEach(el => {
        el.style.display = el.dataset.name.includes(q) ? 'block' : 'none';
    });
}
function showProductsGrid(show){
    document.getElementById('productGrid').classList.toggle('d-none', !show);
    document.getElementById('emptySearch').classList.toggle('d-none', show);
}

document.addEventListener('keydown', (e) => {
    if(e.key === 'F9'){ e.preventDefault(); closeTable(); }
    if(e.key === 'F4'){ e.preventDefault(); startTable(); }
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
