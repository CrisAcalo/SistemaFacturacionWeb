<!-- Modal para Aprobar Pago Individual -->
@if ($showApproveModal)
    <div x-data="{ show: @entangle('showApproveModal') }" x-show="show" x-on:keydown.escape.window="show = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 dark:bg-black/80 backdrop-blur-sm transition-all duration-300"
        style="display: none;">
        <div x-show="show" x-transition
            class="relative w-full max-w-md mx-auto overflow-hidden bg-white dark:bg-gray-900 border rounded-lg shadow-xl dark:border-gray-700">

            <div class="flex items-center justify-between px-6 py-4 border-b bg-green-500/20 dark:bg-green-600/30 border-green-500/40">
                <div class="flex items-center gap-3">
                    <i class="bi-check-circle-fill text-2xl text-green-600"></i>
                    <h2 class="text-xl font-semibold text-green-700 dark:text-green-300">
                        Aprobar Pago
                    </h2>
                </div>
                <button type="button" wire:click="closeModals" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="bi-x-lg text-xl"></i>
                </button>
            </div>

            <form wire:submit="confirmApproval">
                <div class="p-6 space-y-4">
                    @if($selectedPayment)
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Pago seleccionado:</div>
                            <div class="font-medium text-gray-900 dark:text-white">
                                Pago #{{ $selectedPayment->id }} - ${{ number_format($selectedPayment->amount, 2) }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                Factura: {{ $selectedPayment->invoice->invoice_number }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                Cliente: {{ $selectedPayment->client->name }}
                            </div>
                        </div>
                    @endif

                    <div>
                        <label for="validationNotes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Notas de validación (opcional)
                        </label>
                        <textarea
                            id="validationNotes"
                            wire:model="validationNotes"
                            rows="3"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Agregar comentarios sobre la aprobación..."
                        ></textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 px-6 py-4 bg-gray-50 dark:bg-gray-800">
                    <button
                        type="button"
                        wire:click="closeModals"
                        class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-white font-bold py-2 px-4 rounded transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="bg-green-500 hover:bg-green-700 font-bold py-2 px-4 rounded transition-colors"
                    >
                        Continuar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

<!-- Modal para Rechazar Pago Individual -->
@if ($showRejectModal)
    <div x-data="{ show: @entangle('showRejectModal') }" x-show="show" x-on:keydown.escape.window="show = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 dark:bg-black/80 backdrop-blur-sm transition-all duration-300"
        style="display: none;">
        <div x-show="show" x-transition
            class="relative w-full max-w-md mx-auto overflow-hidden bg-white dark:bg-gray-900 border rounded-lg shadow-xl dark:border-gray-700">

            <div class="flex items-center justify-between px-6 py-4 border-b bg-red-500/20 dark:bg-red-600/30 border-red-500/40">
                <div class="flex items-center gap-3">
                    <i class="bi-x-circle-fill text-2xl text-red-600"></i>
                    <h2 class="text-xl font-semibold text-red-700 dark:text-red-300">
                        Rechazar Pago
                    </h2>
                </div>
                <button type="button" wire:click="closeModals" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="bi-x-lg text-xl"></i>
                </button>
            </div>

            <form wire:submit="confirmRejection">
                <div class="p-6 space-y-4">
                    @if($selectedPayment)
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Pago seleccionado:</div>
                            <div class="font-medium text-gray-900 dark:text-white">
                                Pago #{{ $selectedPayment->id }} - ${{ number_format($selectedPayment->amount, 2) }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                Factura: {{ $selectedPayment->invoice->invoice_number }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                Cliente: {{ $selectedPayment->client->name }}
                            </div>
                        </div>
                    @endif

                    <div>
                        <label for="validationNotesReject" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Motivo del rechazo <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="validationNotesReject"
                            wire:model="validationNotes"
                            rows="3"
                            required
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Especifica el motivo del rechazo..."
                        ></textarea>
                        @error('validationNotes')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end space-x-3 px-6 py-4 bg-gray-50 dark:bg-gray-800">
                    <button
                        type="button"
                        wire:click="closeModals"
                        class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-white font-bold py-2 px-4 rounded transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="bg-red-500 hover:bg-red-700 font-bold py-2 px-4 rounded transition-colors"
                    >
                        Continuar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif


<!-- Modal de Confirmación (usando el Form Object) --><!-- Modal de Confirmación (usando el Form Object) -->
@if ($showConfirmationModal)
    <div x-data="{ show: @entangle('showConfirmationModal') }" x-show="show" x-on:keydown.escape.window="show = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 dark:bg-black/80 backdrop-blur-sm transition-all duration-300"
        style="display: none;">
        <div x-show="show" x-transition
            class="relative w-full max-w-md mx-auto overflow-hidden bg-white dark:bg-gray-900 border rounded-lg shadow-xl dark:border-gray-700">

            <div class="flex items-center justify-between px-6 py-4 border-b bg-amber-400/20 dark:bg-amber-500/30 border-amber-400/40">
                <div class="flex items-center gap-3">
                    <i class="bi-exclamation-triangle-fill text-2xl text-amber-600"></i>
                    <h2 class="text-xl font-semibold text-amber-700 dark:text-amber-300">
                        {{ $confirmationTitle }}
                    </h2>
                </div>
                <button type="button" wire:click="closeModals" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="bi-x-lg text-xl"></i>
                </button>
            </div>

            <form wire:submit="executeAction">
                <div class="p-6 space-y-4">
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                        <div class="text-sm text-yellow-800 dark:text-yellow-200">
                            <strong>¿Estás seguro?</strong> Esta acción no se puede deshacer.
                        </div>
                    </div>

                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Motivo de la acción <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="reason"
                            wire:model="confirmation.reason"
                            rows="3"
                            required
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Describe el motivo de esta acción..."
                        ></textarea>
                        @error('confirmation.reason')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Confirma tu contraseña <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="password"
                            id="password"
                            wire:model="confirmation.password"
                            required
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Tu contraseña actual"
                        />
                        @error('confirmation.password')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input
                            type="checkbox"
                            id="confirm"
                            wire:model="confirmation.confirm"
                            required
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600"
                        />
                        <label for="confirm" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Confirmo que quiero realizar esta acción
                        </label>
                    </div>
                    @error('confirmation.confirm')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex justify-end space-x-3 px-6 py-4 bg-gray-50 dark:bg-gray-800">
                    <button
                        type="button"
                        wire:click="closeModals"
                        class="bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-white font-bold py-2 px-4 rounded transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="{{ $confirmationButtonColor }} font-bold py-2 px-4 rounded transition-colors"
                    >
                        {{ $confirmationButtonText }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
