<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Listado de Facturas
        </h2>
    </x-slot>

    <div class="p-4 mx-auto sm:p-6 lg:p-8">
        <div class="p-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">

            {{-- Controles y Buscador --}}
            <div class="flex flex-col items-center justify-between space-y-4 md:flex-row md:space-y-0 md:space-x-4">
                <div class="w-full md:w-1/2">
                    <form autocomplete="off">
                        <input wire:model.live.debounce.300ms="search" type="text"
                            id="search_invoices_{{ uniqid() }}" name="search_invoices_{{ uniqid() }}"
                            placeholder="Buscar por Nº Factura, Cliente o Vendedor..."
                            autocomplete="new-search-{{ uniqid() }}" autocorrect="off" spellcheck="false"
                            wire:key="search-input" class="w-full px-4 py-2 input-style">
                    </form>
                </div>
                <div class="flex items-center space-x-4">
                    {{-- Aquí podríamos añadir filtros por estado o fecha en el futuro --}}
                    <a href="{{ route('invoices.create') }}" wire:navigate class="btn-primary">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span class="ml-2">Crear Nueva Factura</span>
                    </a>
                </div>
            </div>

            {{-- Tabla de Facturas --}}
            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="table-thead-colored">
                        <tr>
                            <th scope="col" class="table-header">Nº Factura</th>
                            <th scope="col" class="table-header">Cliente</th>
                            <th scope="col" class="table-header">Vendedor</th>
                            <th scope="col" class="table-header text-center">Estado</th>
                            <th scope="col" class="table-header text-right">Total</th>
                            <th scope="col" class="table-header text-center">Fecha</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($invoices as $invoice)
                            <tr wire:key="invoice-{{ $invoice->id }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-mono text-sm text-gray-600 dark:text-gray-400">
                                        {{ $invoice->invoice_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ $invoice->client->name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-300">{{ $invoice->user->name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span
                                        class="px-2 py-1 text-xs font-semibold leading-5 rounded-full
                                        @if ($invoice->status === 'Pagada') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @elseif($invoice->status === 'Pendiente') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200 @endif">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-right text-gray-900 dark:text-gray-100">
                                    ${{ number_format($invoice->total, 2) }}
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $invoice->created_at->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                    {{-- Botón para abrir el modal --}}
                                    <button type="button"
                                        @click="window.dispatchEvent(new CustomEvent('show-invoice-modal', { detail: { id: {{ $invoice->id }} } }))"
                                        class="font-medium text-secondary hover:text-secondary/80">
                                        Ver
                                    </button>
                                    @if ($invoice->status !== 'Anulada')
                                        <button wire:click="confirmCancel({{ $invoice->id }})"
                                            class="ml-4 font-medium text-danger hover:text-danger/80">Anular</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No se encontraron facturas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $invoices->links() }}
            </div>
        </div>
    </div>

    {{-- El modal de confirmación se reutilizará aquí para la anulación --}}
    @include('livewire.shared.confirmation-modal')

    {{-- Modal para mostrar detalles de la factura --}}
    <div x-data="{
        show: false,
        invoice: null,
        invoices: @js($invoices->keyBy('id')),
        open(id) {
            this.invoice = this.invoices[id];
            this.show = true;
        }
    }" x-on:show-invoice-modal.window="open($event.detail.id)" x-show="show"
        style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 dark:bg-black/80 backdrop-blur-sm">
        <div x-show="show" x-transition
            class="relative w-full max-w-lg mx-auto bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div
                class="flex items-center justify-between px-6 py-4 border-b bg-primary/10 dark:bg-primary/20 border-primary/20">
                <div class="flex items-center gap-3">
                    <i class="bi bi-receipt text-2xl text-primary-500"></i>
                    <h3 class="text-lg font-semibold text-primary-700 dark:text-primary-300">
                        Detalle Factura: <span x-text="invoice?.invoice_number"></span>
                    </h3>
                </div>
                <button @click="show = false"
                    class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-white transition">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>
            <div class="px-6 py-6 space-y-4">
                <template x-if="invoice">
                    <div>
                        <div class="mb-2 text-sm text-gray-700 dark:text-gray-300">
                            <strong>Cliente:</strong> <span x-text="invoice.client.name"></span>
                        </div>
                        <div class="mb-2 text-sm text-gray-700 dark:text-gray-300">
                            <strong>Vendedor:</strong> <span x-text="invoice.user.name"></span>
                        </div>
                        <div class="mb-2 text-sm text-gray-700 dark:text-gray-300">
                            <strong>Fecha:</strong> <span
                                x-text="(new Date(invoice.created_at)).toLocaleDateString()"></span>
                        </div>
                        <div class="mb-2 text-sm text-gray-700 dark:text-gray-300">
                            <strong>Estado:</strong>
                            <span
                                :class="{
                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': invoice
                                        .status === 'paid',
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': invoice
                                        .status === 'pending',
                                    'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200': invoice
                                        .status !== 'paid' && invoice.status !== 'pending'
                                }"
                                class="px-2 py-1 rounded-full text-xs font-semibold"
                                x-text="invoice.status.charAt(0).toUpperCase() + invoice.status.slice(1)">
                            </span>
                        </div>
                        <div class="mb-4">
                            <strong class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Items:</strong>
                            <ul class="space-y-1 text-left">
                                <template x-for="item in invoice.items" :key="item.id">
                                    <li class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-300"
                                            x-text="item.quantity + ' x ' + (item.product ? item.product.name : '')"></span>
                                        <span class="font-mono text-gray-800 dark:text-gray-200"
                                            x-text="'$' + Number(item.total).toFixed(2)"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                        <div
                            class="flex justify-between font-bold text-gray-900 dark:text-gray-100 border-t pt-2 dark:border-gray-600">
                            <span>Total:</span>
                            <span x-text="'$' + Number(invoice.total).toFixed(2)"></span>
                        </div>
                        <template x-if="invoice.status === 'Anulada'">
                            <p class="mt-2 text-xs text-center text-white bg-red-500 rounded-full">
                                ANULADA
                            </p>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
