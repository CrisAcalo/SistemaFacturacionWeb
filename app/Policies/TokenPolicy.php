<?php

namespace App\Policies;

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TokenPolicy
{
    /**
     * Determine whether the user can view their own tokens.
     */
    public function viewOwnTokens(User $user): bool
    {
        return $user->hasAnyPermission([
            'tokens.view',
            'tokens.manage',
            'admin.full'
        ]);
    }

    /**
     * Determine whether the user can view a specific token.
     */
    public function viewOwnToken(User $user, PersonalAccessToken $token): bool
    {
        // Users can only view their own tokens
        if ($token->tokenable_id !== $user->id || $token->tokenable_type !== get_class($user)) {
            return false;
        }

        return $user->hasAnyPermission([
            'tokens.view',
            'tokens.manage',
            'admin.full'
        ]);
    }

    /**
     * Determine whether the user can create tokens.
     */
    public function createToken(User $user): bool
    {
        return $user->hasAnyPermission([
            'tokens.create',
            'tokens.manage',
            'admin.full'
        ]);
    }

    /**
     * Determine whether the user can revoke their own tokens.
     */
    public function revokeOwnToken(User $user, PersonalAccessToken $token): bool
    {
        // Users can only revoke their own tokens
        if ($token->tokenable_id !== $user->id || $token->tokenable_type !== get_class($user)) {
            return false;
        }

        return $user->hasAnyPermission([
            'tokens.revoke',
            'tokens.manage',
            'admin.full'
        ]);
    }

    /**
     * Determine whether the user can update status of their own tokens.
     */
    public function updateOwnTokenStatus(User $user, PersonalAccessToken $token): bool
    {
        // Users can only update their own tokens
        if ($token->tokenable_id !== $user->id || $token->tokenable_type !== get_class($user)) {
            return false;
        }

        return $user->hasAnyPermission([
            'tokens.status',
            'tokens.manage',
            'admin.full'
        ]);
    }

    /**
     * Determine whether the user can view token audit trail.
     */
    public function viewTokenAudit(User $user): bool
    {
        return $user->hasAnyPermission([
            'tokens.audit',
            'tokens.manage',
            'admin.full'
        ]);
    }

    /**
     * Determine whether the user can view all tokens (admin function).
     */
    public function viewAllTokens(User $user): bool
    {
        return $user->hasAnyPermission([
            'tokens.manage',
            'admin.full'
        ]);
    }

    /**
     * Determine whether the user can revoke any token (admin function).
     */
    public function revokeAnyToken(User $user): bool
    {
        return $user->hasAnyPermission([
            'tokens.manage',
            'admin.full'
        ]);
    }
}
