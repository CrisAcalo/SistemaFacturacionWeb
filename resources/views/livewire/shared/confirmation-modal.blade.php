@if ($showConfirmationModal)
    <div x-data="{ show: @entangle('showConfirmationModal') }" x-show="show" x-on:keydown.escape.window="show = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 dark:bg-black/90 backdrop-blur-sm transition-all duration-300"
        style="display: none;">
        <div x-show="show" x-transition
            class="relative w-full max-w-md mx-auto bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b"
                :class="{
                    'bg-red-600/20 dark:bg-red-700/30 border-red-600/40': {{ $actionType === 'delete' ? 'true' : 'false' }},
                    'bg-blue-500/20 dark:bg-blue-600/30 border-blue-500/40': {{ $actionType !== 'delete' && !$isEditing ? 'true' : 'false' }},
                    'bg-amber-400/20 dark:bg-amber-500/30 border-amber-400/40': {{ $actionType !== 'delete' && $isEditing ? 'true' : 'false' }}
                }">
                <div class="flex items-center gap-3">
                    <i class="text-2xl"
                        :class="{
                            'bi-trash3-fill text-red-600': {{ $actionType === 'delete' ? 'true' : 'false' }},
                            'bi-plus-circle-fill text-blue-600': {{ $actionType !== 'delete' && !$isEditing ? 'true' : 'false' }},
                            'bi-pencil-square text-amber-500': {{ $actionType !== 'delete' && $isEditing ? 'true' : 'false' }}
                        }"></i>
                    <h3 class="text-lg font-semibold"
                        :class="{
                            'text-red-700 dark:text-red-300': {{ $actionType === 'delete' ? 'true' : 'false' }},
                            'text-blue-700 dark:text-blue-300': {{ $actionType !== 'delete' && !$isEditing ? 'true' : 'false' }},
                            'text-amber-700 dark:text-amber-200': {{ $actionType !== 'delete' && $isEditing ? 'true' : 'false' }}
                        }">
                        {{ $confirmationTitle }}
                    </h3>
                </div>
                <button @click="show = false"
                    class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-white transition">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>
            <div class="px-6 py-6 space-y-5">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Para completar esta acción, por favor confirma tu identidad y proporciona un motivo.
                </p>
                <!-- Motivo -->
                <div>
                    <label for="confirmation.reason"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Motivo</label>
                    <textarea wire:model="confirmation.reason" id="confirmation.reason" rows="3"
                        class="block w-full mt-1 input-style bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"></textarea>
                    @error('confirmation.reason')
                        <span class="text-xs text-red-500 dark:text-red-400">{{ $message }}</span>
                    @enderror
                </div>
                <!-- Contraseña -->
                <div>
                    <label for="confirmation.password"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tu Contraseña</label>
                    <input wire:model="confirmation.password" id="confirmation.password" type="password"
                        class="block w-full mt-1 input-style bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500">
                    @error('confirmation.password')
                        <span class="text-xs text-red-500 dark:text-red-400">{{ $message }}</span>
                    @enderror
                </div>
                <!-- Checkbox -->
                <div class="flex items-start gap-2">
                    <input wire:model="confirmation.confirm" id="confirmation.confirm" type="checkbox"
                        class="w-4 h-4 mt-1 rounded text-primary focus:ring-primary border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800">
                    <label for="confirmation.confirm" class="text-sm select-none text-gray-700 dark:text-gray-300">
                        Soy consciente de que esta acción quedará registrada en la auditoría.
                    </label>
                </div>
                @error('confirmation.confirm')
                    <span class="text-xs text-red-500 dark:text-red-400">{{ $message }}</span>
                @enderror
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="show = false" class="btn-secondary transition">Cancelar</button>
                    <button wire:click="executeAction"
                        class="{{ $confirmationButtonColor }} inline-flex items-center gap-2 px-4 py-2 font-bold text-white rounded-md transition disabled:opacity-50">
                        <i class="bi bi-check-circle"></i> {{ $confirmationButtonText }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
