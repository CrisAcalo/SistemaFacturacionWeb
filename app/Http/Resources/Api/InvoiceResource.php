<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'client' => [
                'id' => $this->client->id,
                'name' => $this->client->name,
                'email' => $this->client->email,
            ],
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'total' => $this->total,
            'formatted_subtotal' => '$' . number_format($this->subtotal, 2),
            'formatted_tax' => '$' . number_format($this->tax, 2),
            'formatted_total' => '$' . number_format($this->total, 2),
            'status' => $this->status,
            'notes' => $this->notes,
            'items' => InvoiceItemResource::collection($this->whenLoaded('items')),
            'payments_summary' => $this->when($this->relationLoaded('payments'), function () {
                return [
                    'total_paid' => $this->total_paid,
                    'formatted_total_paid' => '$' . number_format($this->total_paid, 2),
                    'pending_balance' => $this->pending_balance,
                    'formatted_pending_balance' => '$' . number_format($this->pending_balance, 2),
                    'is_fully_paid' => $this->isFullyPaid(),
                    'has_partial_payments' => $this->hasPartialPayments(),
                    'payments_count' => $this->payments->count(),
                ];
            }),
            'is_deleted' => $this->trashed(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
