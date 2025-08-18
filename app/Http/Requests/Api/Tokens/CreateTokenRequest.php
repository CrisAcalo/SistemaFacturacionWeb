<?php

namespace App\Http\Requests\Api\Tokens;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by Gates in the controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:3'
            ],
            'description' => [
                'nullable',
                'string',
                'max:500'
            ],
            'abilities' => [
                'nullable',
                'array'
            ],
            'abilities.*' => [
                'string',
                'max:100'
            ],
            'expires_at' => [
                'nullable',
                'date',
                'after:now'
            ],
            'is_active' => [
                'nullable',
                'boolean'
            ],
            'metadata' => [
                'nullable',
                'array'
            ]
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del token es obligatorio',
            'name.min' => 'El nombre debe tener al menos 3 caracteres',
            'name.max' => 'El nombre no puede tener más de 255 caracteres',

            'description.max' => 'La descripción no puede tener más de 500 caracteres',

            'abilities.array' => 'Las habilidades deben ser un array',
            'abilities.*.string' => 'Cada habilidad debe ser un texto',
            'abilities.*.max' => 'Cada habilidad no puede tener más de 100 caracteres',

            'expires_at.date' => 'La fecha de expiración debe ser una fecha válida',
            'expires_at.after' => 'La fecha de expiración debe ser posterior a la fecha actual',

            'is_active.boolean' => 'El estado activo debe ser verdadero o falso',

            'metadata.array' => 'Los metadatos deben ser un objeto JSON'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'abilities' => 'habilidades',
            'expires_at' => 'fecha de expiración',
            'is_active' => 'estado activo',
            'metadata' => 'metadatos'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate token name uniqueness for the user
            $user = $this->user();
            if ($user) {
                $existingToken = \App\Models\PersonalAccessToken::where('tokenable_id', $user->id)
                    ->where('tokenable_type', get_class($user))
                    ->where('name', $this->get('name'))
                    ->whereNull('deleted_at')
                    ->exists();

                if ($existingToken) {
                    $validator->errors()->add('name', 'Ya tienes un token con este nombre');
                }

                // Validate max tokens per user (limit to 10)
                $tokenCount = \App\Models\PersonalAccessToken::where('tokenable_id', $user->id)
                    ->where('tokenable_type', get_class($user))
                    ->whereNull('deleted_at')
                    ->count();

                if ($tokenCount >= 10) {
                    $validator->errors()->add('name', 'Has alcanzado el límite máximo de 10 tokens');
                }
            }

            // Validate abilities format
            if ($this->has('abilities')) {
                $abilities = $this->get('abilities');
                $validAbilities = ['*', 'read', 'write', 'delete', 'admin'];

                foreach ($abilities as $ability) {
                    if (!in_array($ability, $validAbilities) && !str_contains($ability, ':')) {
                        $validator->errors()->add('abilities', "La habilidad '{$ability}' no es válida");
                    }
                }
            }
        });
    }
}
