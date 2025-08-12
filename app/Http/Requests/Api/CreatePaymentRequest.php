<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CreatePaymentRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invoice_id' => [
                'required',
                'string',
                'exists:invoices,invoice_number',
            ],
            'payment_type' => [
                'required',
                'string',
                Rule::in(['efectivo', 'tarjeta', 'transferencia', 'cheque'])
            ],
            'transaction_number' => [
                'nullable',
                'string',
                'max:255',
                'required_unless:payment_type,efectivo', // Requerido excepto para efectivo
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99'
            ],
            'observations' => [
                'nullable',
                'string',
                'max:1000'
            ]
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'invoice_id.required' => 'El ID de la factura es requerido.',
            'invoice_id.exists' => 'La factura especificada no existe.',
            'payment_type.required' => 'El tipo de pago es requerido.',
            'payment_type.in' => 'El tipo de pago debe ser: efectivo, tarjeta, transferencia o cheque.',
            'transaction_number.required_unless' => 'El número de transacción es requerido para este tipo de pago.',
            'transaction_number.max' => 'El número de transacción no puede tener más de 255 caracteres.',
            'amount.required' => 'El monto del pago es requerido.',
            'amount.numeric' => 'El monto debe ser un número válido.',
            'amount.min' => 'El monto mínimo es $0.01.',
            'amount.max' => 'El monto máximo es $999,999.99.',
            'observations.max' => 'Las observaciones no pueden tener más de 1000 caracteres.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'invoice_id' => 'ID de factura',
            'payment_type' => 'tipo de pago',
            'transaction_number' => 'número de transacción',
            'amount' => 'monto',
            'observations' => 'observaciones',
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
                'message' => 'Datos de pago inválidos',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}

/*
Ejemplo de JSON válido para CreatePaymentRequest:

{
    "invoice_id": 123,
    "payment_type": "tarjeta",
    "transaction_number": "ABC123456789",
    "amount": 1500.50,
    "observations": "Pago realizado con tarjeta de crédito."
}
*/
