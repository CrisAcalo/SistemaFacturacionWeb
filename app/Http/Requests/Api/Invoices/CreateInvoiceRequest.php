<?php

namespace App\Http\Requests\Api\Invoices;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateInvoiceRequest extends FormRequest
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
            'client_id' => [
                'required',
                'integer',
                'exists:users,id'
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in(['Pagada', 'Pendiente', 'Anulada'])
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'tax_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:1'
            ],
            'items' => [
                'required',
                'array',
                'min:1'
            ],
            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id'
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                'max:99999'
            ],
            'items.*.price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99'
            ]
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'client_id.required' => 'El cliente es obligatorio',
            'client_id.exists' => 'El cliente seleccionado no existe',

            'status.in' => 'El estado debe ser: Pagada, Pendiente o Anulada',

            'notes.max' => 'Las notas no pueden tener más de 1000 caracteres',

            'tax_rate.numeric' => 'La tasa de impuesto debe ser un número válido',
            'tax_rate.min' => 'La tasa de impuesto no puede ser negativa',
            'tax_rate.max' => 'La tasa de impuesto no puede ser mayor al 100%',

            'items.required' => 'Debe incluir al menos un producto',
            'items.min' => 'Debe incluir al menos un producto',

            'items.*.product_id.required' => 'El producto es obligatorio',
            'items.*.product_id.exists' => 'El producto seleccionado no existe',

            'items.*.quantity.required' => 'La cantidad es obligatoria',
            'items.*.quantity.integer' => 'La cantidad debe ser un número entero',
            'items.*.quantity.min' => 'La cantidad debe ser mayor a 0',
            'items.*.quantity.max' => 'La cantidad no puede ser mayor a 99,999',

            'items.*.price.required' => 'El precio es obligatorio',
            'items.*.price.numeric' => 'El precio debe ser un número válido',
            'items.*.price.min' => 'El precio no puede ser negativo',
            'items.*.price.max' => 'El precio no puede ser mayor a 999,999.99'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'client_id' => 'cliente',
            'status' => 'estado',
            'notes' => 'notas',
            'tax_rate' => 'tasa de impuesto',
            'items' => 'productos',
            'items.*.product_id' => 'producto',
            'items.*.quantity' => 'cantidad',
            'items.*.price' => 'precio'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('items')) {
                foreach ($this->get('items') as $index => $item) {
                    // Validate that product exists and is not deleted
                    if (isset($item['product_id'])) {
                        $product = \App\Models\Product::find($item['product_id']);
                        if ($product && $product->trashed()) {
                            $validator->errors()->add("items.{$index}.product_id", 'El producto seleccionado está eliminado');
                        }
                    }
                }
            }
        });
    }
}
