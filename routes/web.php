<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TableController as AdminTableController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\BillController;
use App\Http\Controllers\Staff\PosController;
use App\Http\Controllers\Staff\TableController as StaffTableController;

// Auth
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/', function () { return redirect()->route('login'); });

// Admin
Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/tables', [AdminTableController::class, 'index'])->name('tables.index');
    Route::post('/tables', [AdminTableController::class, 'store'])->name('tables.store');
    Route::put('/tables/{table}', [AdminTableController::class, 'update'])->name('tables.update');
    Route::delete('/tables/{table}', [AdminTableController::class, 'destroy'])->name('tables.destroy');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');

    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::put('/staff/{user}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{user}', [StaffController::class, 'destroy'])->name('staff.destroy');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
    Route::delete('/bills/orders/{order}', [BillController::class, 'destroyOrder'])->name('bills.orders.destroy');
});

// Staff
Route::middleware(['role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('pos');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');

    Route::get('/tables', [StaffTableController::class, 'index'])->name('tables');
    Route::post('/tables/{table}/start', [StaffTableController::class, 'start'])->name('tables.start');
    Route::post('/tables/{table}/order', [StaffTableController::class, 'order'])->name('tables.order');
    Route::post('/tables/{table}/close', [StaffTableController::class, 'close'])->name('tables.close');
});
