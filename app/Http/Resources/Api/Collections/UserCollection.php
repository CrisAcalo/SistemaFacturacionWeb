<?php

namespace App\Http\Resources\Api\Collections;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\Api\UserResource;

class UserCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = UserResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'pagination' => [
                'current_page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'last_page' => $this->lastPage(),
                'from' => $this->firstItem(),
                'to' => $this->lastItem(),
                'has_more_pages' => $this->hasMorePages(),
            ],
            'filters' => [
                'search' => $request->get('search'),
                'status' => $request->get('status'),
                'role' => $request->get('role'),
                'sort_by' => $request->get('sort_by', 'created_at'),
                'sort_direction' => $request->get('sort_direction', 'desc'),
                'include_deleted' => $request->boolean('include_deleted'),
            ],
            'meta' => [
                'total_active' => \App\Models\User::where('status', 'active')->count(),
                'total_inactive' => \App\Models\User::where('status', 'inactive')->count(),
                'total_with_trashed' => \App\Models\User::withTrashed()->count(),
                'generated_at' => now()->toISOString(),
            ]
        ];
    }
}
