<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Gestión de Productos
        </h2>
    </x-slot>

    <div class="p-4 mx-auto sm:p-6 lg:p-8">
        <div class="p-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">

            {{-- Controles y Buscador --}}
            <div class="flex flex-col items-center justify-between space-y-4 md:flex-row md:space-y-0 md:space-x-4">
                {{-- Buscador --}}
                <div class="w-full md:w-1/2">
                    <form autocomplete="off">
                        <input wire:model.live.debounce.300ms="search" type="text" id="search_products"
                            name="search_products" placeholder="Buscar por Nombre, SKU o Descripción..."
                            autocomplete="off" wire:key="search-products-input"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-primary focus:border-primary">
                    </form>
                </div>

                {{-- Controles --}}
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <span class="mr-2 text-sm text-gray-700 dark:text-gray-300">Mostrar</span>
                        <select wire:model.live="perPage"
                            class="px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-primary focus:border-primary">
                            @foreach ($perPageOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">por página</span>
                    </div>

                    <button type="button" wire:click="create" class="btn-primary">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span class="ml-2">Crear Producto</span>
                    </button>
                </div>
            </div>

            {{-- Tabla de Productos --}}
            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="table-thead-colored">
                        <tr>
                            <th scope="col" class="table-header">SKU</th>
                            <th scope="col" class="table-header">Nombre</th>
                            <th scope="col" class="table-header">Código de Barras</th>
                            <th scope="col" class="table-header text-center">Stock</th>
                            <th scope="col" class="table-header text-right">Precio</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($products as $product)
                            {{-- Inicializamos Alpine en cada fila, pasando el barcode como dato --}}
                            <tr wire:key="product-{{ $product->id }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-mono text-gray-500 dark:text-gray-400">{{ $product->sku }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $product->name }}</div>
                                    <div class="text-xs text-gray-500 truncate max-w-xs">{{ $product->description }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{-- x-init se ejecuta cuando el elemento se inserta en el DOM --}}
                                    <svg data-barcode="{{ $product->barcode }}"></svg>
                                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        {{ $product->barcode }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span
                                        class="px-2 py-1 text-sm font-bold rounded-md
                    @if ($product->stock > 10) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                    @elseif($product->stock > 0) bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                    @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                                        {{ $product->stock }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">$
                                        {{ number_format($product->price, 2) }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                    <button wire:click="edit({{ $product->id }})"
                                        class="font-medium text-secondary hover:text-secondary/80">Editar</button>
                                    <button wire:click="confirmDelete({{ $product->id }})"
                                        class="ml-4 font-medium text-danger hover:text-danger/80">Eliminar</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6"
                                    class="px-6 py-4 text-center text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    No se encontraron productos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="mt-4">
                <div class="bg-white dark:bg-gray-800 rounded-md">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Incluir todos los modales desde un archivo parcial --}}
    @include('livewire.products.modals')

</div>
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        // Función para renderizar TODOS los códigos de barras visibles en la tabla
        function renderAllBarcodesInTable() {
            // Buscamos todos los elementos que hemos marcado para ser un código de barras
            const barcodeElements = document.querySelectorAll('svg[data-barcode]');

            barcodeElements.forEach(el => {
                const barcodeValue = el.getAttribute('data-barcode');
                // Evitar redibujar si ya tiene contenido, a menos que el valor haya cambiado
                // (Livewire puede no eliminar el SVG, solo su contenido)
                if (barcodeValue && el.innerHTML.trim() === '') {
                    try {
                        const isDarkMode = document.documentElement.classList.contains('dark');
                        JsBarcode(el, barcodeValue, {
                            format: "EAN13",
                            lineColor: isDarkMode ? "#e5e7eb" : "#1f2937",
                            width: 1.5,
                            height: 35,
                            displayValue: false,
                            background: 'transparent',
                            margin: 0,
                        });
                    } catch (e) {
                        console.error(`Error al generar barcode para el valor "${barcodeValue}":`, e.message);
                    }
                }
            });
        }

        // Ejecutar la función en la carga inicial de la página
        document.addEventListener('DOMContentLoaded', () => {
            renderAllBarcodesInTable();
        });

        // Escuchar el evento personalizado que despachamos desde el backend
        // Se ejecuta DESPUÉS de cada actualización de Livewire
        window.addEventListener('render-barcodes', event => {
            // Usamos un pequeño timeout para asegurarnos de que el DOM de Livewire ha terminado
            // su proceso de 'morphing' antes de que nuestro script intente acceder a él.
            setTimeout(() => {
                renderAllBarcodesInTable();
            }, 10); // 10 milisegundos suele ser suficiente
        });
    </script>
@endpush
