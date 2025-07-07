<?php

use App\Livewire\Audits\AuditLog;
use App\Livewire\Invoices\CreateInvoice;
use App\Livewire\Invoices\ListInvoices;
use App\Livewire\Products\ListProducts;
use Illuminate\Support\Facades\Route;
use App\Livewire\Users\ListUsers;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');

    // --- NUEVA RUTA PARA CLIENTES/USUARIOS ---
    Route::get('users', ListUsers::class)
        ->name('clients.index')
        ->middleware(['can:manage users']);

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
})->name('autenticado');

require __DIR__ . '/auth.php';
