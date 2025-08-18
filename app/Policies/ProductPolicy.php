<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'products.view',
            'products.manage',
            'admin.full'
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product = null): bool
    {
        return $user->hasAnyPermission([
            'products.view',
            'products.manage',
            'admin.full'
        ]);
    }

    /**
     * Determine whether the user can view deleted models.
     */
    public function viewDeleted(User $user): bool
    {
        return $user->hasAnyPermission([
            'products.manage',
            'admin.full'
        ]);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission([
            'products.create',
            'products.manage',
            'admin.full'
        ]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->hasAnyPermission([
            'products.edit',
            'products.manage',
            'admin.full'
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->hasAnyPermission([
            'products.delete',
            'products.manage',
            'admin.full'
        ]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        return $user->hasAnyPermission([
            'products.restore',
            'products.manage',
            'admin.full'
        ]);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return $user->hasAnyPermission(['admin.full']);
    }

    /**
     * Determine whether the user can update stock.
     */
    public function updateStock(User $user, Product $product): bool
    {
        return $user->hasAnyPermission([
            'products.stock',
            'products.manage',
            'admin.full'
        ]);
    }

    /**
     * Determine whether the user can bulk update products.
     */
    public function bulkUpdate(User $user): bool
    {
        return $user->hasAnyPermission([
            'products.bulk',
            'products.manage',
            'admin.full'
        ]);
    }

    /**
     * Determine whether the user can view low stock products.
     */
    public function viewLowStock(User $user): bool
    {
        return $user->hasAnyPermission([
            'products.view',
            'products.stock',
            'products.manage',
            'admin.full'
        ]);
    }
}
