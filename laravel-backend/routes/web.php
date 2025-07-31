<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make sure to check out the
| RouteServiceProvider to see how this middleware group is configured.
|
*/

Route::get('/', function () {
    return response()->json([
        'message' => 'MedicoThink API Server',
        'version' => '1.0.0',
        'status' => 'running',
        'timestamp' => now()
    ]);
});