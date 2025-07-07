<?php

namespace App\Livewire\Forms\Users;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Form;

class UserFormObject extends Form
{
    // Propiedad para mantener el usuario que se está editando
    public ?User $editingUser = null;

    // Propiedades que se vincularán (wire:model) a los campos del formulario
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $userRoles = [];

    /**
     * Define las reglas de validación para el formulario.
     */
    public function rules(): array
    {
        // Obtiene el ID del usuario si estamos en modo de edición, para ignorarlo en la regla 'unique'
        $userId = $this->editingUser?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'password' => [
                // La contraseña es requerida solo al crear, opcional al editar
                $userId ? 'nullable' : 'required',
                'string',
                'confirmed', // Requiere que exista un campo 'password_confirmation' con el mismo valor
                Password::min(8)->mixedCase()->numbers()->symbols() // Reglas de contraseña robustas
            ],
            'userRoles' => ['required', 'array', 'min:1'],
            'userRoles.*' => ['exists:roles,name'], // Valida que cada rol en el array exista en la BD
        ];
    }

    /**
     * Rellena el formulario con los datos de un usuario existente.
     * Se llama cuando se entra en modo de edición.
     */
    public function setUser(?User $user)
    {
        $this->editingUser = $user;

        // Si se proporciona un usuario, llenamos las propiedades del formulario
        if ($user) {
            $this->name = $user->name;
            $this->email = $user->email;
            $this->userRoles = $user->roles->pluck('name')->all();
        }
    }

    // Livewire maneja el método reset() automáticamente para limpiar las propiedades públicas.
    // No necesitamos definirlo explícitamente a menos que queramos un comportamiento personalizado.
}
