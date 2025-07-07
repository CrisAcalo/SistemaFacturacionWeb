@if ($showFormModal)
    <div x-data="{ show: @entangle('showFormModal') }" x-show="show" x-on:keydown.escape.window="show = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 dark:bg-black/80 backdrop-blur-sm transition-all duration-300"
        style="display: none;">
        <div x-show="show" x-transition
            class="relative w-full max-w-lg mx-auto bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
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
                        {{ $isEditing ? 'Editar Cliente' : 'Crear Nuevo Cliente' }}
                    </h2>
                </div>
                <button @click="show = false"
                    class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-white transition">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>
            <form wire:submit.prevent="requestConfirmation" class="px-6 py-6 space-y-5">
                <!-- Nombre -->
                <div>
                    <label for="form.name"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                    <input wire:model="form.name" id="form.name" type="text"
                        class="block w-full mt-1 input-style bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
                        autocomplete="off">
                    @error('form.name')
                        <span class="text-xs text-red-500 dark:text-red-400">{{ $message }}</span>
                    @enderror
                </div>
                <!-- Email -->
                <div>
                    <label for="form.email"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input wire:model="form.email" id="form.email" type="email"
                        class="block w-full mt-1 input-style bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
                        wire:key="form-email-input" autocomplete="off">
                    @error('form.email')
                        <span class="text-xs text-red-500 dark:text-red-400">{{ $message }}</span>
                    @enderror
                </div>
                <!-- Contraseña -->
                <div>
                    <label for="form.password"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contraseña</label>
                    <input wire:model="form.password" id="form.password" type="password"
                        class="block w-full mt-1 input-style bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"
                        placeholder="{{ $isEditing ? 'Dejar en blanco para no cambiar' : '' }}">
                    @error('form.password')
                        <span class="text-xs text-red-500 dark:text-red-400">{{ $message }}</span>
                    @enderror
                </div>
                <!-- Confirmar Contraseña -->
                <div>
                    <label for="form.password_confirmation"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirmar Contraseña</label>
                    <input wire:model="form.password_confirmation" id="form.password_confirmation" type="password"
                        class="block w-full mt-1 input-style bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500">
                </div>
                <!-- Roles -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Roles</label>
                    <div class="mt-2 grid grid-cols-2 gap-3">
                        @foreach ($allRoles as $role)
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="form.userRoles" value="{{ $role }}"
                                    class="rounded text-primary focus:ring-primary border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $role }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('form.userRoles')
                        <span class="text-xs text-red-500 dark:text-red-400">{{ $message }}</span>
                    @enderror
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="show = false" class="btn-secondary transition">Cancelar</button>
                    <button type="submit" class="btn-primary transition flex items-center gap-2">
                        <i class="bi bi-arrow-right-circle"></i> Siguiente
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
