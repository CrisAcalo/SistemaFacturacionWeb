<div>
    <!-- Encabezado con Estadísticas -->
    <div class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 dark:bg-gray-800">
                <div class="text-2xl font-bold text-orange-600">{{ $stats['pendiente'] }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Pendientes</div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 dark:bg-gray-800">
                <div class="text-2xl font-bold text-green-600">{{ $stats['validado'] }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Validados</div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 dark:bg-gray-800">
                <div class="text-2xl font-bold text-red-600">{{ $stats['rechazado'] }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Rechazados</div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 dark:bg-gray-800">
                <div class="text-2xl font-bold text-blue-600">${{ number_format($stats['total_pendiente_amount'], 2) }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Monto Pendiente</div>
            </div>
        </div>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 dark:bg-gray-800">
        <div class="p-6">
            <div class="flex flex-wrap gap-4 items-end">
                <!-- Búsqueda -->
                <div class="flex-1 min-w-64">
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Buscar pagos
                    </label>
                    <input
                        type="text"
                        id="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Factura, cliente o número de transacción..."
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    />
                </div>

                <!-- Estado -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Estado
                    </label>
                    <select
                        id="status"
                        wire:model.live="status"
                        class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    >
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendientes</option>
                        <option value="validado">Validados</option>
                        <option value="rechazado">Rechazados</option>
                    </select>
                </div>

                <!-- Items por página -->
                <div>
                    <label for="perPage" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Por página
                    </label>
                    <select
                        id="perPage"
                        wire:model.live="perPage"
                        class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    >
                        @foreach($perPageOptions as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Limpiar filtros -->
                <div>
                    <button
                        type="button"
                        wire:click="$set('search', '')"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors"
                    >
                        Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de pagos -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800">
        <div class="p-6">
            @if($payments->count() > 0)
                <!-- Tabla responsiva -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                    Factura
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                    Cliente
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                    Tipo de Pago
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                    Monto
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                    Fecha
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @foreach($payments as $payment)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $payment->invoice->invoice_number }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            Total: ${{ number_format($payment->invoice->total, 2) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            @if($payment->client)
                                                {{ $payment->client->name }}
                                            @else
                                                <span class="italic text-gray-400">Cliente eliminado</span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            @if($payment->client)
                                                {{ $payment->client->email }}
                                            @else
                                                <span class="italic text-gray-400">-</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @switch($payment->payment_type)
                                                @case('efectivo') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @break
                                                @case('tarjeta') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @break
                                                @case('transferencia') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 @break
                                                @case('cheque') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @break
                                            @endswitch
                                        ">
                                            {{ ucfirst($payment->payment_type) }}
                                        </span>
                                        @if($payment->transaction_number)
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-mono">
                                                {{ $payment->transaction_number }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            ${{ number_format($payment->amount, 2) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @switch($payment->status)
                                                @case('pendiente') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 @break
                                                @case('validado') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @break
                                                @case('rechazado') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @break
                                            @endswitch
                                        ">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                        @if($payment->validated_at)
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                {{ $payment->validated_at->format('d/m/Y H:i') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $payment->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        @if($payment->isPending())
                                            <button
                                                wire:click="requestApproval({{ $payment->id }})"
                                                class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 transition-colors"
                                            >
                                                Aprobar
                                            </button>
                                            <button
                                                wire:click="requestRejection({{ $payment->id }})"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                                            >
                                                Rechazar
                                            </button>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                            @if($payment->validator)
                                                <div class="text-xs text-gray-400 dark:text-gray-500">
                                                    por {{ $payment->validator->name }}
                                                </div>
                                            @elseif($payment->validator_id)
                                                <div class="text-xs text-gray-400 dark:text-gray-500">
                                                    por <span class="italic">Usuario eliminado</span>
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="mt-6">
                    {{ $payments->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="bi bi-credit-card text-6xl text-gray-400 dark:text-gray-600 mb-4"></i>
                    <div class="text-gray-500 dark:text-gray-400 text-lg mb-2">No se encontraron pagos</div>
                    <div class="text-gray-400 dark:text-gray-500 text-sm">
                        @if($search || $status)
                            Intenta ajustar los filtros de búsqueda
                        @else
                            Los pagos registrados por clientes aparecerán aquí
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Incluir modales -->
    @include('livewire.payments.modals')
</div>
