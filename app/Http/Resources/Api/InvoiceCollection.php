<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class InvoiceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'invoices' => $this->collection,
            'stats' => [
                'total_invoices' => $this->collection->count(),
                'total_amount' => $this->collection->sum('total'),
                'average_amount' => $this->collection->avg('total'),
                'by_status' => [
                    'paid' => $this->collection->where('status', 'Pagada')->count(),
                    'pending' => $this->collection->where('status', 'Pendiente')->count(),
                    'cancelled' => $this->collection->where('status', 'Anulada')->count(),
                ],
                'amounts_by_status' => [
                    'paid' => $this->collection->where('status', 'Pagada')->sum('total'),
                    'pending' => $this->collection->where('status', 'Pendiente')->sum('total'),
                    'cancelled' => $this->collection->where('status', 'Anulada')->sum('total'),
                ],
                'total_items' => $this->collection->sum(function ($invoice) {
                    return $invoice->items ? $invoice->items->count() : 0;
                })
            ]
        ];
    }
}
