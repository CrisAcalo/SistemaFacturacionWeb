<?php

namespace App\Http\Requests\Api\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // La autorización se maneja en el controlador
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->route('user') ? $this->route('user')->id : null;

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^[\pL\s\-]+$/u' // Solo letras, espacios y guiones
            ],
            'email' => [
                'sometimes',
                'string',
                'max:255',
                'unique:users,email,' . $userId
            ],
            'password' => [
                'sometimes',
                'string',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
            'password_confirmation' => [
                'required_with:password',
                'string'
            ],
            'status' => [
                'sometimes',
                'in:active,inactive'
            ],
            'roles' => [
                'sometimes',
                'array'
            ],
            'roles.*' => [
                'string',
                'exists:roles,name'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'El nombre solo puede contener letras, espacios y guiones.',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'password_confirmation.required_with' => 'La confirmación de contraseña es obligatoria cuando se actualiza la contraseña.',
            'status.in' => 'El estado debe ser activo o inactivo.',
            'roles.*.exists' => 'Uno o más roles especificados no existen.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
