<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Gestión de Clientes
        </h2>
    </x-slot>

    <div class="p-4 mx-auto sm:p-6 lg:p-8">
        <div class="p-6 overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">

            {{-- Controles y Buscador --}}
            <div class="flex flex-col items-center justify-between space-y-4 md:flex-row md:space-y-0 md:space-x-4">
                {{-- Buscador --}}
                <div class="w-full md:w-1/2">
                    <form autocomplete="off">
                        <input wire:model.live.debounce.200ms="search" type="text" id="search_users_{{ uniqid() }}"
                            name="search_users_{{ uniqid() }}" placeholder="Buscar cliente por nombre o email..."
                            autocomplete="new-search-{{ uniqid() }}" autocorrect="off" spellcheck="false"
                            wire:key="search-input"
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

                    <a href="{{ route('clients.trash') }}" wire:navigate class="btn-secondary">
                        <i class="bi bi-trash3"></i>
                        <span class="ml-2">Papelera</span>
                    </a>


                    <button type="button" wire:click="create" class="btn-primary">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span class="ml-2">Crear Cliente</span>
                    </button>
                </div>
            </div>

            {{-- Tabla de Clientes --}}
            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="table-thead-colored">
                        <tr>
                            <th scope="col"
                                class="table-header font-semibold uppercase tracking-wide text-sm px-6 py-3">Nombre</th>
                            <th scope="col"
                                class="table-header font-semibold uppercase tracking-wide text-sm px-6 py-3">Email</th>
                            <th scope="col"
                                class="table-header font-semibold uppercase tracking-wide text-sm px-6 py-3">Estado</th>
                            <th scope="col"
                                class="table-header font-semibold uppercase tracking-wide text-sm px-6 py-3">Roles</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($users as $user)
                            <tr wire:key="user-{{ $user->id }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0 w-10 h-10">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">ID: {{ $user->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <i class="bi bi-envelope text-gray-400"></i>
                                        <span class="text-sm text-gray-900 dark:text-gray-100">{{ $user->email }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="flex items-center justify-center w-8 h-8 rounded-full
                                                {{ $user->status === 'active' 
                                                    ? 'bg-green-100 dark:bg-green-800' 
                                                    : 'bg-red-100 dark:bg-red-800' }}">
                                                <i class="text-sm {{ $user->status === 'active' 
                                                    ? 'bi bi-check-circle-fill text-green-600 dark:text-green-300' 
                                                    : 'bi bi-x-circle-fill text-red-600 dark:text-red-300' }}"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $user->status === 'active' 
                                                    ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' 
                                                    : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                                                {{ $user->status === 'active' ? 'Activo' : 'Inactivo' }}
                                            </span>
                                            @if($user->id !== auth()->id())
                                                <div class="mt-1">
                                                    <button wire:click="toggleUserStatus({{ $user->id }})" 
                                                        class="text-xs font-medium transition-colors duration-200 {{ $user->status === 'active' 
                                                            ? 'text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300' 
                                                            : 'text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300' }}"
                                                        title="{{ $user->status === 'active' ? 'Desactivar usuario' : 'Activar usuario' }}">
                                                        <i class="bi {{ $user->status === 'active' ? 'bi-toggle-off' : 'bi-toggle-on' }} mr-1"></i>
                                                        {{ $user->status === 'active' ? 'Desactivar' : 'Activar' }}
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($user->roles as $role)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                bg-gradient-to-r from-blue-100 to-purple-100 text-blue-800 
                                                dark:from-blue-800 dark:to-purple-800 dark:text-blue-100 
                                                border border-blue-200 dark:border-blue-600">
                                                <i class="bi bi-person-badge mr-1"></i>
                                                {{ $role->name }}
                                            </span>
                                        @empty
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                <i class="bi bi-person-x mr-1"></i>
                                                Sin roles
                                            </span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button wire:click="edit({{ $user->id }})"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg border border-blue-200 hover:bg-blue-100 hover:text-blue-800 transition-colors duration-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800 dark:hover:bg-blue-900/40">
                                            <i class="bi bi-pencil mr-1"></i>
                                            Editar
                                        </button>
                                        <button wire:click="confirmDelete({{ $user->id }})"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-lg border border-red-200 hover:bg-red-100 hover:text-red-800 transition-colors duration-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800 dark:hover:bg-red-900/40">
                                            <i class="bi bi-trash mr-1"></i>
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5"
                                    class="px-6 py-4 text-center text-gray-500 dark:text-gray-400 whitespace-nowrap">No
                                    se
                                    encontraron usuarios.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="mt-4">
                <div class="bg-white dark:bg-gray-800 rounded-md">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Incluir todos los modales desde un archivo parcial --}}
    @include('livewire.users.modals')
    @include('livewire.shared.confirmation-modal')
</div>
