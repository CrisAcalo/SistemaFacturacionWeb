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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // Un número de factura único

            // Quién emitió la factura (Vendedor)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // A quién se le emitió la factura (Cliente)
            // Asumimos que el "Cliente" también es un registro en la tabla 'users'
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');

            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2); // Impuesto (ej: IVA)
            $table->decimal('total', 10, 2);

            $table->enum('status', ['Pagada', 'Pendiente', 'Anulada'])->default('Pendiente');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
