<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettlementController;

// Auth Routes (Login, Logout & Password Reset)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

// Public Read-Only Routes (Bisa diakses tanpa login)
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
Route::get('/bills/{bill}', [BillController::class, 'show'])->name('bills.show')->whereNumber('bill');

// Protected Routes (Wajib Login untuk Create, Update, Delete & Bayar)
Route::middleware('auth')->group(function () {
    Route::get('/bills/create', [BillController::class, 'create'])->name('bills.create');
    Route::post('/bills', [BillController::class, 'store'])->name('bills.store');
    Route::get('/bills/{bill}/edit', [BillController::class, 'edit'])->name('bills.edit')->whereNumber('bill');
    Route::match(['put', 'patch'], '/bills/{bill}', [BillController::class, 'update'])->name('bills.update')->whereNumber('bill');
    Route::delete('/bills/{bill}', [BillController::class, 'destroy'])->name('bills.destroy')->whereNumber('bill');

    // Settlement / Potong Hutang Timbal Balik
    Route::get('/settlements/{share}', [SettlementController::class, 'show'])->name('settlements.show')->whereNumber('share');
    Route::post('/settlements/{share}/offset', [SettlementController::class, 'offset'])->name('settlements.offset')->whereNumber('share');
    Route::post('/settlements/{share}/confirm', [SettlementController::class, 'confirm'])->name('settlements.confirm')->whereNumber('share');

    Route::post('/shares/{id}/pay', [PaymentController::class, 'payShare'])->name('shares.pay');
    Route::post('/shares/{id}/approve', [PaymentController::class, 'approveShare'])->name('shares.approve');
    Route::post('/payment-methods', [PaymentController::class, 'updatePaymentMethod'])->name('payment.methods.update');

    // Profile & Rekening Routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/payment-method', [ProfileController::class, 'updatePaymentMethod'])->name('profile.payment.update');
});
