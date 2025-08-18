<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TokenResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'abilities' => $this->abilities,
            'is_active' => $this->is_active,
            'status' => $this->getTokenStatus(),
            'metadata' => $this->metadata,
            'created_by_role' => $this->created_by_role,
            'usage_stats' => [
                'last_used_at' => $this->last_used_at?->toISOString(),
                'days_since_last_use' => $this->last_used_at ? $this->last_used_at->diffInDays(now()) : null,
                'total_uses' => $this->getUsageCount(),
            ],
            'expiration' => [
                'expires_at' => $this->expires_at?->toISOString(),
                'is_expired' => $this->expires_at ? $this->expires_at <= now() : false,
                'expires_in_days' => $this->expires_at ? max(0, now()->diffInDays($this->expires_at, false)) : null,
            ],
            'security' => [
                'token_hash' => substr($this->token, 0, 8) . '...' . substr($this->token, -8),
                'is_secure' => $this->isSecure(),
                'created_from_role' => $this->created_by_role,
            ],
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Get the status of the token
     */
    private function getTokenStatus(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if ($this->expires_at && $this->expires_at <= now()) {
            return 'expired';
        }

        return 'active';
    }

    /**
     * Get usage count (simplified estimation)
     */
    private function getUsageCount(): int
    {
        // This is a simplified count - in a real app you might track this differently
        return $this->last_used_at ?
            max(1, $this->created_at->diffInDays($this->last_used_at) + 1) : 0;
    }

    /**
     * Check if token is considered secure
     */
    private function isSecure(): bool
    {
        // Basic security checks
        $hasExpiration = $this->expires_at !== null;
        $hasLimitedAbilities = !in_array('*', $this->abilities ?? ['*']);
        $hasDescription = !empty($this->description);

        return $hasExpiration && $hasLimitedAbilities && $hasDescription;
    }
}
