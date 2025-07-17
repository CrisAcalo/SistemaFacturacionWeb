<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="w-full p-8 space-y-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg">
    @if (session('error'))
        <div class="mb-4 p-4 rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/50 dark:border-red-800">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="bi bi-exclamation-triangle-fill text-red-600 dark:text-red-400 text-lg"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                        Acceso Denegado
                    </h3>
                    <div class="mt-1 text-sm text-red-700 dark:text-red-300">
                        {{ session('error') }}
                    </div>
                    <div class="mt-2 text-xs text-red-600 dark:text-red-400">
                        Si crees que esto es un error, contacta al administrador del sistema.
                    </div>
                </div>
            </div>
        </div>
    @endif
    <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-white">
        Inicia Sesión en tu Cuenta
    </h2>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-6">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Correo Electrónico" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="email" name="email"
                required autofocus autocomplete="username" placeholder="tu@email.com" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Contraseña" />
            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full" type="password"
                name="password" required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox"
                    class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-blue-600 shadow-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:focus:ring-offset-gray-800"
                    name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Recuérdame</span>
            </label>

            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800"
                    href="{{ route('password.request') }}" wire:navigate>
                    ¿Olvidaste tu contraseña?
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <div>
            <x-primary-button class="w-full justify-center">
                Iniciar Sesión
            </x-primary-button>
        </div>
    </form>

    <p class="text-sm text-center text-gray-600 dark:text-gray-400">
        ¿No tienes una cuenta?
        <a href="{{ route('register') }}" wire:navigate
            class="font-medium text-blue-600 hover:underline dark:text-blue-500">
            Regístrate aquí
        </a>
    </p>
</div>
