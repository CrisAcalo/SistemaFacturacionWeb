<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased">

    <div class="min-h-screen grid grid-cols-1 md:grid-cols-2 bg-gray-100 dark:bg-gray-900">
        <!-- Columna Izquierda (Visual) - Oculta en móviles -->
        <div class="hidden md:flex flex-col items-center justify-center p-12 bg-gradient-to-br from-blue-600 to-teal-500 text-white">
            <a href="/" wire:navigate>
                <x-application-logo class="w-24 h-24 text-white" />
            </a>
            <h1 class="mt-6 text-3xl font-bold text-center">Bienvenido a Tu Plataforma</h1>
            <p class="mt-2 text-center text-blue-100 max-w-sm">
                Accede para gestionar tus proyectos, ideas y mucho más.
            </p>
        </div>

        <!-- Columna Derecha (Formulario) -->
        <div class="flex flex-col items-center justify-center p-6 sm:p-12">
            <div class="w-full max-w-md">
                <!-- Logo para vista móvil -->
                <div class="md:hidden flex justify-center mb-6">
                    <a href="/" wire:navigate>
                        <x-application-logo class="w-20 h-20 fill-current text-gray-500 dark:text-gray-400" />
                    </a>
                </div>

                {{-- Aquí se insertará el contenido del formulario (login, register, etc.) --}}
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
