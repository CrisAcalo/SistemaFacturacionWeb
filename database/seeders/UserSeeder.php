<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Administrador
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('Administrador');

        // Usuario Secretario
        $secretario = User::create([
            'name' => 'Secretario User',
            'email' => 'secretario@example.com',
            'password' => Hash::make('password'),
        ]);
        $secretario->assignRole('Secretario');

        // Usuario Bodega
        $bodega = User::create([
            'name' => 'Bodega User',
            'email' => 'bodega@example.com',
            'password' => Hash::make('password'),
        ]);
        $bodega->assignRole('Bodega');

        // Usuario Ventas
        $ventas = User::create([
            'name' => 'Ventas User',
            'email' => 'ventas@example.com',
            'password' => Hash::make('password'),
        ]);
        $ventas->assignRole('Ventas');

        // Usuario Pagos
        $pagos = User::create([
            'name' => 'Pagos User',
            'email' => 'pagos@example.com',
            'password' => Hash::make('password'),
        ]);
        $pagos->assignRole('Pagos');
    }
}
