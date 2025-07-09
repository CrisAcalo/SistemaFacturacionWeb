<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Papelera de Clientes
            </h2>
            <a href="{{ route('clients.index') }}" wire:navigate class="btn-secondary">
                <i class="bi bi-arrow-left-circle"></i>
                <span class="ml-2">Volver al Listado</span>
            </a>
        </div>
    </x-slot>

    <div class="p-4 mx-auto sm:p-6 lg:p-8">
        <div class="p-6 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">

            {{-- Buscador y Alerta --}}
            <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
                <div class="w-full md:w-1/2">

                    <form autocomplete="off">
                        <input wire:model.live.debounce.300ms="search" type="search"
                            placeholder="Buscar en la papelera..." class="w-full px-4 py-2 input-style">
                    </form>

                </div>
                <div class="w-full p-4 text-sm text-yellow-800 bg-yellow-100 border-l-4 border-yellow-500 dark:bg-yellow-900/50 dark:text-yellow-200"
                    role="alert">
                    <p class="font-bold">Atención</p>
                    <p>Los elementos eliminados permanentemente no podrán ser recuperados.</p>
                </div>
            </div>

            {{-- Tabla de Usuarios Eliminados --}}
            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="table-thead-colored">
                        <tr>
                            <th scope="col" class="table-header">Nombre</th>
                            <th scope="col" class="table-header">Email</th>
                            <th scope="col" class="table-header text-center">Fecha de Eliminación</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($trashedUsers as $user)
                            <tr wire:key="trashed-user-{{ $user->id }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ $user->email }}</div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $user->deleted_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                    <button wire:click="confirmRestore({{ $user->id }})"
                                        class="font-medium text-green-600 hover:text-green-800 dark:text-green-500 dark:hover:text-green-400">
                                        Restaurar
                                    </button>
                                    <button wire:click="confirmForceDelete({{ $user->id }})"
                                        class="ml-4 font-medium text-danger hover:text-danger/80">
                                        Eliminar Permanentemente
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    La papelera está vacía.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $trashedUsers->links() }}
            </div>
        </div>
    </div>

    {{-- Reutilizamos el modal de confirmación de seguridad --}}
    @include('livewire.shared.confirmation-modal')
</div>
