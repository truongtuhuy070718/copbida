<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Bida Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #1e3c72; background: linear-gradient(135deg, #1e3c72, #2a5298); min-height: 100vh; }
        .login-card { border-radius: 1rem; box-shadow: 0 1rem 3rem rgba(0,0,0,.25); }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">
    <div class="login-card bg-white p-4 p-md-5" style="width:100%;max-width:400px;">
        <div class="text-center mb-4">
            <i class="bi bi-box-seam text-primary" style="font-size:3rem;"></i>
            <h4 class="mt-2 fw-bold">Bida Manager</h4>
            <p class="text-muted mb-0">Đăng nhập hệ thống</p>
        </div>
        @if($errors->any())
            <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Tên đăng nhập / SĐT</label>
                <input type="text" name="phone" class="form-control form-control-lg" placeholder="admin hoặc staff" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Mật khẩu</label>
                <input type="password" name="password" class="form-control form-control-lg" value="admin123" placeholder="••••••" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Ghi nhớ</label>
            </div>
            <button type="submit" class="btn btn-primary w-100 btn-lg">Đăng nhập</button>
        </form>
        <div class="mt-3 text-center text-muted small">
            Mặc định: admin/admin123 hoặc staff/staff123
        </div>
    </div>
</body>
</html>
