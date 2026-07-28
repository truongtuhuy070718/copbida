<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Bida Manager'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    <style>
        :root { --sidebar-width: 260px; }
        html, body { min-height: 100vh; overflow-x: hidden; background: #f4f6f9; }
        .sidebar { width: var(--sidebar-width); min-height: 100vh; position: fixed; top: 0; left: 0; z-index: 1040; transition: transform .25s ease; }
        .main-content { margin-left: var(--sidebar-width); min-width: 0; transition: margin .25s ease; }
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
        .nav-link { color: rgba(255,255,255,.8); }
        .nav-link:hover, .nav-link.active { color: #fff; background: rgba(255,255,255,.1); }
        .card-table { transition: transform .15s, box-shadow .15s; cursor: pointer; }
        .card-table:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.1); }
        .status-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        [data-hx-request] .htmx-indicator { display: inline-block; }
        .htmx-indicator { display: none; }
        .product-card .card-body { word-break: break-word; }
    </style>
</head>
<body>
    @auth
    @include('layouts.partials.sidebar')
    @endauth

    <div class="main-content w-100" id="mainContent">
        @auth
        <nav class="navbar navbar-expand navbar-light bg-white shadow-sm sticky-top">
            <div class="container-fluid">
                <button class="btn btn-light d-lg-none me-2" id="sidebarToggle"><i class="bi bi-list"></i></button>
                <span class="navbar-brand mb-0 h6 d-none d-lg-block">{{ config('app.name', 'Bida Manager') }}</span>
                <div class="ms-auto d-flex align-items-center gap-2 gap-md-3">
                    <span class="text-muted small d-none d-md-block">{{ auth()->user()?->name }} ({{ auth()->user()?->role }})</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right"></i></button>
                    </form>
                </div>
            </div>
        </nav>
        @endauth
        <div class="container-fluid py-3" id="appContainer">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle')?.addEventListener('click', () => {
            document.querySelector('.sidebar')?.classList.toggle('show');
        });
        document.body.addEventListener('htmx:configRequest', function(evt) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (token) evt.detail.headers['X-CSRF-TOKEN'] = token;
        });
    </script>
    @stack('scripts')
</body>
</html>
