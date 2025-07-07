@if ($showFormModal)
    <div x-data="{ show: @entangle('showFormModal') }" x-show="show" x-on:keydown.escape.window="show = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 dark:bg-black/80 backdrop-blur-sm transition-all duration-300"
        style="display: none;">
        <div x-show="show" x-transition
            class="relative w-full max-w-2xl mx-auto overflow-hidden bg-white dark:bg-gray-900 border rounded-lg shadow-xl dark:border-gray-700">

            <div class="flex items-center justify-between px-6 py-4 border-b"
                :class="{
                    'bg-blue-500/20 dark:bg-blue-600/30 border-blue-500/40': {{ !$isEditing ? 'true' : 'false' }},
                    'bg-amber-400/20 dark:bg-amber-500/30 border-amber-400/40': {{ $isEditing ? 'true' : 'false' }}
                }">
                <div class="flex items-center gap-3">
                    <i class="text-2xl"
                        :class="{
                            'bi-plus-circle-fill text-blue-600': {{ !$isEditing ? 'true' : 'false' }},
                            'bi-pencil-square text-amber-500': {{ $isEditing ? 'true' : 'false' }}
                        }"></i>
                    <h2 class="text-xl font-semibold"
                        :class="{
                            'text-blue-700 dark:text-blue-300': {{ !$isEditing ? 'true' : 'false' }},
                            'text-amber-700 dark:text-amber-200': {{ $isEditing ? 'true' : 'false' }}
                        }">
                        {{ $isEditing ? 'Editar Producto' : 'Crear Nuevo Producto' }}
                    </h2>
                </div>
                <button @click="show = false"
                    class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-white transition">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>

            <!-- Cuerpo del Modal -->
            <form wire:submit.prevent="requestConfirmation" class="p-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="form.sku" class="block text-sm font-medium text-gray-700 dark:text-gray-300">SKU (Código Único)</label>
                        <input wire:model.defer="form.sku" id="form.sku" type="text" readonly
                            class="block w-full mt-1 input-style bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-600 cursor-not-allowed placeholder-gray-400 dark:placeholder-gray-500">
                        @error('form.sku')
                            <span class="text-xs text-red-500 dark:text-red-400">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Barcode -->
                    <div>
                        <label for="form.barcode" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Código de Barras</label>
                        <input wire:model.defer="form.barcode" id="form.barcode" type="text" readonly
                            class="block w-full mt-1 input-style bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-600 cursor-not-allowed placeholder-gray-400 dark:placeholder-gray-500">

                        @error('form.barcode')
                            <span class="text-xs text-red-500 dark:text-red-400">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <!-- Nombre -->
                <div class="mt-4">
                    <label for="form.name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre del Producto</label>
                    <input wire:model.live.debounce.300ms="form.name" id="form.name" type="text" autocomplete="off"
                        class="block w-full mt-1 input-style bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-600 placeholder-gray-400 dark:placeholder-gray-500">
                    @error('form.name')
                        <span class="text-xs text-red-500 dark:text-red-400">{{ $message }}</span>
                    @enderror
                </div>
                <!-- Descripción -->
                <div class="mt-4">
                    <label for="form.description"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                    <textarea wire:model="form.description" id="form.description" rows="3"
                        class="block w-full mt-1 input-style bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-600 placeholder-gray-400 dark:placeholder-gray-500"></textarea>
                    @error('form.description')
                        <span class="text-xs text-red-500 dark:text-red-400">{{ $message }}</span>
                    @enderror
                </div>
                <div class="grid grid-cols-1 gap-6 mt-4 md:grid-cols-2">
                    <!-- Precio -->
                    <div>
                        <label for="form.price"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Precio</label>
                        <div class="relative mt-1">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400 sm:text-sm">$</span>
                            </div>
                            <input wire:model="form.price" id="form.price" type="text"
                                class="block w-full pl-7 input-style bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-600 placeholder-gray-400 dark:placeholder-gray-500" placeholder="0.00">
                        </div>
                        @error('form.price')
                            <span class="text-xs text-red-500 dark:text-red-400">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Stock -->
                    <div>
                        <label for="form.stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stock Inicial</label>
                        <input wire:model="form.stock" id="form.stock" type="number"
                            class="block w-full mt-1 input-style bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-600 placeholder-gray-400 dark:placeholder-gray-500">
                        @error('form.stock')
                            <span class="text-xs text-red-500 dark:text-red-400">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Footer del Formulario -->
                <div class="flex justify-end pt-8 space-x-4">
                    <button type="button" @click="show = false" class="btn-secondary">Cancelar</button>
                    <button type="submit" class="btn-primary">Siguiente <i
                            class="ml-1 bi bi-arrow-right-circle-fill"></i></button>
                </div>
            </form>
        </div>
    </div>
@endif
@include('livewire.shared.confirmation-modal')
