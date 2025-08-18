<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'products' => $this->collection,
            'stats' => [
                'total_products' => $this->collection->count(),
                'in_stock' => $this->collection->where('stock', '>', 0)->count(),
                'out_of_stock' => $this->collection->where('stock', '<=', 0)->count(),
                'low_stock' => $this->collection->where('stock', '>', 0)->where('stock', '<=', 10)->count(),
                'average_price' => $this->collection->avg('price'),
                'total_inventory_value' => $this->collection->sum(function ($product) {
                    return $product->stock * $product->price;
                })
            ]
        ];
    }
}
