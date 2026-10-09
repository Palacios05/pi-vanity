<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Config\ProductCategorie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use PHPUnit\Framework\Attributes\Test;


class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $role;


    protected function setUp(): void
    {
        parent::setUp();


        // Crear permiso necesario para categorías
        Permission::create([
            'name'=>'settings',
        ]);


        // Crear rol administrador
        $this->role = Role::create([
            'name'=>'Administrador'
        ]);


        $this->role->givePermissionTo([
            'settings'
        ]);


        // Crear usuario administrador
        $this->admin = User::factory()->create([
            'name'=>'Admin',
            'email'=>'admin@test.com',
            'role_id'=>$this->role->id,
            'state'=>1
        ]);


        $this->admin->assignRole($this->role);

    }



    #[Test]
    public function usuario_puede_listar_categorias()
    {

        ProductCategorie::create([
            'title'=>'Maquillaje',
            'state'=>1
        ]);


        $token = auth()->login($this->admin);


        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer '.$token
            )
            ->getJson('/api/categories');


        $response->assertStatus(200);


        $response->assertJsonStructure([
            'categories'
        ]);

    }




    #[Test]
    public function usuario_puede_crear_categoria()
    {

        $token = auth()->login($this->admin);


        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer '.$token
            )
            ->postJson('/api/categories',[

                'title'=>'Labiales',
                'state'=>1

            ]);


        $response->assertStatus(200);


        $response->assertJson([
            'message'=>200
        ]);


        $this->assertDatabaseHas(
            'product_categories',
            [
                'title'=>'Labiales'
            ]
        );

    }




    #[Test]
    public function no_permite_crear_categoria_repetida()
    {

        ProductCategorie::create([
            'title'=>'Sombras',
            'state'=>1
        ]);


        $token = auth()->login($this->admin);


        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer '.$token
            )
            ->postJson('/api/categories',[

                'title'=>'Sombras',
                'state'=>1

            ]);


        $response->assertStatus(200);


        $response->assertJson([
            'message'=>403
        ]);

    }





    #[Test]
    public function usuario_puede_actualizar_categoria()
    {

        $categorie = ProductCategorie::create([

            'title'=>'Categoria Antigua',
            'state'=>1

        ]);


        $token = auth()->login($this->admin);


        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer '.$token
            )
            ->postJson(
                '/api/categories/'.$categorie->id,
                [

                    'title'=>'Categoria Nueva',
                    'state'=>1

                ]
            );


        $response->assertStatus(200);


        $this->assertDatabaseHas(
            'product_categories',
            [

                'id'=>$categorie->id,
                'title'=>'Categoria Nueva'

            ]
        );

    }





    #[Test]
    public function usuario_puede_eliminar_categoria()
    {

        $categorie = ProductCategorie::create([

            'title'=>'Categoria Eliminar',
            'state'=>1

        ]);


        $token = auth()->login($this->admin);


        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer '.$token
            )
            ->deleteJson(
                '/api/categories/'.$categorie->id
            );


        $response->assertStatus(200);


        $this->assertSoftDeleted(
            'product_categories',
            [

                'id'=>$categorie->id

            ]
        );

    }

}