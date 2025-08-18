<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\InvoiceController;

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

// --- RUTAS DE AUTENTICACIÓN (PÚBLICAS) ---
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// --- RUTAS PROTEGIDAS (REQUIEREN AUTENTICACIÓN) ---
Route::middleware('auth:sanctum')->group(function () {

    // --- RUTAS DE AUTENTICACIÓN PROTEGIDAS ---
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // --- RUTAS DE GESTIÓN DE USUARIOS ---
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::put('/{user}', [UserController::class, 'update']);
        Route::delete('/{user}', [UserController::class, 'destroy']);
        Route::post('/{userId}/restore', [UserController::class, 'restore']);
        Route::put('/{user}/status', [UserController::class, 'updateStatus']);

        // Gestión de roles
        Route::get('/{user}/roles', [UserController::class, 'getRoles']);
        Route::put('/{user}/roles', [UserController::class, 'assignRoles']);
    });

    // --- RUTAS AUXILIARES PARA ROLES Y PERMISOS ---
    Route::get('/roles', [UserController::class, 'getRolesAvailable']);
    Route::get('/permissions', [UserController::class, 'getPermissionsAvailable']);

    // --- RUTAS DE GESTIÓN DE PRODUCTOS ---
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::post('/', [ProductController::class, 'store']);
        Route::get('/low-stock', [ProductController::class, 'lowStock']);
        Route::post('/bulk-update', [ProductController::class, 'bulkUpdate']);
        Route::get('/{product}', [ProductController::class, 'show']);
        Route::put('/{product}', [ProductController::class, 'update']);
        Route::delete('/{product}', [ProductController::class, 'destroy']);
        Route::post('/{productId}/restore', [ProductController::class, 'restore']);
        Route::put('/{product}/stock', [ProductController::class, 'updateStock']);
    });

    // --- RUTAS DE GESTIÓN DE FACTURAS ---
    Route::prefix('invoices')->group(function () {
        Route::get('/statistics', [InvoiceController::class, 'statistics']); // Mover antes del parámetro dinámico
        Route::get('/', [InvoiceController::class, 'index']);
        Route::post('/', [InvoiceController::class, 'store']);
        Route::get('/{invoice}', [InvoiceController::class, 'show']);
        Route::put('/{invoice}', [InvoiceController::class, 'update']);
        Route::delete('/{invoice}', [InvoiceController::class, 'destroy']);
        Route::post('/{invoiceId}/restore', [InvoiceController::class, 'restore']);
        Route::put('/{invoice}/status', [InvoiceController::class, 'updateStatus']);
    });

    // --- RUTAS DE TOKENS API ---
    Route::get('/token-info', [TokenController::class, 'info']);
    Route::get('/user-invoices', [TokenController::class, 'getInvoices']); // Cambiar nombre para evitar conflicto

    // Gestión completa de tokens
    // Route::prefix('tokens')->group(function () {
    //     Route::get('/', [TokenController::class, 'index']);
    //     Route::post('/', [TokenController::class, 'store']);
    //     Route::get('/{token}', [TokenController::class, 'show']);
    //     Route::delete('/{token}', [TokenController::class, 'destroy']);
    //     Route::patch('/{token}/status', [TokenController::class, 'updateStatus']);
    //     Route::get('/{token}/audit', [TokenController::class, 'auditTrail']);
    // });

    // --- RUTAS DE PAGOS ---
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index']); // Obtener todos los pagos del cliente
        Route::post('/', [PaymentController::class, 'store']); // Registrar un nuevo pago
        Route::get('/invoice/{invoiceId}', [PaymentController::class, 'show']); // Obtener pagos de una factura específica
        Route::patch('/{payment}/validate', [PaymentController::class, 'validatePayment']); // Aprobar o rechazar pago (solo admin)
    });
});
