{{-- Modal de Creación/Edición de Token --}}
<div x-data="{ show: @entangle('showCreateModal') }"
     x-show="show"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">

    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75"
             x-on:click="$wire.closeCreateModal()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-2xl">

            <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    <i class="bi bi-key-fill text-purple-600 mr-2"></i>
                    {{ $editingToken ? 'Editar Token' : 'Crear Nuevo Token' }}
                </h3>
                <button wire:click="closeCreateModal"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form wire:submit.prevent="save" class="mt-6 space-y-6">
                {{-- Información Básica --}}
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {{-- Nombre del Token --}}
                    <div>
                        <label for="form.name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nombre del Token *
                        </label>
                        <input type="text"
                               wire:model="form.name"
                               id="form.name"
                               autocomplete="off"
                               placeholder="Ej: API Web App, Mobile App Token"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm
                                      bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                      placeholder-gray-400 dark:placeholder-gray-500
                                      focus:outline-none focus:ring-purple-500 focus:border-purple-500
                                      dark:focus:ring-purple-400 dark:focus:border-purple-400">
                        @error('form.name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Usuario --}}
                    <div>
                        <label for="form.user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Usuario *
                        </label>
                        <select wire:model="form.user_id"
                                id="form.user_id"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm
                                       bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                       focus:outline-none focus:ring-purple-500 focus:border-purple-500
                                       dark:focus:ring-purple-400 dark:focus:border-purple-400">
                            <option value="">Seleccionar usuario...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        @error('form.user_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Descripción --}}
                <div>
                    <label for="form.description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Descripción (Opcional)
                    </label>
                    <textarea wire:model="form.description"
                              id="form.description"
                              rows="3"
                              placeholder="Describe el propósito de este token..."
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm
                                     bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                     placeholder-gray-400 dark:placeholder-gray-500
                                     focus:outline-none focus:ring-purple-500 focus:border-purple-500
                                     dark:focus:ring-purple-400 dark:focus:border-purple-400 resize-none"></textarea>
                    @error('form.description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Configuración de Expiración --}}
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {{-- Checkbox para token permanente --}}
                    <div class="flex items-center">
                        <input type="checkbox"
                               wire:model.live="form.never_expires"
                               id="form.never_expires"
                               class="w-4 h-4 text-purple-600 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded
                                      focus:ring-purple-500 dark:focus:ring-purple-400 focus:ring-2">
                        <label for="form.never_expires" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            Token Permanente (Sin expiración)
                        </label>
                    </div>

                    {{-- Fecha de Expiración --}}
                    @if(!$form['never_expires'])
                        <div>
                            <label for="form.expires_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Fecha de Expiración
                            </label>
                            <input type="datetime-local"
                                   wire:model="form.expires_at"
                                   id="form.expires_at"
                                   min="{{ now()->format('Y-m-d\TH:i') }}"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm
                                          bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                          focus:outline-none focus:ring-purple-500 focus:border-purple-500
                                          dark:focus:ring-purple-400 dark:focus:border-purple-400">
                            @error('form.expires_at')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>

                {{-- Metadata Adicional --}}
                <div>
                    <label for="form.metadata_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Notas Adicionales (Opcional)
                    </label>
                    <input type="text"
                           wire:model="form.metadata.notes"
                           id="form.metadata_notes"
                           placeholder="Información adicional sobre el token..."
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm
                                  bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100
                                  placeholder-gray-400 dark:placeholder-gray-500
                                  focus:outline-none focus:ring-purple-500 focus:border-purple-500
                                  dark:focus:ring-purple-400 dark:focus:border-purple-400">
                </div>

                {{-- Botones de Acción --}}
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button type="button"
                            wire:click="closeCreateModal"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm
                                   hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500
                                   dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600
                                   transition-colors duration-200">
                        <i class="bi bi-x-circle mr-2"></i>
                        Cancelar
                    </button>

                    <button type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-md shadow-sm
                                   hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500
                                   disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                        <div wire:loading wire:target="save" class="mr-2">
                            <i class="bi bi-arrow-clockwise animate-spin"></i>
                        </div>
                        <div wire:loading.remove wire:target="save" class="mr-2">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <span wire:loading.remove wire:target="save">
                            {{ $editingToken ? 'Actualizar Token' : 'Crear Token' }}
                        </span>
                        <span wire:loading wire:target="save">
                            Procesando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal de Token Creado --}}
<div x-data="{ show: @entangle('showTokenModal') }"
     x-show="show"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">

    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-2xl">

            <div class="text-center">
                <div class="w-16 h-16 mx-auto mb-4 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center">
                    <i class="bi bi-check-circle-fill text-2xl text-green-600 dark:text-green-300"></i>
                </div>

                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    ¡Token Creado Exitosamente!
                </h3>

                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Tu token de API ha sido creado. Copia el token a continuación, ya que no podrás verlo nuevamente.
                </p>

                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">
                        Token de API (Bearer Token)
                    </label>
                    <div class="flex items-center space-x-2">
                        <input type="text"
                               readonly
                               value="{{ $newTokenValue }}"
                               id="new-token-value"
                               class="flex-1 px-3 py-2 text-sm font-mono bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md
                                      text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <button onclick="copyToClipboard('new-token-value')"
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-purple-600 rounded-md
                                       hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-colors">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>

                @if($newTokenInfo)
                    <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <div class="text-sm text-blue-800 dark:text-blue-300">
                            <strong>{{ $newTokenInfo['name'] }}</strong>
                            @if($newTokenInfo['description'])
                                <p class="text-xs mt-1">{{ $newTokenInfo['description'] }}</p>
                            @endif
                            @if($newTokenInfo['expires_at'])
                                <p class="text-xs mt-1">
                                    <i class="bi bi-clock mr-1"></i>
                                    Expira: {{ $newTokenInfo['expires_at'] }}
                                </p>
                            @else
                                <p class="text-xs mt-1">
                                    <i class="bi bi-infinity mr-1"></i>
                                    Sin fecha de expiración
                                </p>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="mt-6 flex justify-center space-x-3">
                    <button wire:click="closeTokenModal"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-md shadow-sm
                                   hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500
                                   transition-colors duration-200">
                        <i class="bi bi-check-circle mr-2"></i>
                        Entendido
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    document.execCommand('copy');

    // Mostrar feedback visual
    const button = element.nextElementSibling;
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="bi bi-check"></i>';
    button.classList.add('bg-green-600');
    button.classList.remove('bg-purple-600');

    setTimeout(() => {
        button.innerHTML = originalContent;
        button.classList.remove('bg-green-600');
        button.classList.add('bg-purple-600');
    }, 2000);
}
</script>
