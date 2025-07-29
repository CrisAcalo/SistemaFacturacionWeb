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
}
