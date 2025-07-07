<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'product_id',
        'quantity',
        'price',
        'total',
    ];

    // Un item de factura pertenece a una factura
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // Un item de factura pertenece a un producto
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
