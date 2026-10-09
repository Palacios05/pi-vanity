<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client\Client;
use App\Models\Config\Sucursale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $role;
    protected $sucursale;

    protected function setUp(): void
    {
        parent::setUp();

        // Permisos
        Permission::create(['name' => 'list_client']);
        Permission::create(['name' => 'register_client']);
        Permission::create(['name' => 'edit_client']);
        Permission::create(['name' => 'delete_client']);

        // Rol
        $this->role = Role::create([
            'name' => 'Administrador',
            'guard_name' => 'api'
        ]);

        $this->role->givePermissionTo([
            'list_client',
            'register_client',
            'edit_client',
            'delete_client',
        ]);

        // Sucursal
        $this->sucursale = Sucursale::create([
            'name' => 'Sucursal Principal'
        ]);

        // Usuario administrador
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
    public function usuario_puede_listar_clientes()
    {
        Client::create([
            'name' => 'Juan',
            'surname' => 'Perez',
            'full_name' => 'Juan Perez',
            'phone' => '3001234567',
            'email' => 'juan@test.com',
            'n_document' => '12345678',
            'user_id' => $this->admin->id,
            'sucursale_id' => $this->sucursale->id,
            'state' => 1,
        ]);

        $token = auth()->login($this->admin);

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/clients');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'total_page',
            'clients'
        ]);
    }

    #[Test]
    public function usuario_puede_crear_cliente()
    {
        $token = auth()->login($this->admin);

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/clients',[
                'name'=>'Juan',
                'surname'=>'Perez',
                'full_name'=>'Juan Perez',
                'phone'=>'3001234567',
                'email'=>'juan@test.com',
                'n_document'=>'12345678',
                'state'=>1
            ]);

        $response->assertStatus(200);

        $response->assertJson([
            'message'=>200
        ]);

        $this->assertDatabaseHas('clients',[
            'n_document'=>'12345678'
        ]);
    }

    #[Test]
    public function no_permite_crear_cliente_con_documento_repetido()
    {
        Client::create([
            'name'=>'Juan',
            'surname'=>'Perez',
            'full_name'=>'Juan Perez',
            'phone'=>'3001234567',
            'email'=>'juan@test.com',
            'n_document'=>'12345678',
            'user_id'=>$this->admin->id,
            'sucursale_id'=>$this->sucursale->id,
            'state'=>1
        ]);

        $token = auth()->login($this->admin);

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/clients',[
                'name'=>'Pedro',
                'surname'=>'Lopez',
                'full_name'=>'Pedro Lopez',
                'phone'=>'3000000000',
                'email'=>'pedro@test.com',
                'n_document'=>'12345678',
                'state'=>1
            ]);

        $response->assertStatus(200);

        $response->assertJson([
            'message'=>403
        ]);
    }

    #[Test]
    public function usuario_puede_actualizar_cliente()
    {
        $client = Client::create([
            'name'=>'Juan',
            'surname'=>'Perez',
            'full_name'=>'Juan Perez',
            'phone'=>'3001234567',
            'email'=>'juan@test.com',
            'n_document'=>'12345678',
            'user_id'=>$this->admin->id,
            'sucursale_id'=>$this->sucursale->id,
            'state'=>1
        ]);

        $token = auth()->login($this->admin);

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/clients/'.$client->id,[
                'name'=>'Carlos',
                'surname'=>'Perez',
                'full_name'=>'Carlos Perez',
                'phone'=>'3001234567',
                'email'=>'juan@test.com',
                'n_document'=>'12345678',
                'state'=>1
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('clients',[
            'id'=>$client->id,
            'name'=>'Carlos'
        ]);
    }

    #[Test]
    public function usuario_puede_eliminar_cliente()
    {
        $client = Client::create([
            'name'=>'Juan',
            'surname'=>'Perez',
            'full_name'=>'Juan Perez',
            'phone'=>'3001234567',
            'email'=>'juan@test.com',
            'n_document'=>'12345678',
            'user_id'=>$this->admin->id,
            'sucursale_id'=>$this->sucursale->id,
            'state'=>1
        ]);

        $token = auth()->login($this->admin);

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/clients/'.$client->id);

        $response->assertStatus(200);

        $this->assertSoftDeleted('clients',[
            'id'=>$client->id
        ]);
    }
}