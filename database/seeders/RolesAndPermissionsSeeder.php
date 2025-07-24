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
        $manageProductsPermission = Permission::firstOrCreate(['name' => 'manage products']);
        $bodegaRole = Role::firstOrCreate(['name' => 'Bodega']);
        $bodegaRole->givePermissionTo($manageProductsPermission);

        // --- Permisos de Facturación ---
        $manageInvoicesPermission = Permission::firstOrCreate(['name' => 'manage invoices']);
        $ventasRole = Role::firstOrCreate(['name' => 'Ventas']);
        $ventasRole->givePermissionTo($manageInvoicesPermission);

        // --- Permisos de Auditoría ---
        $viewAuditsPermission = Permission::firstOrCreate(['name' => 'view audits']);

        // --- Permisos de Tokens API ---
        $manageTokensPermission = Permission::firstOrCreate(['name' => 'manage tokens']);

        // Asignar el nuevo permiso solo al Administrador
        $adminRole = Role::where('name', 'Administrador')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($viewAuditsPermission);
            $adminRole->givePermissionTo($manageTokensPermission);
        }
        // El Admin debería poder hacer todo
        $adminRole->givePermissionTo($manageProductsPermission);
        $adminRole->givePermissionTo($manageInvoicesPermission); // <-- Añadir
    }
}
