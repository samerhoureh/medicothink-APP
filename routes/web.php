<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{id}', [AdminController::class, 'userDetails'])->name('users.show');
    Route::get('/conversations', [AdminController::class, 'conversations'])->name('conversations');
    Route::get('/conversations/{id}', [AdminController::class, 'conversationDetails'])->name('conversations.show');
    Route::get('/subscriptions', [AdminController::class, 'subscriptions'])->name('subscriptions');
    Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
    Route::get('/app-versions', [AdminController::class, 'appVersions'])->name('app-versions');
    Route::post('/app-versions', [AdminController::class, 'createAppVersion'])->name('app-versions.store');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
});