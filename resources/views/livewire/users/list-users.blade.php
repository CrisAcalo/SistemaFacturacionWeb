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
                                class="table-header font-semibold uppercase tracking-wide text-sm px-6 py-3">Roles</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($users as $user)
                            <tr wire:key="user-{{ $user->id }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ $user->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @foreach ($user->roles as $role)
                                        <span
                                            class="inline-flex px-2 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                    <button wire:click="edit({{ $user->id }})"
                                        class="font-medium text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">Editar</button>
                                    <button wire:click="confirmDelete({{ $user->id }})"
                                        class="ml-4 font-medium text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Eliminar</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4"
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
