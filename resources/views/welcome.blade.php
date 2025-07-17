<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts y Estilos (requiere Tailwind CSS configurado con Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 antialiased font-sans">

    <div class="flex flex-col min-h-screen">
        <!-- ===== Barra de Navegación ===== -->
        <header class="bg-white dark:bg-gray-800/90 backdrop-blur-sm shadow-sm sticky top-0 z-50">
            <div class="container mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <a href="{{ url('/') }}" class="text-2xl font-bold text-gray-800 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            Mi App
                        </a>
                    </div>
                    <nav class="flex items-center">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Iniciar Sesión</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="ml-4 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 transition-transform transform hover:scale-105">
                                        Registrarse
                                    </a>
                                @endif
                            @endauth
                        @endif
                    </nav>
                </div>
            </div>
        </header>

        <!-- ===== Contenido Principal ===== -->
        <main class="flex-grow">
            <!-- ===== Sección Hero ===== -->
            <section class="container mx-auto px-6 py-24 text-center">
                <h1 class="text-4xl md:text-6xl font-extrabold leading-tight mb-4 bg-gradient-to-r from-blue-600 to-teal-400 text-transparent bg-clip-text">
                    Construye Algo Increíble
                </h1>
                <p class="text-lg md:text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto mb-8">
                    La plataforma perfecta para dar vida a tus ideas. Rápida, segura y escalable. Justo lo que necesitas para tu próximo gran proyecto.
                </p>
                <a href="{{ route('register') }}" class="inline-block bg-blue-600 text-white font-bold text-lg px-8 py-3 rounded-md hover:bg-blue-700 transition duration-300 ease-in-out transform hover:scale-105 shadow-lg hover:shadow-xl">
                    ¡Comienza Ahora Gratis!
                </a>
            </section>

            <!-- ===== Sección de Características ===== -->
            <section class="bg-white dark:bg-gray-800 py-20">
                <div class="container mx-auto px-6">
                    <div class="text-center mb-16">
                        <h2 class="text-3xl md:text-4xl font-bold">¿Por qué elegir nuestra plataforma?</h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-3 max-w-2xl mx-auto">Te ofrecemos las mejores herramientas para que te enfoques en lo que realmente importa: crear.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                        <!-- Característica 1 -->
                        <div class="text-center p-8 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm hover:shadow-lg hover:border-blue-500 dark:hover:border-blue-500 transition-all duration-300">
                            <div class="flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 dark:bg-blue-900/40 mx-auto mb-5">
                                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">Rendimiento Veloz</h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                Nuestra infraestructura está optimizada para ofrecerte la máxima velocidad y un rendimiento sin igual.
                            </p>
                        </div>

                        <!-- Característica 2 -->
                        <div class="text-center p-8 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm hover:shadow-lg hover:border-blue-500 dark:hover:border-blue-500 transition-all duration-300">
                            <div class="flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 dark:bg-blue-900/40 mx-auto mb-5">
                                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">Seguridad Robusta</h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                Protegemos tus datos con los más altos estándares de seguridad, para que puedas trabajar con tranquilidad.
                            </p>
                        </div>

                        <!-- Característica 3 -->
                        <div class="text-center p-8 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm hover:shadow-lg hover:border-blue-500 dark:hover:border-blue-500 transition-all duration-300">
                            <div class="flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 dark:bg-blue-900/40 mx-auto mb-5">
                                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">Soporte 24/7</h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                Nuestro equipo de soporte está disponible a cualquier hora para ayudarte a resolver cualquier duda o problema.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- ===== Pie de Página ===== -->
        <footer class="bg-white dark:bg-gray-800 py-8 border-t border-gray-200 dark:border-gray-700">
            <div class="container mx-auto px-6 text-center text-sm text-gray-500 dark:text-gray-400">
                <p>© {{ date('Y') }} Mi App. Todos los derechos reservados.</p>
                <p class="mt-2">
                    Desarrollado con <span class="text-red-500">♥</span> sobre Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
                </p>
            </div>
        </footer>
    </div>
</body>
</html>
