<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SegmentationController;
use Illuminate\Support\Facades\Route;

// --- Guest routes ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

// --- Authenticated routes ---
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.alias');
    Route::get('/about', [DashboardController::class, 'about'])->name('about');

    Route::prefix('segmentation')->name('segmentation.')->group(function () {
        Route::get('/', [SegmentationController::class, 'index'])->name('index');
        Route::get('/upload', [SegmentationController::class, 'create'])->name('create');
        Route::post('/upload', [SegmentationController::class, 'store'])->name('store');
        Route::get('/{run}', [SegmentationController::class, 'show'])->name('show');
        Route::delete('/{run}', [SegmentationController::class, 'destroy'])->name('destroy');
        Route::get('/{run}/image/{file}', [SegmentationController::class, 'image'])->name('image');
    });
});
