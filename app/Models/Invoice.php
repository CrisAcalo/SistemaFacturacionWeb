<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'user_id',
        'client_id',
        'subtotal',
        'tax',
        'total',
        'status',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Configuración para redondeo automático
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            $invoice->subtotal = round($invoice->subtotal, 2);
            $invoice->tax = round($invoice->tax, 2);
            $invoice->total = round($invoice->total, 2);
        });

        static::updating(function ($invoice) {
            if ($invoice->isDirty('subtotal')) {
                $invoice->subtotal = round($invoice->subtotal, 2);
            }
            if ($invoice->isDirty('tax')) {
                $invoice->tax = round($invoice->tax, 2);
            }
            if ($invoice->isDirty('total')) {
                $invoice->total = round($invoice->total, 2);
            }
        });
    }

    /**
     * Una factura pertenece a un usuario (vendedor)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Una factura pertenece a un cliente (que también es un usuario)
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Una factura tiene muchos items (productos)
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Una factura puede tener muchos pagos
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Calcular el total pagado (solo pagos validados)
     */
    public function getTotalPaidAttribute(): float
    {
        return round($this->payments()->where('status', 'validado')->sum('amount'), 2);
    }

    /**
     * Calcular el saldo pendiente
     */
    public function getPendingBalanceAttribute(): float
    {
        return round($this->total - $this->total_paid, 2);
    }

    /**
     * Verificar si la factura está completamente pagada
     */
    public function isFullyPaid(): bool
    {
        return $this->pending_balance <= 0;
    }

    /**
     * Verificar si la factura tiene pagos parciales
     */
    public function hasPartialPayments(): bool
    {
        return $this->total_paid > 0 && !$this->isFullyPaid();
    }
}
