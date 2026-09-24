<?php

use App\Http\Controllers\AdminArticleController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminFinanceController;
use App\Http\Controllers\AdminInquiryController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminPaymentController;
use App\Http\Controllers\AdminPaymentMethodController;
use App\Http\Controllers\AdminSettingController;
use App\Http\Controllers\AdminWithdrawalController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/warung', [HomeController::class, 'nearby'])->name('store.index');
Route::get('/w/{store}', [StoreController::class, 'show'])->name('store.show');
Route::get('/kategori/{category}', [CategoryController::class, 'show'])->name('category.show');

// Editorial content: blog index + detail, plus the company page.
Route::get('/artikel', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/artikel/{article}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/tentang', fn () => view('about', [
    'seoTitle' => 'Tentang Warung Hebat',
    'seoDescription' => 'Warung Hebat menghubungkan pembeli dengan warung tetangga: jarak dekat, harga warung asli, dan 92% uang pesanan sampai ke pemilik warung.',
]))->name('about');

// ---------- BUYER (cart, checkout, orders) ----------
Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/items/{product}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/items/{product}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/proof', [OrderController::class, 'uploadProof'])->name('orders.proof');
    Route::post('/orders/{order}/rate', [OrderController::class, 'rate'])->name('orders.rate');
});

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

// PWA: installable manifest and offline fallback. sw.js is served straight
// from public/ by the web server (Laravel's front controller never runs for
// existing files, which also keeps the worker's scope at "/").
Route::get('/manifest.webmanifest', [PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/offline', [PwaController::class, 'offline'])->name('pwa.offline');

// Crawler files. robots.txt is served here (not from public/) so the sitemap
// URL always matches the domain the app runs on.
Route::get('/robots.txt', fn () => response(
    "User-agent: *\nAllow: /\n\nDisallow: /backoffice\nDisallow: /admin\nDisallow: /cart\nDisallow: /orders\n\nSitemap: ".url('/sitemap.xml')."\n"
)->header('Content-Type', 'text/plain; charset=UTF-8'));
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

// ---------- SELLER (penjual + admin, CRUD produk) ----------
Route::middleware(['auth', 'seller'])->prefix('seller')->name('seller.')->group(function () {
    Route::middleware('penjual')->group(function () {
        Route::get('/store', [StoreController::class, 'edit'])->name('store.edit');
        Route::put('/store', [StoreController::class, 'update'])->name('store.update');
        Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
        Route::post('/wallet/withdrawals', [WalletController::class, 'store'])->name('wallet.withdraw');
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
    Route::patch('/warungs/{store}/suspend', [AdminController::class, 'suspendStore'])->name('warungs.suspend');
    Route::patch('/warungs/{store}/activate', [AdminController::class, 'activateStore'])->name('warungs.activate');
    Route::get('/products', [AdminController::class, 'products'])->name('products');
    Route::patch('/products/bulk-update', [AdminController::class, 'bulkUpdate'])->name('products.bulk-update');
    Route::patch('/products/{product}/approve', [AdminController::class, 'approve'])->name('products.approve');
    Route::patch('/products/{product}/reject', [AdminController::class, 'reject'])->name('products.reject');

    // Editorial blog: buyers read it, the backoffice writes it.
    Route::get('/articles', [AdminArticleController::class, 'index'])->name('articles');
    Route::get('/articles/create', [AdminArticleController::class, 'create'])->name('articles.create');
    Route::post('/articles', [AdminArticleController::class, 'store'])->name('articles.store');
    Route::get('/articles/{article}/edit', [AdminArticleController::class, 'edit'])->name('articles.edit');
    Route::put('/articles/{article}', [AdminArticleController::class, 'update'])->name('articles.update');
    Route::patch('/articles/{article}/toggle', [AdminArticleController::class, 'toggle'])->name('articles.toggle');
    Route::delete('/articles/{article}', [AdminArticleController::class, 'destroy'])->name('articles.destroy');

    // Platform money overview: escrow, commission, payouts.
    Route::get('/finance', [AdminFinanceController::class, 'index'])->name('finance');

    // Payment methods buyers can transfer into (bank / QRIS / e-wallet).
    Route::get('/payment-methods', [AdminPaymentMethodController::class, 'index'])->name('payment-methods');
    Route::get('/payment-methods/create', [AdminPaymentMethodController::class, 'create'])->name('payment-methods.create');
    Route::post('/payment-methods', [AdminPaymentMethodController::class, 'store'])->name('payment-methods.store');
    Route::get('/payment-methods/{paymentMethod}/edit', [AdminPaymentMethodController::class, 'edit'])->name('payment-methods.edit');
    Route::put('/payment-methods/{paymentMethod}', [AdminPaymentMethodController::class, 'update'])->name('payment-methods.update');
    Route::patch('/payment-methods/{paymentMethod}/toggle', [AdminPaymentMethodController::class, 'toggle'])->name('payment-methods.toggle');
    Route::delete('/payment-methods/{paymentMethod}', [AdminPaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');

    // Buyer transfer proofs waiting for verification.
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments');
    Route::patch('/payments/{payment}/verify', [AdminPaymentController::class, 'verify'])->name('payments.verify');
    Route::patch('/payments/{payment}/reject', [AdminPaymentController::class, 'reject'])->name('payments.reject');

    // Orders: release escrow to the store wallet, or cancel and return stock.
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders');
    Route::patch('/orders/{order}/complete', [AdminOrderController::class, 'complete'])->name('orders.complete');
    Route::patch('/orders/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');

    // Store withdrawal requests.
    Route::get('/withdrawals', [AdminWithdrawalController::class, 'index'])->name('withdrawals');
    Route::patch('/withdrawals/{withdrawal}/paid', [AdminWithdrawalController::class, 'markPaid'])->name('withdrawals.paid');
    Route::patch('/withdrawals/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject'])->name('withdrawals.reject');

    // Inquiries from the "Hubungi Kami" form, grouped by topic.
    Route::get('/inquiries', [AdminInquiryController::class, 'index'])->name('inquiries');

    // Platform settings: withdrawal limits and commission.
    Route::get('/settings', [AdminSettingController::class, 'edit'])->name('settings');
    Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
});
