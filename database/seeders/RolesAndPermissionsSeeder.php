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
        $manageUsersPermission = Permission::create(['name' => 'manage users']);
        $adminRole = Role::create(['name' => 'Administrador']);
        $adminRole->givePermissionTo($manageUsersPermission);
        $secretarioRole = Role::create(['name' => 'Secretario']);
        $secretarioRole->givePermissionTo($manageUsersPermission);

        // --- Permisos de Productos ---
        $manageProductsPermission = Permission::create(['name' => 'manage products']);
        $bodegaRole = Role::create(['name' => 'Bodega']);
        $bodegaRole->givePermissionTo($manageProductsPermission);

        // --- Permisos de Facturación ---
        $manageInvoicesPermission = Permission::create(['name' => 'manage invoices']);
        $ventasRole = Role::create(['name' => 'Ventas']);
        $ventasRole->givePermissionTo($manageInvoicesPermission);
        // --- Permisos de Auditoría ---
        $viewAuditsPermission = Permission::create(['name' => 'view audits']);

        // Asignar el nuevo permiso solo al Administrador
        $adminRole = Role::where('name', 'Administrador')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($viewAuditsPermission);
        }
        // El Admin debería poder hacer todo
        $adminRole->givePermissionTo($manageProductsPermission);
        $adminRole->givePermissionTo($manageInvoicesPermission); // <-- Añadir
    }
}
