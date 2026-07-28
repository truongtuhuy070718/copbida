<div class="sidebar bg-dark" id="sidebar">
    <div class="d-flex flex-column h-100">
        <a href="#" class="d-flex align-items-center gap-2 p-3 text-white text-decoration-none">
            <i class="bi bi-box-seam fs-4"></i>
            <span class="fw-bold">Bida Manager</span>
        </a>
        <hr class="text-secondary mx-3 my-2">
        <ul class="nav nav-pills flex-column mb-auto px-2">
            @if(auth()->user()->role === 'admin')
            <li class="nav-item mb-1">
                <a href="{{ route('admin.dashboard') }}" class="nav-link rounded {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i> Tổng quan
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="{{ route('admin.tables.index') }}" class="nav-link rounded {{ request()->routeIs('admin.tables.*') ? 'active' : '' }}">
                    <i class="bi bi-table me-2"></i> Quản lý bàn
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="{{ route('admin.products.index') }}" class="nav-link rounded {{ request()->routeIs('admin.products.*','admin.categories.*','admin.inventory.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam me-2"></i> Sản phẩm & Kho
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="{{ route('admin.staff.index') }}" class="nav-link rounded {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                    <i class="bi bi-people me-2"></i> Nhân viên
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="{{ route('admin.reports.index') }}" class="nav-link rounded {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up me-2"></i> Báo cáo
                </a>
            </li>
            @endif
            @if(auth()->user()->role === 'staff')
            <li class="nav-item mb-1">
                <a href="{{ route('staff.pos') }}" class="nav-link rounded {{ request()->routeIs('staff.pos') ? 'active' : '' }}">
                    <i class="bi bi-cart3 me-2"></i> POS Bán hàng
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="{{ route('staff.tables') }}" class="nav-link rounded {{ request()->routeIs('staff.tables') ? 'active' : '' }}">
                    <i class="bi bi-table me-2"></i> Sơ đồ bàn
                </a>
            </li>
            @endif
        </ul>
        <div class="p-3 text-white-50 small">
            &copy; Bida Manager
        </div>
    </div>
</div>
