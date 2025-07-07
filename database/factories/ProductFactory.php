<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generar un nombre de producto un poco más realista
        $productAdjective = fake()->randomElement(['Ergonómico', 'Inteligente', 'De Acero', 'Compacto', 'Premium', 'Reciclado']);
        $productNoun = fake()->randomElement(['Teclado', 'Ratón', 'Monitor', 'Soporte', 'Cable', 'Adaptador', 'Hub USB-C']);
        $productName = "{$productAdjective} {$productNoun}";

        // Generar un SKU basado en el nombre
        $baseSku = strtoupper(substr(preg_replace('/[^A-Z]/', '', $productNoun), 0, 3));
        $sku = $baseSku . '-' . fake()->unique()->numberBetween(1000, 9999);

        return [
            'sku' => $sku,
            'barcode' => fake()->unique()->ean13(),
            'name' => $productName,
            'description' => fake()->sentence(15),
            'stock' => fake()->numberBetween(0, 200),
            'price' => fake()->randomFloat(2, 5, 500), // Precio entre $5.00 y $500.00
        ];
    }
}
