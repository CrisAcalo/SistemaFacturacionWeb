<?php

use App\Livewire\Audits\AuditLog;
use App\Livewire\Invoices\CreateInvoice;
use App\Livewire\Invoices\ListInvoices;
use App\Livewire\Products\ListProducts;
use App\Livewire\Tokens\ListTokens;
use App\Livewire\Users\UserTrash;
use Illuminate\Support\Facades\Route;
use App\Livewire\Users\ListUsers;
use App\Models\User;
use Illuminate\Support\Carbon; // <-- Asegúrate de tener esta importación

Route::view('/', 'welcome');

// ESTA ES LA ÚNICA RUTA QUE DEBE EXISTIR PARA EL DASHBOARD
Route::get('/dashboard', function () {
    // 1. Tarjetas de estadísticas
    $totalUsers = User::count();
    $activeUsers = User::where('status', 'active')->count();
    $inactiveUsers = User::where('status', 'inactive')->count();
    $verifiedUsers = User::whereNotNull('email_verified_at')->count();

    // 2. Nuevos usuarios en los últimos 30 días
    $newUsersLast30Days = User::where('created_at', '>=', Carbon::now()->subDays(30))->count();

    // 3. Tabla de usuarios recientes
    $recentUsers = User::latest()->take(5)->get();

    // 4. Pasamos todas las variables a la vista
    return view('dashboard', [
        'totalUsers' => $totalUsers,
        'activeUsers' => $activeUsers,
        'inactiveUsers' => $inactiveUsers,
        'verifiedUsers' => $verifiedUsers,
        'newUsersLast30Days' => $newUsersLast30Days,
        'recentUsers' => $recentUsers,
    ]);
})->middleware(['auth'])->name('dashboard');


Route::middleware(['auth'])->group(function () {
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
})->name('autenticado');

require __DIR__ . '/auth.php';
