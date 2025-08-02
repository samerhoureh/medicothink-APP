<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Admin Dashboard Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{id}', [AdminController::class, 'userShow'])->name('users.show');
    Route::get('/conversations', [AdminController::class, 'conversations'])->name('conversations');
    Route::get('/conversations/{id}', [AdminController::class, 'conversationShow'])->name('conversations.show');
    Route::get('/subscriptions', [AdminController::class, 'subscriptions'])->name('subscriptions');
    Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
    Route::get('/app-versions', [AdminController::class, 'appVersions'])->name('app-versions');
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
});