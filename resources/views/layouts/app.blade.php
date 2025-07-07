<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    {{-- El contenido del <head> no cambia --}}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('dark') === 'true' || (!('dark' in localStorage) && window.matchMedia(
                '(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
    @stack('styles')
</head>

<body class="h-full font-sans antialiased">

    <div x-data="{ sidebarOpen: false, dark: localStorage.getItem('dark') === 'true' }" x-init="$watch('dark', val => localStorage.setItem('dark', val))" x-bind:class="{ 'dark': dark }">

        <div class="relative min-h-screen bg-bg-muted dark:bg-bg-base">

            @include('layouts.partials.sidebar')

            <!-- CAMBIO CLAVE: El contenedor del contenido ahora tiene el padding a la izquierda -->
            <div class="lg:pl-64">
                <livewire:layout.header />

                <main class="py-10">{{-- Alerta de toast --}}
                    <div x-data="{ show: false, message: '', type: 'success' }"
                        x-on:show-toast.window="
        message = $event.detail.message;
        type = $event.detail.type;
        show = true;
        setTimeout(() => show = false, 3500);
    "
                        x-show="show" x-transition
                        class="fixed top-6 right-6 z-[9999] min-w-[220px] max-w-xs px-4 py-3 rounded-lg shadow-lg text-white"
                        :class="{
                            'bg-green-600': type === 'success',
                            'bg-red-600': type === 'error',
                            'bg-yellow-500': type === 'warning'
                        }"
                        style="display: none;">
                        <div class="flex items-center gap-2">
                            <template x-if="type === 'success'">
                                <i class="bi bi-check-circle-fill"></i>
                            </template>
                            <template x-if="type === 'error'">
                                <i class="bi bi-x-circle-fill"></i>
                            </template>
                            <template x-if="type === 'warning'">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </template>
                            <span x-text="message"></span>
                        </div>
                    </div>


                    <div class="px-4 sm:px-6 lg:px-8">
                        @if (isset($header))
                            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                                {{ $header }}
                            </h2>
                        @endif

                        <div class="py-6">
                            {{ $slot }}
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
