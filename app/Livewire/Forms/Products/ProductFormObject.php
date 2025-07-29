<?php

namespace App\Livewire\Forms\Products;

use App\Models\Product;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ProductFormObject extends Form
{
    public ?Product $editingProduct = null;

    // --- NUEVAS PROPIEDADES AÑADIDAS ---
    public string $sku = '';
    public string $barcode = '';

    // Propiedades existentes
    public string $name = '';
    public string $description = '';
    public int $stock = 0;
    public string $price = '0.00';

    /**
     * Define las reglas de validación para el formulario de producto.
     */
    public function rules(): array
    {
        $productId = $this->editingProduct?->id;

        return [
            // El SKU debe ser único, ignorando el producto actual al editar.
            'sku' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($productId)],
            'barcode' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'stock' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'], // Valida formato de precio
        ];
    }

    /**
     * Rellena el formulario con los datos de un producto existente.
     */
    public function setProduct(?Product $product)
    {
        $this->editingProduct = $product;

        if ($product) {
            $this->sku = $product->sku;
            $this->barcode = $product->barcode;
            $this->name = $product->name;
            $this->description = $product->description;
            $this->stock = $product->stock;
            // Formateamos el precio para asegurar que tenga 2 decimales en el input
            $this->price = number_format((float) $product->price, 2, '.', '');
        }
    }
}
