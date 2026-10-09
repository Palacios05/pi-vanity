<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Config\Sucursale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use PHPUnit\Framework\Attributes\Test;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $role;
    protected $sucursale;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'list_user']);
        Permission::create(['name' => 'register_user']);
        Permission::create(['name' => 'edit_user']);
        Permission::create(['name' => 'delete_user']);

        $this->role = Role::create([
            'name' => 'Administrador'
        ]);

        $this->role->givePermissionTo([
            'list_user',
            'register_user',
            'edit_user',
            'delete_user',
        ]);

        $this->sucursale = Sucursale::create([
            'name' => 'Sucursal Principal',
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Admin',
            'surname' => 'Test',
            'email' => 'admin@test.com',
            'role_id' => $this->role->id,
            'sucursale_id' => $this->sucursale->id,
            'state' => 1,
        ]);

        $this->admin->assignRole($this->role);
    }


    #[Test]
    public function usuario_puede_listar_usuarios()
    {
        $token = auth()->login($this->admin);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'users'
        ]);
    }


    #[Test]
    public function usuario_puede_crear_usuario()
    {
        $token = auth()->login($this->admin);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/users', [
                'name' => 'Juan',
                'surname' => 'Perez',
                'email' => 'juan@test.com',
                'password' => 'password123',
                'role_id' => $this->role->id,
                'sucursale_id' => $this->sucursale->id,
                'phone' => '3000000000',
                'state' => 1,
            ]);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 200
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'juan@test.com'
        ]);
    }


    #[Test]
    public function no_permite_crear_usuario_con_email_repetido()
    {
        $token = auth()->login($this->admin);

        User::factory()->create([
            'email' => 'repetido@test.com',
        ]);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/users', [
                'name' => 'Otro',
                'surname' => 'Usuario',
                'email' => 'repetido@test.com',
                'password' => 'password123',
                'role_id' => $this->role->id,
                'sucursale_id' => $this->sucursale->id,
            ]);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 403
        ]);
    }


    #[Test]
    public function usuario_puede_actualizar_usuario()
    {
        $token = auth()->login($this->admin);

        $user = User::factory()->create([
            'role_id' => $this->role->id,
            'sucursale_id' => $this->sucursale->id,
        ]);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/users/' . $user->id, [
                'name' => 'Nombre Actualizado',
                'surname' => 'Apellido',
                'email' => $user->email,
                'role_id' => $this->role->id,
                'sucursale_id' => $this->sucursale->id,
                'state' => 1,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nombre Actualizado'
        ]);
    }


    #[Test]
    public function usuario_puede_eliminar_usuario()
    {
        $token = auth()->login($this->admin);

        $user = User::factory()->create([
            'role_id' => $this->role->id,
            'sucursale_id' => $this->sucursale->id,
        ]);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/users/' . $user->id);

        $response->assertStatus(200);

        $this->assertSoftDeleted('users', [
            'id' => $user->id
        ]);
    }


    #[Test]
    public function usuario_puede_obtener_configuracion()
    {
        $token = auth()->login($this->admin);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users/config');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'sucursales',
            'roles'
        ]);
    }
}