<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Config\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use PHPUnit\Framework\Attributes\Test;

class ProviderTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $role;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear permiso necesario para acceder a proveedores
        $permission = Permission::create([
            'name' => 'settings',
            'guard_name' => 'api',
        ]);

        // Crear rol
        $this->role = Role::create([
            'name' => 'Administrador',
            'guard_name' => 'api',
        ]);

        // Asignar permiso al rol
        $this->role->givePermissionTo($permission);

        // Crear usuario administrador
        $this->admin = User::factory()->create([
            'name' => 'Admin',
            'surname' => 'Test',
            'email' => 'admin@test.com',
            'role_id' => $this->role->id,
            'state' => 1,
        ]);

        // Asignar rol al usuario
        $this->admin->assignRole($this->role);

        // Generar JWT
        $this->token = auth()->login($this->admin);
    }

    #[Test]
    public function usuario_puede_listar_proveedores()
    {
        Provider::create([
            'full_name' => 'Proveedor ABC',
            'ruc' => '900123456',
            'email' => 'proveedor@test.com',
            'phone' => '3001234567',
            'address' => 'Cali',
            'state' => 1,
        ]);

        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer ' . $this->token
            )
            ->getJson('/api/providers');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'providers'
        ]);
    }

    #[Test]
    public function usuario_puede_crear_proveedor()
    {
        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer ' . $this->token
            )
            ->postJson('/api/providers', [
                'full_name' => 'Proveedor Nuevo',
                'ruc' => '900987654',
                'email' => 'nuevo@test.com',
                'phone' => '3009876543',
                'address' => 'Cali',
                'state' => 1,
            ]);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 200
        ]);

        $this->assertDatabaseHas('providers', [
            'ruc' => '900987654',
            'full_name' => 'Proveedor Nuevo',
        ]);
    }

    #[Test]
    public function no_permite_crear_proveedor_con_ruc_repetido()
    {
        Provider::create([
            'full_name' => 'Proveedor Existente',
            'ruc' => '900111111',
            'email' => 'existente@test.com',
            'phone' => '3001111111',
            'address' => 'Cali',
            'state' => 1,
        ]);

        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer ' . $this->token
            )
            ->postJson('/api/providers', [
                'full_name' => 'Otro Proveedor',
                'ruc' => '900111111',
                'email' => 'otro@test.com',
                'phone' => '3002222222',
                'address' => 'Palmira',
                'state' => 1,
            ]);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 403
        ]);
    }

    #[Test]
    public function usuario_puede_actualizar_proveedor()
    {
        $provider = Provider::create([
            'full_name' => 'Proveedor Antiguo',
            'ruc' => '900222222',
            'email' => 'antiguo@test.com',
            'phone' => '3003333333',
            'address' => 'Cali',
            'state' => 1,
        ]);

        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer ' . $this->token
            )
            ->postJson(
                '/api/providers/' . $provider->id,
                [
                    'full_name' => 'Proveedor Actualizado',
                    'ruc' => '900222222',
                    'email' => 'actualizado@test.com',
                    'phone' => '3004444444',
                    'address' => 'Palmira',
                    'state' => 1,
                ]
            );

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 200
        ]);

        $this->assertDatabaseHas('providers', [
            'id' => $provider->id,
            'full_name' => 'Proveedor Actualizado',
        ]);
    }

    #[Test]
    public function usuario_puede_eliminar_proveedor()
    {
        $provider = Provider::create([
            'full_name' => 'Proveedor Eliminar',
            'ruc' => '900333333',
            'email' => 'eliminar@test.com',
            'phone' => '3005555555',
            'address' => 'Cali',
            'state' => 1,
        ]);

        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer ' . $this->token
            )
            ->deleteJson('/api/providers/' . $provider->id);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 200
        ]);

        $this->assertSoftDeleted('providers', [
            'id' => $provider->id
        ]);
    }
}