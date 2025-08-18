<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // --- Permisos de Usuarios ---
        $manageUsersPermission = Permission::firstOrCreate(['name' => 'manage users']);
        $adminRole = Role::firstOrCreate(['name' => 'Administrador']);
        $adminRole->givePermissionTo($manageUsersPermission);
        $secretarioRole = Role::firstOrCreate(['name' => 'Secretario']);
        $secretarioRole->givePermissionTo($manageUsersPermission);

        // --- Permisos de Productos ---
        $viewProductsPermission = Permission::firstOrCreate(['name' => 'products.view']);
        $createProductsPermission = Permission::firstOrCreate(['name' => 'products.create']);
        $editProductsPermission = Permission::firstOrCreate(['name' => 'products.edit']);
        $deleteProductsPermission = Permission::firstOrCreate(['name' => 'products.delete']);
        $restoreProductsPermission = Permission::firstOrCreate(['name' => 'products.restore']);
        $stockProductsPermission = Permission::firstOrCreate(['name' => 'products.stock']);
        $bulkProductsPermission = Permission::firstOrCreate(['name' => 'products.bulk']);
        $manageProductsPermission = Permission::firstOrCreate(['name' => 'products.manage']);

        $bodegaRole = Role::firstOrCreate(['name' => 'Bodega']);
        $bodegaRole->givePermissionTo([
            $viewProductsPermission,
            $createProductsPermission,
            $editProductsPermission,
            $deleteProductsPermission,
            $restoreProductsPermission,
            $stockProductsPermission,
            $bulkProductsPermission,
            $manageProductsPermission
        ]);

        // --- Permisos de Facturación ---
        $viewInvoicesPermission = Permission::firstOrCreate(['name' => 'invoices.view']);
        $createInvoicesPermission = Permission::firstOrCreate(['name' => 'invoices.create']);
        $editInvoicesPermission = Permission::firstOrCreate(['name' => 'invoices.edit']);
        $deleteInvoicesPermission = Permission::firstOrCreate(['name' => 'invoices.delete']);
        $restoreInvoicesPermission = Permission::firstOrCreate(['name' => 'invoices.restore']);
        $statusInvoicesPermission = Permission::firstOrCreate(['name' => 'invoices.status']);
        $statisticsInvoicesPermission = Permission::firstOrCreate(['name' => 'invoices.statistics']);
        $manageInvoicesPermission = Permission::firstOrCreate(['name' => 'invoices.manage']);

        $ventasRole = Role::firstOrCreate(['name' => 'Ventas']);
        $ventasRole->givePermissionTo([
            $viewInvoicesPermission,
            $createInvoicesPermission,
            $editInvoicesPermission,
            $deleteInvoicesPermission,
            $restoreInvoicesPermission,
            $statusInvoicesPermission,
            $statisticsInvoicesPermission,
            $manageInvoicesPermission
        ]);

        // --- Permisos de Pagos ---
        $managePaymentsPermission = Permission::firstOrCreate(['name' => 'manage payments']);
        $pagosRole = Role::firstOrCreate(['name' => 'Pagos']);
        $pagosRole->givePermissionTo($managePaymentsPermission);

        // --- Permisos de Auditoría ---
        $viewAuditsPermission = Permission::firstOrCreate(['name' => 'view audits']);

        // --- Permisos de Tokens API ---
        $viewTokensPermission = Permission::firstOrCreate(['name' => 'tokens.view']);
        $createTokensPermission = Permission::firstOrCreate(['name' => 'tokens.create']);
        $revokeTokensPermission = Permission::firstOrCreate(['name' => 'tokens.revoke']);
        $statusTokensPermission = Permission::firstOrCreate(['name' => 'tokens.status']);
        $auditTokensPermission = Permission::firstOrCreate(['name' => 'tokens.audit']);
        $manageTokensPermission = Permission::firstOrCreate(['name' => 'tokens.manage']);

        // --- Rol Cliente (para usuarios que se registran vía API) ---
        $clienteRole = Role::firstOrCreate(['name' => 'Cliente']);

        // Todos los usuarios autenticados pueden gestionar sus propios tokens
        $clienteRole->givePermissionTo([
            $viewTokensPermission,
            $createTokensPermission,
            $revokeTokensPermission,
            $statusTokensPermission,
            $auditTokensPermission
        ]);

        $ventasRole->givePermissionTo([
            $viewTokensPermission,
            $createTokensPermission,
            $revokeTokensPermission,
            $statusTokensPermission,
            $auditTokensPermission
        ]);

        $bodegaRole->givePermissionTo([
            $viewTokensPermission,
            $createTokensPermission,
            $revokeTokensPermission,
            $statusTokensPermission,
            $auditTokensPermission
        ]);

        $pagosRole->givePermissionTo([
            $viewTokensPermission,
            $createTokensPermission,
            $revokeTokensPermission,
            $statusTokensPermission,
            $auditTokensPermission
        ]);

        // Asignar el nuevo permiso solo al Administrador
        $adminRole = Role::where('name', 'Administrador')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($viewAuditsPermission);
            $adminRole->givePermissionTo($manageTokensPermission);
        }
                // El Admin debería poder hacer todo
        $adminRole->givePermissionTo($manageProductsPermission);
        $adminRole->givePermissionTo($manageInvoicesPermission);
        $adminRole->givePermissionTo($managePaymentsPermission);
    }
}
