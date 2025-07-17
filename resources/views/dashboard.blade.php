<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Dashboard de Estadísticas
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- ===== Sección de Tarjetas de Estadísticas ===== -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">

                <!-- Tarjeta: Total de Usuarios -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total de Usuarios</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalUsers }}</p>
                    </div>
                    <div class="bg-blue-100 dark:bg-blue-900/50 p-3 rounded-full">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m-7.5-2.962A3.375 3.375 0 0112 15.75c-1.01 0-1.933-.39-2.625-1.035A3.375 3.375 0 0112 15.75zM12 12A3.375 3.375 0 0115.375 8.625c0-1.01-.39-1.933-1.035-2.625A3.375 3.375 0 0112 12zm-3.75 0a3.375 3.375 0 01-2.625-1.035 3.375 3.375 0 010-4.682A3.375 3.375 0 0112 6.75v3.375c0 .621.504 1.125 1.125 1.125h3.375c.621 0 1.125-.504 1.125-1.125V6.75a3.375 3.375 0 01-1.035-2.625 3.375 3.375 0 01-4.682 0A3.375 3.375 0 016 8.625v3.375zm8.036 2.472A3.375 3.375 0 0118 15.75c0 1.01-.39 1.933-1.035 2.625a3.375 3.375 0 01-4.682 0 3.375 3.375 0 01-2.625-1.035m11.642-1.975a9.094 9.094 0 01-3.742.479 3 3 0 01-4.682-2.72m-7.5-2.962A3.375 3.375 0 009 12.375c-1.01 0-1.933.39-2.625 1.035A3.375 3.375 0 009 12.375z" />
                        </svg>
                    </div>
                </div>

                <!-- Tarjeta: Usuarios Activos -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Usuarios Activos</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $activeUsers }}</p>
                    </div>
                    <div class="bg-green-100 dark:bg-green-900/50 p-3 rounded-full">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Tarjeta: Usuarios Verificados -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Verificado</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $verifiedUsers }}</p>
                    </div>
                    <div class="bg-indigo-100 dark:bg-indigo-900/50 p-3 rounded-full">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- ===== Sección de Tabla de Usuarios Recientes ===== -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Últimos Usuarios Registrados</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nombre</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha de Registro</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($recentUsers as $user)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $user->email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if ($user->status == 'active')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    Activo
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                    Inactivo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                            No hay usuarios registrados recientemente.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
