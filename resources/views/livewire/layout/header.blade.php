<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<!-- El HTML del header que ya habíamos diseñado va aquí debajo -->
<header class="z-10 py-4 bg-white shadow-md dark:bg-gray-800">
    <div class="container flex items-center justify-between h-full px-6 mx-auto text-primary dark:text-primary"> <!-- Usando nuestros colores! -->
        <!-- Botón para abrir sidebar en móvil -->
        <button
            class="p-1 mr-5 -ml-1 rounded-md lg:hidden focus:outline-none focus:ring-2 focus:ring-primary"
            @click="sidebarOpen = !sidebarOpen"
            aria-label="Menu"
        >
            <i class="bi bi-list w-10 h-10"></i>
        </button>

        <!-- Espacio en blanco para empujar el menú de usuario a la derecha -->
        <div class="flex-1"></div>

        <ul class="flex items-center flex-shrink-0 space-x-6">
            <!-- Dark Mode Toggle -->
            <li>
                <button
                    class="rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    @click="dark = !dark"
                    aria-label="Toggle color mode"
                >
                    <template x-if="!dark">
                        <i class="bi bi-moon-fill w-5 h-5"></i>
                    </template>
                    <template x-if="dark">
                        <i class="bi bi-sun-fill w-5 h-5"></i>
                    </template>
                </button>
            </li>

            <!-- Menú de Usuario -->
            <li class="relative">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-bg-base dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <!-- Tomamos prestada la forma reactiva de mostrar el nombre de Breeze -->
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <!-- ¡LA FORMA CORRECTA! -->
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </li>
        </ul>
    </div>
</header>
