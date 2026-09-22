<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('home');

// ---------- USER SIDE (public) ----------
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// ---------- ADMIN BACKOFFICE SIDE (URL-only, not linked from public UI) ----------
Route::get('/backoffice/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
Route::post('/backoffice/login', [AuthController::class, 'adminLogin']);

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ---------- INFO PAGES ----------
Route::get('/syarat-ketentuan', fn () => view('legal.terms'))->name('terms');
Route::get('/kebijakan-privasi', fn () => view('legal.privacy'))->name('privacy');
Route::get('/hubungi-kami', [ContactController::class, 'show'])->name('contact');
Route::post('/hubungi-kami', [ContactController::class, 'send'])->name('contact.send');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

// ---------- SELLER (penjual + admin, CRUD produk) ----------
Route::middleware(['auth', 'seller'])->prefix('seller')->name('seller.')->group(function () {
    Route::middleware('penjual')->group(function () {
        Route::get('/store', [StoreController::class, 'edit'])->name('store.edit');
        Route::put('/store', [StoreController::class, 'update'])->name('store.update');
    });
    Route::patch('/store/toggle', [StoreController::class, 'toggle'])->name('store.toggle');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
});

// ---------- ADMIN BACKOFFICE (auth + admin only) ----------
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/warungs', [AdminController::class, 'warungs'])->name('warungs');
    Route::get('/products', [AdminController::class, 'products'])->name('products');
    Route::patch('/products/{product}/approve', [AdminController::class, 'approve'])->name('products.approve');
    Route::patch('/products/{product}/reject', [AdminController::class, 'reject'])->name('products.reject');
});
