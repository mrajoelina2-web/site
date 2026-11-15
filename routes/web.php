<?php

use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SpinController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::get('/', [ProductController::class, 'index'])->name('products.index');
Route::get('/categorie/{category}', [ProductController::class, 'byCategory'])->name('products.category');
Route::post('/chat', [ChatController::class, 'reply'])
    ->middleware('throttle:30,1')
    ->name('chat.reply');

Route::middleware('auth')->group(function () {
    Route::get('/panier', [CartController::class, 'index'])->name('cart.index');
    Route::post('/panier/ajouter/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::delete('/panier/retirer', [CartController::class, 'remove'])->name('cart.remove');    Route::post('/panier/coupon', [CartController::class, 'applyCoupon'])->name('cart.applyCoupon');
    Route::delete('/panier/coupon', [CartController::class, 'removeCoupon'])->name('cart.removeCoupon');
    Route::post('/commande/valider', [CheckoutController::class, 'validate'])->name('checkout.validate');
    Route::get('/spin', [SpinController::class, 'index'])->name('spin.spin');
    Route::post('/spin/jouer', [SpinController::class, 'play'])->name('spin.play');
});

// Espace administrateur : réservé au compte avec is_admin = true
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/achats', [AdminOrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/admin/bilan', [AdminReportController::class, 'index'])->name('admin.report.index');
    });

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';