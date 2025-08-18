<?php

namespace App\Http\Requests\Api\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProductRequest extends FormRequest
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
                'min:2'
            ],
            'sku' => [
                'required',
                'string',
                'max:100',
                'unique:products,sku'
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:255',
                'unique:products,barcode'
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'stock' => [
                'required',
                'integer',
                'min:0',
                'max:999999'
            ],
            'price' => [
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
            'name.required' => 'El nombre del producto es obligatorio',
            'name.min' => 'El nombre debe tener al menos 2 caracteres',
            'name.max' => 'El nombre no puede tener más de 255 caracteres',

            'sku.required' => 'El SKU es obligatorio',
            'sku.unique' => 'Ya existe un producto con este SKU',
            'sku.max' => 'El SKU no puede tener más de 100 caracteres',

            'barcode.unique' => 'Ya existe un producto con este código de barras',
            'barcode.max' => 'El código de barras no puede tener más de 255 caracteres',

            'description.max' => 'La descripción no puede tener más de 1000 caracteres',

            'stock.required' => 'El stock es obligatorio',
            'stock.integer' => 'El stock debe ser un número entero',
            'stock.min' => 'El stock no puede ser negativo',
            'stock.max' => 'El stock no puede ser mayor a 999,999',

            'price.required' => 'El precio es obligatorio',
            'price.numeric' => 'El precio debe ser un número válido',
            'price.min' => 'El precio no puede ser negativo',
            'price.max' => 'El precio no puede ser mayor a 999,999.99'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'sku' => 'SKU',
            'barcode' => 'código de barras',
            'description' => 'descripción',
            'stock' => 'stock',
            'price' => 'precio'
        ];
    }
}
