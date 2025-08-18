<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TokenCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'tokens' => $this->collection,
            'stats' => [
                'total_tokens' => $this->collection->count(),
                'active_tokens' => $this->collection->where('is_active', true)
                                                   ->filter(function ($token) {
                                                       return !$token->expires_at || $token->expires_at > now();
                                                   })->count(),
                'inactive_tokens' => $this->collection->where('is_active', false)->count(),
                'expired_tokens' => $this->collection->filter(function ($token) {
                    return $token->expires_at && $token->expires_at <= now();
                })->count(),
                'tokens_with_expiration' => $this->collection->whereNotNull('expires_at')->count(),
                'tokens_last_used_recently' => $this->collection->filter(function ($token) {
                    return $token->last_used_at && $token->last_used_at >= now()->subDays(7);
                })->count(),
                'security_summary' => [
                    'secure_tokens' => $this->collection->filter(function ($token) {
                        return $token->expires_at &&
                               !in_array('*', $token->abilities ?? ['*']) &&
                               !empty($token->description);
                    })->count(),
                    'unlimited_ability_tokens' => $this->collection->filter(function ($token) {
                        return in_array('*', $token->abilities ?? ['*']);
                    })->count(),
                    'never_expires_tokens' => $this->collection->whereNull('expires_at')->count(),
                ]
            ]
        ];
    }
}
