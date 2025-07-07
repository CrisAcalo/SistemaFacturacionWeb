<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            // Relación con la factura
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');

            // Relación con el producto
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');

            $table->unsignedInteger('quantity');
            $table->decimal('price', 10, 2); // Precio unitario al momento de la venta
            $table->decimal('total', 10, 2); // (quantity * price)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
