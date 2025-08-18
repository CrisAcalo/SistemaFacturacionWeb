<?php

use App\Livewire\Audits\AuditLog;
use App\Livewire\Invoices\CreateInvoice;
use App\Livewire\Invoices\ListInvoices;
use App\Livewire\Products\ListProducts;
use App\Livewire\Tokens\ListTokens;
use App\Livewire\Users\UserTrash;
use App\Livewire\Payments\ListPayments;
use Illuminate\Support\Facades\Route;
use App\Livewire\Users\ListUsers;
use App\Models\User;
use Illuminate\Support\Carbon; // <-- Asegúrate de tener esta importación

Route::view('/', 'welcome');

Route::get('/dashboard', function () {
    $totalUsers = User::count();
    $activeUsers = User::where('status', 'active')->count();
    $inactiveUsers = User::where('status', 'inactive')->count();
    $verifiedUsers = User::whereNotNull('email_verified_at')->count();

    $newUsersLast30Days = User::where('created_at', '>=', Carbon::now()->subDays(30))->count();

    $recentUsers = User::latest()->take(5)->get();

    return view('dashboard', [
        'totalUsers' => $totalUsers,
        'activeUsers' => $activeUsers,
        'inactiveUsers' => $inactiveUsers,
        'verifiedUsers' => $verifiedUsers,
        'newUsersLast30Days' => $newUsersLast30Days,
        'recentUsers' => $recentUsers,
    ]);
})->middleware(['auth', 'check.user.status'])->name('dashboard');


Route::middleware(['auth', 'check.user.status'])->group(function () {
    // Route::view('dashboard', 'dashboard')->name('dashboard'); // <-- ELIMINA ESTA LÍNEA

    Route::view('profile', 'profile')->name('profile');

    // --- NUEVA RUTA PARA CLIENTES/USUARIOS ---
    Route::get('users', ListUsers::class)
        ->name('clients.index')
        ->middleware(['can:manage users']);
    Route::get('users/trash', UserTrash::class)->name('clients.trash')->middleware(['can:manage users']);

    Route::get('products', ListProducts::class)
        ->name('products.index')
        ->middleware(['can:manage products']);
    Route::get('invoices', ListInvoices::class)
        ->name('invoices.index')
        ->middleware(['can:manage invoices']);

    Route::get('invoices/create', CreateInvoice::class)
        ->name('invoices.create')
        ->middleware(['can:manage invoices']);

    Route::get('audits', AuditLog::class)
        ->name('audits.index')
        ->middleware(['can:view audits']);

    Route::get('api-tokens', ListTokens::class)
        ->name('tokens.index')
        ->middleware(['can:manage tokens']);

    // --- RUTAS PARA GESTIÓN DE PAGOS ---
    Route::get('payments', ListPayments::class)
        ->name('payments.index')
        ->middleware(['can:manage payments']);
})->name('autenticado');

require __DIR__ . '/auth.php';
