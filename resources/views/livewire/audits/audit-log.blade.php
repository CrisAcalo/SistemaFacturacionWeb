<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Registro de Auditoría del Sistema
        </h2>
    </x-slot>

    <div class="p-4 mx-auto sm:p-6 lg:p-8">
        <div class="p-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">

            {{-- SECCIÓN DE FILTROS --}}
            <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Filtro por Usuario -->
                <div>
                    <label for="filter-user"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Usuario</label>
                    <select wire:model.live="filters.user_id" id="filter-user" class="w-full mt-1 input-style">
                        <option value="">Todos los Usuarios</option>
                        @foreach ($usersForFilter as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Filtro por Evento -->
                <div>
                    <label for="filter-event"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Evento</label>
                    <select wire:model.live="filters.event" id="filter-event" class="w-full mt-1 input-style">
                        <option value="">Todos los Eventos</option>
                        <option value="created">Creado</option>
                        <option value="updated">Actualizado</option>
                        <option value="deleted">Eliminado</option>
                        <option value="restored">Restaurado</option>
                        <option value="forceDeleted">Eliminado Definitivamente</option>
                        <option value="login">Inicio de Sesión</option>
                        <option value="logout">Cierre de Sesión</option>
                        <option value="exported">Exportado</option>
                        <option value="imported">Importado</option>
                    </select>
                </div>
                <!-- Filtro por Tipo de Objeto -->
                <div>
                    <label for="filter-subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo
                        de Objeto</label>
                    <select wire:model.live="filters.subject_type" id="filter-subject" class="w-full mt-1 input-style">
                        <option value="">Todos los Tipos</option>
                        <option value="App\Models\User">Usuario</option>
                        <option value="App\Models\Product">Producto</option>
                        <option value="App\Models\Invoice">Factura</option>
                    </select>
                </div>
                <!-- Botón para limpiar filtros -->
                <div class="flex items-end">
                    <button wire:click="resetFilters" class="w-full btn-secondary">Limpiar Filtros</button>
                </div>
            </div>

            {{-- TABLA DE LOGS DE AUDITORÍA --}}
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="table-thead-colored">
                        <tr>
                            <th scope="col" class="table-header">Usuario</th>
                            <th scope="col" class="table-header">Acción</th>
                            <th scope="col" class="table-header">Objeto</th>
                            <th scope="col" class="table-header">Fecha y Hora</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Detalles</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($logs as $log)
                            <tr wire:key="log-{{ $log->id }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ $log->causer->name ?? 'Sistema' }}</div>
                                    <div class="text-xs text-gray-500">{{ $log->causer->email ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 py-1 text-xs font-semibold leading-5 rounded-full
                                        @if ($log->event === 'created') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                        @elseif($log->event === 'updated') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @elseif($log->event === 'deleted') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                        @elseif($log->event === 'restored') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @elseif($log->event === 'forceDeleted') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                                        @elseif($log->event === 'login') bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200
                                        @elseif($log->event === 'logout') bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200
                                        @elseif($log->event === 'exported') bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200
                                        @elseif($log->event === 'imported') bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-200
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200 @endif">
                                        {{ $this->getEventDisplayName($log->event) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $log->description }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $log->created_at->format('d/m/Y H:i:s') }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                    <button wire:click="showDetails({{ $log->id }})"
                                        class="font-medium text-secondary hover:text-secondary/80">
                                        Ver Detalles
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No se encontraron registros de auditoría con los filtros aplicados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL PARA VER DETALLES DE AUDITORÍA --}}
    @if ($selectedLog)
        <div x-data="{ show: @entangle('showDetailsModal') }" x-show="show" x-on:keydown.escape.window="show = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
            style="display: none;">
            <div x-show="show" x-transition
                class="relative w-full max-w-2xl mx-auto overflow-hidden bg-white border rounded-lg shadow-xl dark:bg-gray-900 dark:border-gray-700">

                <div
                    class="flex items-center justify-between p-4 border-b dark:border-gray-600 bg-gray-50 dark:bg-gray-800">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Detalles del Evento de Auditoría
                    </h3>
                    <button @click="show = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-xl font-bold">
                        ×
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <strong>Motivo Proporcionado:</strong>
                        <em class="text-gray-700 dark:text-gray-300">
                            {{ $selectedLog->properties->get('reason', 'No se proporcionó motivo.') }}
                        </em>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        {{-- Datos Antiguos --}}
                        <div>
                            <h4 class="font-semibold text-red-600">Valores Anteriores</h4>
                            @if ($selectedLog->properties->has('old'))
                                <pre class="p-2 mt-2 text-xs text-white bg-gray-800 rounded-md overflow-x-auto">{{ json_encode($selectedLog->properties->get('old'), JSON_PRETTY_PRINT) }}</pre>
                            @else
                                <p class="text-sm text-gray-500">N/A (Evento de creación)</p>
                            @endif
                        </div>

                        {{-- Datos Nuevos --}}
                        <div>
                            <h4 class="font-semibold text-green-600">Valores Nuevos</h4>
                            @if ($selectedLog->properties->has('attributes'))
                                <pre class="p-2 mt-2 text-xs text-white bg-gray-800 rounded-md overflow-x-auto">{{ json_encode($selectedLog->properties->get('attributes'), JSON_PRETTY_PRINT) }}</pre>
                            @else
                                <p class="text-sm text-gray-500">N/A (Evento de eliminación)</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
