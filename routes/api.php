<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\PaymentController;

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
    // Rutas de información de token y facturas
    Route::get('/token-info', [TokenController::class, 'info']);
    Route::get('/invoices', [TokenController::class, 'getInvoices']);

    // Rutas de pagos
    Route::get('/payments', [PaymentController::class, 'index']); // Obtener todos los pagos del cliente
    Route::post('/payments', [PaymentController::class, 'store']); // Registrar un nuevo pago
    Route::get('/payments/invoice/{invoiceId}', [PaymentController::class, 'show']); // Obtener pagos de una factura específica
});
