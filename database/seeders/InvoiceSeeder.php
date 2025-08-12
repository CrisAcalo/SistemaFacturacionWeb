<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuarios existentes para asignar como vendedores y clientes
        $users = User::all();

        if ($users->count() < 2) {
            $this->command->error('Se necesitan al menos 2 usuarios para crear facturas. Ejecuta UserSeeder primero.');
            return;
        }

        // Obtener productos existentes
        $products = Product::all();

        if ($products->count() < 1) {
            $this->command->error('Se necesitan productos para crear facturas. Ejecuta el ProductFactory primero.');
            return;
        }

        $this->command->info('Creando 30 facturas con sus items...');

        // Crear 30 facturas
        for ($i = 1; $i <= 30; $i++) {
            // Seleccionar vendedor y cliente aleatorios (diferentes)
            $seller = $users->random();
            $client = $users->where('id', '!=', $seller->id)->random();

            // Crear la factura
            $invoice = Invoice::create([
                'invoice_number' => 'INV-' . str_pad($i, 6, '0', STR_PAD_LEFT), // INV-000001, INV-000002, etc.
                'user_id' => $seller->id,
                'client_id' => $client->id,
                'subtotal' => 0, // Se calculará después
                'tax' => 0, // Se calculará después
                'total' => 0, // Se calculará después
                'status' => collect(['Pagada', 'Pendiente', 'Anulada'])->random(),
                'notes' => fake()->optional(0.3)->sentence(), // 30% de probabilidad de tener notas
                'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
            ]);

            // Crear entre 1 y 5 items por factura
            $numItems = rand(1, 5);
            $subtotal = 0;

            for ($j = 1; $j <= $numItems; $j++) {
                $product = $products->random();
                $quantity = rand(1, 10);
                $price = $product->price; // Usar el precio actual del producto
                $itemTotal = $quantity * $price;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $itemTotal,
                ]);

                $subtotal += $itemTotal;
            }

            // Calcular impuesto (12% por ejemplo)
            $tax = $subtotal * 0.12;
            $total = $subtotal + $tax;

            // Actualizar la factura con los totales calculados
            $invoice->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);

            $this->command->info("Factura {$invoice->invoice_number} creada con {$numItems} items - Total: $" . number_format($total, 2));
        }

        $this->command->info('¡30 facturas creadas exitosamente!');
    }
}
