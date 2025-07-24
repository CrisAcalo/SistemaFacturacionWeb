<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Gestión de Tokens de API
        </h2>
    </x-slot>

    <div class="p-4 mx-auto sm:p-6 lg:p-8">
        <div class="p-6 overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">

            {{-- Controles y Buscador --}}
            <div class="flex flex-col items-center justify-between space-y-4 md:flex-row md:space-y-0 md:space-x-4">
                {{-- Buscador --}}
                <div class="w-full md:w-1/2">
                    <form autocomplete="off">
                        <input wire:model.live.debounce.200ms="search" type="text"
                            placeholder="Buscar por nombre, descripción o usuario..."
                            autocomplete="new-search-{{ uniqid() }}" autocorrect="off" spellcheck="false"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-primary focus:border-primary">
                    </form>
                </div>

                {{-- Controles --}}
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Estado:</span>
                        <div class="relative">
                            <select wire:model.live="statusFilter"
                                class="pl-8 pr-8 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none">
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                                <i class="bi bi-funnel text-gray-400"></i>
                            </div>
                            <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none">
                                <i class="bi bi-chevron-down text-gray-400"></i>
                            </div>
                        </div>
                    </div>

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
                        <i class="bi bi-key-fill"></i>
                        <span class="ml-2">Crear Token</span>
                    </button>
                </div>
            </div>

            {{-- Tabla de Tokens --}}
            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="table-thead-colored">
                        <tr>
                            <th scope="col"
                                class="table-header font-semibold uppercase tracking-wide text-sm px-6 py-3">Token</th>
                            <th scope="col"
                                class="table-header font-semibold uppercase tracking-wide text-sm px-6 py-3">Valor del
                                Token</th>
                            <th scope="col"
                                class="table-header font-semibold uppercase tracking-wide text-sm px-6 py-3">Usuario
                            </th>
                            <th scope="col"
                                class="table-header font-semibold uppercase tracking-wide text-sm px-6 py-3">Última
                                Actividad</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($tokens as $token)
                            <tr wire:key="token-{{ $token->id }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-500 to-pink-600 flex items-center justify-center">
                                                <i class="bi bi-key-fill text-white"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $token->name }}</div>
                                            @if ($token->description)
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ Str::limit($token->description, 30) }}</div>
                                            @endif
                                            <div class="text-xs text-gray-400">ID: {{ $token->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2 p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                                <div class="flex-1">
                                                    <input type="text"
                                                           readonly
                                                           value="{{ $token->plain_text_token ?? 'Token no disponible' }}"
                                                           id="token-{{ $token->id }}"
                                                           class="w-full px-2 py-1 text-xs font-mono bg-transparent border-0 focus:outline-none text-blue-800 dark:text-blue-200">
                                                </div>
                                                @if($token->plain_text_token)
                                                    <button onclick="copyTokenToClipboard('token-{{ $token->id }}')"
                                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                                                        <i class="bi bi-clipboard"></i>
                                                    </button>
                                                @else
                                                    <div class="text-xs text-gray-400 dark:text-gray-500">
                                                        <i class="bi bi-exclamation-triangle"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            {{-- @if($token->plain_text_token)
                                                <div class="text-xs text-blue-600 dark:text-blue-400 mt-1 font-medium">
                                                    <i class="bi bi-clipboard-check mr-1"></i>
                                                    Disponible para copiar
                                                </div>
                                            @else
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    <i class="bi bi-info-circle mr-1"></i>
                                                    Token creado antes de esta funcionalidad
                                                </div>
                                            @endif --}}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <div class="flex-shrink-0 w-8 h-8">
                                            <div
                                                class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-xs">
                                                {{ strtoupper(substr($token->tokenable->name, 0, 2)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $token->tokenable->name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $token->tokenable->email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    @if ($token->last_used_at)
                                        <div class="flex items-center space-x-1">
                                            <i class="bi bi-clock text-green-500"></i>
                                            <span>{{ $token->last_used_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            {{ $token->last_used_at->format('d/m/Y H:i') }}
                                        </div>
                                    @else
                                        <div class="flex items-center space-x-1">
                                            <i class="bi bi-clock-history text-gray-400"></i>
                                            <span>Nunca usado</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button wire:click="confirmDelete({{ $token->id }})"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-lg border border-red-200 hover:bg-red-100 hover:text-red-800 transition-colors duration-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800 dark:hover:bg-red-900/40">
                                            <i class="bi bi-trash mr-1"></i>
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-3">
                                        <div
                                            class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                            <i class="bi bi-key text-2xl text-gray-400"></i>
                                        </div>
                                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">No hay tokens
                                            disponibles</h3>
                                        <p class="text-sm text-gray-400 dark:text-gray-500">Crea tu primer token de API
                                            para comenzar.</p>
                                        <button wire:click="create" class="btn-primary">
                                            <i class="bi bi-plus-circle mr-1"></i>
                                            Crear Token
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="mt-4">
                <div class="bg-white dark:bg-gray-800 rounded-md">
                    {{ $tokens->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Incluir modales --}}
    @include('livewire.tokens.modals')
    @include('livewire.shared.confirmation-modal')

    {{-- Script para copiar tokens --}}
    @push('scripts')
        <script>
            function copyTokenToClipboard(elementId) {
                const element = document.getElementById(elementId);
                if (!element) return;

                // Verificar si hay contenido válido
                // if (!element.value || element.value === 'Token no disponible') {
                //     alert('Token no disponible para copiar');
                //     return;
                // }

                element.select();
                element.setSelectionRange(0, 99999); // Para móviles

                try {
                    document.execCommand('copy');

                    // Mostrar feedback visual
                    const button = element.parentElement.nextElementSibling;
                    if (button) {
                        const originalContent = button.innerHTML;
                        button.innerHTML = '<i class="bi bi-check"></i>';
                        button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                        button.classList.add('bg-green-600');

                        setTimeout(() => {
                            button.innerHTML = originalContent;
                            button.classList.remove('bg-green-600');
                            button.classList.add('bg-blue-600', 'hover:bg-blue-700');
                        }, 2000);
                    }

                    // Mostrar notificación
                    if (window.Livewire) {
                        window.Livewire.dispatch('show-toast', {
                            message: 'Token copiado al portapapeles',
                            type: 'success'
                        });
                    }
                } catch (err) {
                    console.error('Error al copiar token:', err);
                    // Fallback para navegadores modernos
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(element.value).then(() => {
                            console.log('Token copiado usando clipboard API');
                            if (window.Livewire) {
                                window.Livewire.dispatch('show-toast', {
                                    message: 'Token copiado al portapapeles',
                                    type: 'success'
                                });
                            }
                        }).catch(err => {
                            console.error('Error con clipboard API:', err);
                            alert('Error al copiar el token');
                        });
                    } else {
                        alert('No se pudo copiar el token. Copia manualmente el texto.');
                    }
                }
            }
        </script>
    @endpush
</div>
