<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    use LogsActivity;

    protected $fillable = [
        'invoice_id',
        'client_id',
        'payment_type',
        'transaction_number',
        'amount',
        'observations',
        'status',
        'validated_at',
        'validated_by',
        'validation_notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'validated_at' => 'datetime',
    ];

    // Configuración para redondeo automático
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            $payment->amount = round($payment->amount, 2);
        });

        static::updating(function ($payment) {
            if ($payment->isDirty('amount')) {
                $payment->amount = round($payment->amount, 2);
            }
        });
    }

    // Relaciones
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'invoice_id',
                'payment_type',
                'transaction_number',
                'amount',
                'status',
                'validated_at',
                'validation_notes'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pendiente');
    }

    public function scopeValidated($query)
    {
        return $query->where('status', 'validado');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rechazado');
    }

    // Métodos de utilidad
    public function isPending(): bool
    {
        return $this->status === 'pendiente';
    }

    public function isValidated(): bool
    {
        return $this->status === 'validado';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rechazado';
    }

    public function approve(User $validator, ?string $notes = null): bool
    {
        $updated = $this->update([
            'status' => 'validado',
            'validated_at' => now(),
            'validated_by' => $validator->id,
            'validation_notes' => $notes,
        ]);

        if ($updated) {
            $this->updateInvoiceStatus();
        }

        return $updated;
    }

    public function reject(User $validator, ?string $notes = null): bool
    {
        $updated = $this->update([
            'status' => 'rechazado',
            'validated_at' => now(),
            'validated_by' => $validator->id,
            'validation_notes' => $notes,
        ]);

        if ($updated) {
            $this->updateInvoiceStatus();
        }

        return $updated;
    }

    /**
     * Actualiza el estado de la factura basado en los pagos
     */
    private function updateInvoiceStatus(): void
    {
        $invoice = $this->invoice;

        // Calcular total de pagos validados
        $totalPaid = $invoice->payments()
            ->where('status', 'validado')
            ->sum('amount');

        // Redondear para evitar problemas de precisión
        $totalPaid = round($totalPaid, 2);
        $invoiceTotal = round($invoice->total, 2);

        // Log para debug (se puede comentar en producción)
        Log::info("Invoice #{$invoice->invoice_number}: Total={$invoiceTotal}, Paid={$totalPaid}");

        // Determinar el nuevo estado de la factura
        // Usar bccomp para comparación precisa de decimales
        if (bccomp($totalPaid, $invoiceTotal, 2) >= 0) {
            // Factura completamente pagada
            $invoice->update(['status' => 'Pagada']);
            Log::info("Invoice #{$invoice->invoice_number} marked as PAID");
        } else {
            // Verificar si hay pagos pendientes o solo rechazados
            $hasPendingPayments = $invoice->payments()
                ->where('status', 'pendiente')
                ->exists();

            if ($hasPendingPayments || $totalPaid > 0) {
                // Hay pagos pendientes o pagos parciales
                $invoice->update(['status' => 'Pendiente']);
                Log::info("Invoice #{$invoice->invoice_number} remains PENDING");
            }
        }
    }
}
