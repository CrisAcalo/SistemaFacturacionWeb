<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TokenController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned the "api" middleware group. Make something great!
|
*/

Route::get('/test', [TokenController::class, 'test']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/token-info', [TokenController::class, 'info']);
    Route::get('/invoices', [TokenController::class, 'getInvoices']);
});
