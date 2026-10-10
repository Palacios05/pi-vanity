<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Sucursal principal (DB::table evita problemas con $fillable)
        $sucursale = DB::table('sucursales')->where('name', 'Principal')->first();
        $sucursaleId = $sucursale
            ? $sucursale->id
            : DB::table('sucursales')->insertGetId([
                'name'       => 'Principal',
                'address'    => 'Sede principal',
                'state'      => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        // Rol admin (el guard debe coincidir con el de tu config/auth.php)
        $role = Role::firstOrCreate([
            'name'       => 'Admin',
            'guard_name' => 'api',
        ]);

        // Usuario admin (la contraseña se hashea sola por el cast 'hashed')
        $user = User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name'         => 'Admin',
                'surname'      => 'Principal',
                'password'     => '12345678',
                'role_id'      => $role->id,
                'sucursale_id' => $sucursaleId,
                'state'        => true,
            ]
        );

        $user->assignRole($role);
    }
}