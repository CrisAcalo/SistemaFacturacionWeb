<?php

namespace App\Livewire\Forms\Invoices;

use Livewire\Form;

class InvoiceFormObject extends Form
{
    public ?int $clientId = null;
    public array $items = [];
    public float $subtotal = 0.00;
    public float $taxAmount = 0.00;
    public float $total = 0.00;
    public string $notes = '';

    public function rules(): array
    {
        return [
            'clientId' => ['required', 'exists:users,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
