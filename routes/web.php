<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [DashboardController::class, 'users'])->name('dashboard.users');
    Route::get('/conversations', [DashboardController::class, 'conversations'])->name('dashboard.conversations');
    Route::get('/subscriptions', [DashboardController::class, 'subscriptions'])->name('dashboard.subscriptions');
    Route::get('/settings', [DashboardController::class, 'settings'])->name('dashboard.settings');
});