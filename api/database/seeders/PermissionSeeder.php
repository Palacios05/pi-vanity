<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'api';

        // Permisos de las Policies: list_, register_, edit_, delete_
        $modules = ['client', 'product', 'purchase', 'role', 'sale', 'user'];
        $actions = ['list', 'register', 'edit', 'delete'];

        $names = [];
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $names[] = "{$action}_{$module}";
            }
        }

        // Permisos de los middlewares en routes/api.php
        $names = array_merge($names, [
            'settings',
            'show_inventory_product',
            'show_wallet_price_product',
            'return',
            'conversions',
            'kardex',
            'dashboard',
        ]);

        foreach ($names as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
        }

        // Asignar todos al rol Admin
        $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => $guard]);
        $role->syncPermissions(Permission::where('guard_name', $guard)->get());

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}