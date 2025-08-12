<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener facturas existentes
        $invoices = Invoice::with('client')->take(10)->get();

        if ($invoices->isEmpty()) {
            $this->command->warn('No hay facturas disponibles. Ejecuta primero InvoiceSeeder.');
            return;
        }

        $paymentTypes = ['efectivo', 'tarjeta', 'transferencia', 'cheque'];

        foreach ($invoices as $invoice) {
            // Crear 1-3 pagos por factura
            $paymentsCount = rand(1, 3);
            $totalPaid = 0;

            for ($i = 0; $i < $paymentsCount; $i++) {
                // Calcular monto del pago (no puede exceder el total de la factura)
                $remainingAmount = $invoice->total - $totalPaid;

                if ($remainingAmount <= 0) {
                    break;
                }

                // El último pago podría ser el total restante o una parte
                if ($i === $paymentsCount - 1) {
                    $paymentAmount = rand(1, 100) <= 70 ? $remainingAmount : rand(1, min($remainingAmount, 500));
                } else {
                    $paymentAmount = rand(50, min($remainingAmount, 500));
                }

                $paymentType = $paymentTypes[array_rand($paymentTypes)];
                $transactionNumber = null;

                // Generar número de transacción para tipos que no sean efectivo
                if ($paymentType !== 'efectivo') {
                    switch ($paymentType) {
                        case 'tarjeta':
                            $transactionNumber = 'CARD-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
                            break;
                        case 'transferencia':
                            $transactionNumber = 'TRX-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                            break;
                        case 'cheque':
                            $transactionNumber = 'CHQ-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
                            break;
                    }
                }

                // Estados posibles con probabilidades
                $statusOptions = [
                    'pendiente' => 50,  // 50% pendientes
                    'validado' => 35,   // 35% validados
                    'rechazado' => 15   // 15% rechazados
                ];

                $randomStatus = $this->weightedRandom($statusOptions);

                $payment = Payment::create([
                    'invoice_id' => $invoice->id,
                    'client_id' => $invoice->client_id,
                    'payment_type' => $paymentType,
                    'transaction_number' => $transactionNumber,
                    'amount' => $paymentAmount,
                    'observations' => $this->getRandomObservation($paymentType),
                    'status' => $randomStatus,
                    'created_at' => now()->subDays(rand(0, 30)),
                ]);

                // Si el pago no está pendiente, agregar datos de validación
                if ($payment->status !== 'pendiente') {
                    $validator = User::whereHas('roles', function ($query) {
                        $query->whereIn('name', ['Administrador', 'Pagos']);
                    })->inRandomOrder()->first();

                    if ($validator) {
                        $payment->update([
                            'validated_at' => now()->subDays(rand(0, 15)),
                            'validated_by' => $validator->id,
                            'validation_notes' => $this->getRandomValidationNote($payment->status),
                        ]);
                    }
                }

                $totalPaid += $paymentAmount;
            }
        }

        $this->command->info('Seeder de pagos completado. Se crearon pagos para las facturas existentes.');
    }

    /**
     * Selección aleatoria ponderada
     */
    private function weightedRandom(array $options): string
    {
        $totalWeight = array_sum($options);
        $random = rand(1, $totalWeight);

        $currentWeight = 0;
        foreach ($options as $option => $weight) {
            $currentWeight += $weight;
            if ($random <= $currentWeight) {
                return $option;
            }
        }

        return array_key_first($options);
    }

    /**
     * Obtener observación aleatoria según el tipo de pago
     */
    private function getRandomObservation(string $paymentType): ?string
    {
        $observations = [
            'efectivo' => [
                'Pago en efectivo realizado en oficina',
                'Abono parcial en efectivo',
                'Pago completo en billetes',
                null,
            ],
            'tarjeta' => [
                'Pago con tarjeta Visa terminada en 1234',
                'Transacción aprobada con Mastercard',
                'Pago procesado exitosamente',
                'Cargo a tarjeta de crédito',
                null,
            ],
            'transferencia' => [
                'Transferencia bancaria desde cuenta corriente',
                'Depósito interbancario verificado',
                'Transferencia electrónica confirmada',
                'Pago desde Banco Nacional',
                null,
            ],
            'cheque' => [
                'Cheque del Banco Central, cuenta 12345',
                'Cheque a fecha verificado',
                'Cheque nominativo depositado',
                null,
            ],
        ];

        return $observations[$paymentType][array_rand($observations[$paymentType])];
    }

    /**
     * Obtener nota de validación aleatoria
     */
    private function getRandomValidationNote(string $status): ?string
    {
        if ($status === 'validado') {
            $notes = [
                'Pago verificado y aprobado',
                'Documentación correcta, pago válido',
                'Transacción confirmada por el banco',
                'Pago procesado correctamente',
                null,
            ];
        } else {
            $notes = [
                'Número de transacción inválido',
                'El comprobante no coincide con el monto',
                'Transferencia no localizada en el sistema bancario',
                'Cheque sin fondos suficientes',
                'Documentación incompleta',
            ];
        }

        return $notes[array_rand($notes)];
    }
}
