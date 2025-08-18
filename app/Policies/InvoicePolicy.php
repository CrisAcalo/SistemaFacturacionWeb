<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InvoicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'invoices.view',
            'invoices.manage',
            'admin.full'
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invoice $invoice = null): bool
    {
        // Admin and users with full invoice permissions can view any invoice
        if ($user->hasAnyPermission(['invoices.manage', 'admin.full'])) {
            return true;
        }

        // Users can view invoices they created or invoices where they are the client
        if ($invoice && ($invoice->user_id === $user->id || $invoice->client_id === $user->id)) {
            return true;
        }

        // Users with view permission can see all invoices
        return $user->hasAnyPermission(['invoices.view']);
    }

    /**
     * Determine whether the user can view deleted models.
     */
    public function viewDeleted(User $user): bool
    {
        return $user->hasAnyPermission([
            'invoices.manage',
            'admin.full'
        ]);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission([
            'invoices.create',
            'invoices.manage',
            'admin.full'
        ]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        // Admin and users with full invoice permissions can update any invoice
        if ($user->hasAnyPermission(['invoices.manage', 'admin.full'])) {
            return true;
        }

        // Users can only edit invoices they created and only if status is Pendiente
        if ($invoice->user_id === $user->id && $invoice->status === 'Pendiente') {
            return $user->hasAnyPermission(['invoices.edit']);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        // Admin and users with full invoice permissions can delete any invoice
        if ($user->hasAnyPermission(['invoices.manage', 'admin.full'])) {
            return true;
        }

        // Users can only delete invoices they created and only if status is Pendiente and no payments
        if ($invoice->user_id === $user->id &&
            $invoice->status === 'Pendiente' &&
            !$invoice->payments()->exists()) {
            return $user->hasAnyPermission(['invoices.delete']);
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Invoice $invoice): bool
    {
        return $user->hasAnyPermission([
            'invoices.restore',
            'invoices.manage',
            'admin.full'
        ]);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Invoice $invoice): bool
    {
        return $user->hasAnyPermission(['admin.full']);
    }

    /**
     * Determine whether the user can update invoice status.
     */
    public function updateStatus(User $user, Invoice $invoice): bool
    {
        // Admin and users with full invoice permissions can update any invoice status
        if ($user->hasAnyPermission(['invoices.manage', 'admin.full'])) {
            return true;
        }

        // Users with status permission can update status
        return $user->hasAnyPermission(['invoices.status']);
    }

    /**
     * Determine whether the user can view invoice statistics.
     */
    public function viewStatistics(User $user): bool
    {
        return $user->hasAnyPermission([
            'invoices.view',
            'invoices.statistics',
            'invoices.manage',
            'admin.full'
        ]);
    }
}
