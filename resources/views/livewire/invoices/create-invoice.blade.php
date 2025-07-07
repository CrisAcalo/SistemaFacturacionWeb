<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Crear Nueva Factura
        </h2>
    </x-slot>

    <div class="p-4 mx-auto sm:p-6 lg:p-8">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">

            <!-- Columna Izquierda: Detalles de la Factura -->
            <div class="p-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg lg:col-span-2">

                {{-- SECCIÓN 1: DATOS DEL CLIENTE --}}
                <div class="pb-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Datos del Cliente</h3>
                    <div class="relative mt-4">
                        <input wire:model.live.debounce.300ms="clientSearch" type="text"
                            placeholder="Buscar cliente por nombre o email..."
                            class="w-full pl-10 input-style bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="text-gray-400 bi bi-search"></i>
                        </span>

                        {{-- Resultados de la búsqueda de clientes --}}
                        @if (!empty($clientSearchResults) && !$selectedClient)
                            <ul
                                class="absolute z-10 w-full mt-1 overflow-y-auto bg-white border border-gray-300 rounded-md shadow-lg dark:bg-gray-700 max-h-60">
                                @foreach ($clientSearchResults as $client)
                                    <li wire:click="selectClient({{ $client->id }})"
                                        class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                        <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $client->name }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $client->email }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    @if (!$selectedClient)
                        <div class="mt-3 text-sm text-blue-600 dark:text-blue-300 flex items-center gap-2">
                            <i class="bi bi-info-circle"></i>
                            Selecciona un cliente para continuar con la factura.
                        </div>
                    @endif

                    {{-- Cliente seleccionado --}}
                    @if ($selectedClient)
                        <div class="p-4 mt-4 border border-green-500 rounded-lg bg-green-50 dark:bg-green-900/50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-green-800 dark:text-green-200">
                                        {{ $selectedClient->name }}</p>
                                    <p class="text-sm text-green-700 dark:text-green-300">{{ $selectedClient->email }}
                                    </p>
                                </div>
                                <button wire:click="deselectClient" class="text-red-500 hover:text-red-700">×
                                    Desvincular</button>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- SECCIÓN 2: AÑADIR PRODUCTOS --}}
                <div class="py-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Añadir Productos</h3>
                    <div class="relative mt-4">
                        <input wire:model.live.debounce.300ms="productSearch" type="text"
                            placeholder="Buscar producto por nombre o SKU..."
                            class="w-full pl-10 input-style bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
                            @if (!$selectedClient) disabled @endif>
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i
                                class="text-gray-400 bi bi-search"></i></span>

                        @if (!$selectedClient)
                            <div
                                class="absolute left-0 right-0 mt-2 text-xs text-center text-blue-600 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/40 rounded p-2">
                                Selecciona un cliente para habilitar la búsqueda de productos.
                            </div>
                        @endif

                        {{-- Resultados de la búsqueda de productos --}}
                        @if (!empty($productSearchResults) && $selectedClient)
                            <ul
                                class="absolute z-10 w-full mt-1 overflow-y-auto bg-white border border-gray-300 rounded-md shadow-lg dark:bg-gray-700 max-h-60">
                                @foreach ($productSearchResults as $product)
                                    <li wire:click="addProduct({{ $product->id }})"
                                        class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                                        <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $product->name }}
                                            (SKU: {{ $product->sku }})</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Stock: {{ $product->stock }}
                                            | Precio: ${{ number_format($product->price, 2) }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                {{-- SECCIÓN 3: DETALLE DE LA FACTURA (ITEMS) --}}
                <div class="mt-4">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Detalle de la Factura
                    </h3>
                    <div class="mt-4 -mx-6 overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th
                                        class="py-2 pl-6 pr-3 text-sm font-semibold text-left text-gray-900 dark:text-gray-200">
                                        Producto</th>
                                    <th
                                        class="px-3 py-2 text-sm font-semibold text-center text-gray-900 dark:text-gray-200">
                                        Cantidad</th>
                                    <th
                                        class="px-3 py-2 text-sm font-semibold text-center text-gray-900 dark:text-gray-200">
                                        Disponible</th>
                                    <th
                                        class="px-3 py-2 text-sm font-semibold text-right text-gray-900 dark:text-gray-200">
                                        Precio Unit.</th>
                                    <th
                                        class="py-2 pl-3 pr-6 text-sm font-semibold text-right text-gray-900 dark:text-gray-200">
                                        Total</th>
                                    <th class="relative py-2 pl-3 pr-4"><span class="sr-only">Eliminar</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoiceItems as $index => $item)
                                    @php
                                        $product = \App\Models\Product::find($item['product_id']);
                                        $stock = $product ? $product->stock : 0;
                                    @endphp
                                    <tr class="border-b border-gray-200 dark:border-gray-700"
                                        wire:key="item-{{ $index }}">
                                        <td class="py-4 pl-6 pr-3 text-sm">
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ $item['name'] }}</div>
                                            <div class="text-gray-500 dark:text-gray-400">SKU: {{ $item['sku'] }}
                                            </div>
                                        </td>
                                        <td class="px-3 py-4 text-sm text-center">
                                            <input type="number"
                                                wire:model.live="invoiceItems.{{ $index }}.quantity"
                                                min="1" max="{{ $stock }}"
                                                class="w-20 text-center input-style bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                                                @if ($stock < 1) disabled @endif>
                                            @if ($stock < 1)
                                                <div class="mt-1 text-xs text-red-500">Sin stock</div>
                                            @elseif($item['quantity'] > $stock)
                                                <div class="mt-1 text-xs text-red-500">Máx: {{ $stock }}</div>
                                            @elseif($item['quantity'] < 1)
                                                <div class="mt-1 text-xs text-red-500">Cantidad mínima: 1</div>
                                            @endif
                                        </td>
                                        <td class="px-3 py-4 text-sm text-center text-gray-500 dark:text-gray-400">
                                            {{ $stock }}
                                        </td>
                                        <td class="px-3 py-4 text-sm text-right text-gray-500 dark:text-gray-400">
                                            ${{ number_format((float) $item['price'], 2) }}
                                        </td>
                                        <td class="py-4 pl-3 pr-6 text-sm text-right text-gray-900 dark:text-gray-100">
                                            ${{ number_format((float) $item['quantity'] * (float) $item['price'], 2) }}
                                        </td>
                                        <td class="py-4 pl-3 pr-4 text-sm text-right">
                                            <button wire:click="removeItem({{ $index }})"
                                                class="text-red-500 hover:text-red-700">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6"
                                            class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                            Aún no has añadido productos a la factura.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Resumen y Acciones -->
            <div class="lg:col-span-1">
                <div class="sticky top-8">
                    <div class="p-6 bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Resumen de la Factura
                        </h3>

                        {{-- SECCIÓN 4: TOTALES --}}
                        <dl class="mt-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <dt class="text-sm text-gray-600 dark:text-gray-400">Subtotal</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    ${{ number_format($subtotal, 2) }}</dd>
                            </div>
                            <div
                                class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                                <dt class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <span>Impuesto ({{ $taxRate * 100 }}%)</span>
                                </dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    ${{ number_format($taxAmount, 2) }}</dd>
                            </div>
                            <div
                                class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                                <dt class="text-base font-semibold text-gray-900 dark:text-gray-100">Total a Pagar</dt>
                                <dd class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                    ${{ number_format($total, 2) }}</dd>
                            </div>
                        </dl>

                        {{-- SECCIÓN 5: ACCIONES --}}
                        <div class="mt-8">
                            <button wire:click="requestConfirmation" @if (!$selectedClient || empty($invoiceItems)) disabled @endif
                                class="w-full btn-primary">
                                Generar Factura
                            </button>
                            @if (!$selectedClient)
                                <div class="mt-2 text-xs text-blue-600 dark:text-blue-300 flex items-center gap-2">
                                    <i class="bi bi-info-circle"></i>
                                    Debes seleccionar un cliente antes de generar la factura.
                                </div>
                            @elseif(empty($invoiceItems))
                                <div class="mt-2 text-xs text-blue-600 dark:text-blue-300 flex items-center gap-2">
                                    <i class="bi bi-info-circle"></i>
                                    Debes agregar al menos un producto antes de generar la factura.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Incluimos el modal de confirmación, que será controlado por este componente --}}
    @include('livewire.shared.confirmation-modal')
</div>
