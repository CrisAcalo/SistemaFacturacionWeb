<?php

namespace App\Livewire\Forms\Shared;

use Livewire\Form;

class ConfirmationFormObject extends Form
{
    // Propiedades que se vincularán a los campos del modal de confirmación
    public string $reason = '';
    public string $password = '';
    public bool $confirm = false;

    /**
     * Define las reglas de validación para el modal de confirmación.
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:255'],
            'password' => ['required', 'current_password'], // Valida que la contraseña coincida con la del usuario autenticado
            'confirm' => ['accepted'], // Valida que el checkbox haya sido marcado
        ];
    }
}
