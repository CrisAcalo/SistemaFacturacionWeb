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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Relación con la factura
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');

            // Cliente que registra el pago (debe coincidir con el client_id de la factura)
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');

            // Datos del pago
            $table->enum('payment_type', ['efectivo', 'tarjeta', 'transferencia', 'cheque']);
            $table->string('transaction_number')->nullable(); // Número de transacción o comprobante
            $table->decimal('amount', 10, 2); // Monto pagado
            $table->text('observations')->nullable(); // Observaciones opcionales

            // Estado del pago
            $table->enum('status', ['pendiente', 'validado', 'rechazado'])->default('pendiente');

            // Campos de auditoría
            $table->timestamp('validated_at')->nullable(); // Cuándo fue validado
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null'); // Quién lo validó
            $table->text('validation_notes')->nullable(); // Notas de validación

            $table->timestamps();

            // Índices
            $table->index(['invoice_id', 'status']);
            $table->index(['client_id', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
