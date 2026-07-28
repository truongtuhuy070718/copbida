<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) return redirect()->route('login');
        $user = auth()->user();
        if (!$user->active) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['Tài khoản đã bị khóa.']);
        }
        if ($role === 'admin' && !$user->isAdmin()) {
            return redirect()->route('staff.pos')->withErrors(['Bạn không có quyền truy cập.']);
        }
        if ($role === 'staff' && !$user->isStaff() && !$user->isAdmin()) {
            return redirect()->route('login')->withErrors(['Bạn không có quyền truy cập.']);
        }
        return $next($request);
    }
}
