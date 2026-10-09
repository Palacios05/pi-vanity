<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use PHPUnit\Framework\Attributes\Test;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $role;
    protected $permissions;


    protected function setUp(): void
    {
        parent::setUp();


        // Crear permisos necesarios para roles
        Permission::create([
            'name' => 'viewAny',
            'guard_name' => 'api'
        ]);

        Permission::create([
            'name' => 'create',
            'guard_name' => 'api'
        ]);

        Permission::create([
            'name' => 'update',
            'guard_name' => 'api'
        ]);

        Permission::create([
            'name' => 'delete',
            'guard_name' => 'api'
        ]);


        // Permisos que tendrá el nuevo rol
        $this->permissions = [
            Permission::create([
                'name' => 'list_user',
                'guard_name' => 'api'
            ]),
            Permission::create([
                'name' => 'register_user',
                'guard_name' => 'api'
            ])
        ];


        // Crear rol administrador
        $this->role = Role::create([
            'name' => 'Administrador',
            'guard_name' => 'api'
        ]);


        $this->role->givePermissionTo([
            'viewAny',
            'create',
            'update',
            'delete'
        ]);


        // Crear usuario administrador
        $this->admin = User::factory()->create([
            'email' => 'admin@test.com'
        ]);


        $this->admin->assignRole($this->role);
    }



    #[Test]
    public function administrador_puede_listar_roles()
    {
        $token = auth()->login($this->admin);


        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/role');


        $response->assertStatus(200);

        $response->assertJsonStructure([
            'roles'
        ]);
    }



    #[Test]
    public function administrador_puede_crear_rol()
    {
        $token = auth()->login($this->admin);


        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/role', [
                'name' => 'Vendedor',
                'permissions' => [
                    'list_user',
                    'register_user'
                ]
            ]);


        $response->assertStatus(200);


        $response->assertJson([
            'message' => 200
        ]);


        $this->assertDatabaseHas('roles', [
            'name' => 'Vendedor'
        ]);
    }



    #[Test]
    public function no_permite_crear_rol_repetido()
    {
        $token = auth()->login($this->admin);


        Role::create([
            'name' => 'Vendedor',
            'guard_name' => 'api'
        ]);


        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/role', [
                'name' => 'Vendedor',
                'permissions' => []
            ]);


        $response->assertStatus(200);


        $response->assertJson([
            'message' => 403
        ]);
    }



    #[Test]
    public function administrador_puede_actualizar_rol()
    {
        $token = auth()->login($this->admin);


        $role = Role::create([
            'name' => 'Vendedor',
            'guard_name' => 'api'
        ]);


        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/role/' . $role->id, [
                'name' => 'Supervisor',
                'permissions' => [
                    'list_user'
                ]
            ]);


        $response->assertStatus(200);


        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'Supervisor'
        ]);
    }



    #[Test]
    public function administrador_puede_eliminar_rol()
    {
        $token = auth()->login($this->admin);


        $role = Role::create([
            'name' => 'Temporal',
            'guard_name' => 'api'
        ]);


        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/role/' . $role->id);


        $response->assertStatus(200);


        $this->assertDatabaseMissing('roles', [
            'id' => $role->id
        ]);
    }
}