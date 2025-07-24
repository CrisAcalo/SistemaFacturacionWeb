@extends('layouts.guest')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-purple-900">
    <div class="relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-blue-500 to-purple-600 transform rotate-12 scale-150"></div>
        </div>

        <!-- Navigation -->
        <nav class="relative z-10 px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <i class="bi bi-receipt text-white"></i>
                    </div>
                    <span class="text-xl font-bold text-gray-900 dark:text-white">Sistema de Facturación</span>
                </div>

                @if (Route::has('login'))
                    <div class="flex items-center space-x-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors duration-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                                <i class="bi bi-speedometer2 mr-1"></i>
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors duration-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                                <i class="bi bi-box-arrow-in-right mr-1"></i>
                                Iniciar Sesión
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg hover:from-blue-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                    <i class="bi bi-person-plus mr-1"></i>
                                    Registrarse
                                </a>
                            @endif
                        @endauth
                    </div>
                @endif
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative z-10 px-6 py-20">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-5xl md:text-6xl font-bold text-gray-900 dark:text-white mb-6">
                    Sistema de <span class="bg-gradient-to-r from-blue-500 to-purple-600 bg-clip-text text-transparent">Facturación</span> Seguro
                </h1>

                <p class="text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto">
                    Plataforma completa para la gestión de facturación con control de acceso avanzado y gestión de usuarios por estado.
                </p>

                <!-- Feature Cards -->
                <div class="grid md:grid-cols-3 gap-6 mt-12">
                    <!-- Security Feature -->
                    <div class="p-6 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-xl border border-gray-200 dark:border-gray-700 shadow-lg hover:shadow-xl transition-all duration-300">
                        <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg flex items-center justify-center mx-auto mb-4">
                            <i class="bi bi-shield-check text-white text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Control de Acceso</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm">
                            Sistema avanzado de middleware que controla el acceso basado en el estado del usuario: activo, inactivo o eliminado.
                        </p>
                    </div>

                    <!-- User Management -->
                    <div class="p-6 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-xl border border-gray-200 dark:border-gray-700 shadow-lg hover:shadow-xl transition-all duration-300">
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mx-auto mb-4">
                            <i class="bi bi-people text-white text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Gestión de Usuarios</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm">
                            Administra usuarios con diferentes estados y roles. Cambio instantáneo entre activo/inactivo con invalidación automática de sesiones.
                        </p>
                    </div>

                    <!-- Real-time Security -->
                    <div class="p-6 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-xl border border-gray-200 dark:border-gray-700 shadow-lg hover:shadow-xl transition-all duration-300">
                        <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-pink-600 rounded-lg flex items-center justify-center mx-auto mb-4">
                            <i class="bi bi-lightning text-white text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Seguridad en Tiempo Real</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm">
                            Desconexión automática de usuarios desactivados o eliminados. Mensajes claros y seguros para el usuario final.
                        </p>
                    </div>
                </div>

                <!-- Status Examples -->
                <div class="mt-16 p-8 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-xl border border-gray-200 dark:border-gray-700 shadow-lg">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Estados de Usuario</h3>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                            <div class="flex items-center justify-center w-8 h-8 bg-green-100 dark:bg-green-800 rounded-full mx-auto mb-2">
                                <i class="bi bi-check-circle-fill text-green-600 dark:text-green-300"></i>
                            </div>
                            <h4 class="font-semibold text-green-800 dark:text-green-300">Activo</h4>
                            <p class="text-xs text-green-600 dark:text-green-400 mt-1">Acceso completo al sistema</p>
                        </div>

                        <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                            <div class="flex items-center justify-center w-8 h-8 bg-red-100 dark:bg-red-800 rounded-full mx-auto mb-2">
                                <i class="bi bi-x-circle-fill text-red-600 dark:text-red-300"></i>
                            </div>
                            <h4 class="font-semibold text-red-800 dark:text-red-300">Inactivo</h4>
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">Acceso denegado temporalmente</p>
                        </div>

                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-center w-8 h-8 bg-gray-100 dark:bg-gray-600 rounded-full mx-auto mb-2">
                                <i class="bi bi-trash-fill text-gray-600 dark:text-gray-300"></i>
                            </div>
                            <h4 class="font-semibold text-gray-800 dark:text-gray-300">Eliminado</h4>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Usuario eliminado del sistema</p>
                        </div>
                    </div>
                </div>

                @if (!auth()->check())
                    <div class="mt-8">
                        <a href="{{ route('login') }}" class="inline-flex items-center px-8 py-3 text-lg font-medium text-white bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg hover:from-blue-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                            <i class="bi bi-box-arrow-in-right mr-2"></i>
                            Acceder al Sistema
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
