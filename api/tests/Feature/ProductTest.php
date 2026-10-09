<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product\Product;
use App\Models\Config\Unit;
use App\Models\Config\Warehouse;
use App\Models\Config\Sucursale;
use App\Models\Config\ProductCategorie;
use App\Models\Product\ProductWallet;
use App\Models\Product\ProductWarehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use PHPUnit\Framework\Attributes\Test;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        /*
        |--------------------------------------------------------------------------
        | Permisos
        |--------------------------------------------------------------------------
        */

        $permissions = [
            'list_product',
            'register_product',
            'edit_product',
            'delete_product',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Rol administrador
        |--------------------------------------------------------------------------
        */

        $role = Role::firstOrCreate([
            'name' => 'Administrador',
            'guard_name' => 'api',
        ]);

        $role->givePermissionTo($permissions);

        /*
        |--------------------------------------------------------------------------
        | Usuario administrador
        |--------------------------------------------------------------------------
        */

        $this->admin = User::factory()->create([
            'email' => 'admin_product@test.com',
        ]);

        $this->admin->assignRole($role);

        /*
        |--------------------------------------------------------------------------
        | JWT
        |--------------------------------------------------------------------------
        */

        $this->token = auth()->login($this->admin);
    }

    /*
    |--------------------------------------------------------------------------
    | Datos auxiliares
    |--------------------------------------------------------------------------
    */

    private function crearCategoria(): ProductCategorie
    {
        return ProductCategorie::create([
            'title' => 'Maquillaje',
            'state' => 1,
        ]);
    }

    private function crearSucursal(): Sucursale
    {
        return Sucursale::create([
            'name' => 'Sucursal Principal',
            'state' => 1,
        ]);
    }

    private function crearAlmacen(): Warehouse
    {
        return Warehouse::create([
            'name' => 'Almacén Principal',
            'state' => 1,
        ]);
    }

    private function crearUnidad(): Unit
    {
        return Unit::create([
            'name' => 'Unidad',
            'state' => 1,
        ]);
    }

    private function datosProducto(): array
    {
        $categoria = $this->crearCategoria();
        $sucursal = $this->crearSucursal();
        $almacen = $this->crearAlmacen();
        $unidad = $this->crearUnidad();

        return [
            'title' => 'Labial Rojo',
            'sku' => 'LAB-001',
            'product_categorie_id' => $categoria->id,
            'price_general' => 25000,
            'price_company' => 22000,
            'description' => 'Labial rojo de prueba',
            'is_discount' => 0,
            'max_discount' => 0,
            'is_gift' => 0,
            'disponibilidad' => 1,
            'state' => 1,
            'state_stock' => 1,
            'warranty_day' => 30,
            'tax_selected' => 0,
            'importe_iva' => 0,

            'product_warehouses' => json_encode([
                [
                    'warehouse_id' => $almacen->id,
                    'unit_id' => $unidad->id,
                    'stock' => 100,
                    'umbral' => 10,
                ]
            ]),

            'product_wallets' => json_encode([
                [
                    'type_client' => 'general',
                    'sucursale_id' => $sucursal->id,
                    'unit_id' => $unidad->id,
                    'price' => 25000,
                ]
            ]),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | LISTAR
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function administrador_puede_listar_productos()
    {
        $categoria = $this->crearCategoria();

        Product::create([
            'title' => 'Base de Maquillaje',
            'sku' => 'BASE-001',
            'product_categorie_id' => $categoria->id,
            'price_general' => 30000,
            'price_company' => 27000,
            'description' => 'Base de prueba',
            'is_discount' => 0,
            'max_discount' => 0,
            'is_gift' => 0,
            'disponibilidad' => 1,
            'state' => 1,
            'state_stock' => 1,
            'warranty_day' => 30,
            'tax_selected' => 0,
            'importe_iva' => 0,
        ]);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/products/index', []);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'total',
            'total_page',
            'products',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREAR
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function administrador_puede_crear_producto()
    {
        $datos = $this->datosProducto();

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/products', $datos);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 200,
        ]);

        $this->assertDatabaseHas('products', [
            'title' => 'Labial Rojo',
            'sku' => 'LAB-001',
        ]);

        $product = Product::where('sku', 'LAB-001')->first();

        $this->assertDatabaseHas('product_warehouses', [
            'product_id' => $product->id,
            'stock' => 100,
            'umbral' => 10,
        ]);

        $this->assertDatabaseHas('product_wallets', [
            'product_id' => $product->id,
            'price' => 25000,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SKU REPETIDO
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function no_permite_crear_producto_con_sku_repetido()
    {
        $datos = $this->datosProducto();

        Product::create([
            'title' => 'Producto Existente',
            'sku' => 'LAB-001',
            'product_categorie_id' => $datos['product_categorie_id'],
            'price_general' => 20000,
            'price_company' => 18000,
            'description' => 'Producto existente',
            'is_discount' => 0,
            'max_discount' => 0,
            'is_gift' => 0,
            'disponibilidad' => 1,
            'state' => 1,
            'state_stock' => 1,
            'warranty_day' => 30,
            'tax_selected' => 0,
            'importe_iva' => 0,
        ]);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/products', $datos);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 403,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function administrador_puede_actualizar_producto()
    {
        $datos = $this->datosProducto();

        $product = Product::create([
            'title' => 'Producto Antiguo',
            'sku' => 'OLD-001',
            'product_categorie_id' => $datos['product_categorie_id'],
            'price_general' => 20000,
            'price_company' => 18000,
            'description' => 'Producto antiguo',
            'is_discount' => 0,
            'max_discount' => 0,
            'is_gift' => 0,
            'disponibilidad' => 1,
            'state' => 1,
            'state_stock' => 1,
            'warranty_day' => 30,
            'tax_selected' => 0,
            'importe_iva' => 0,
        ]);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson(
                '/api/products/' . $product->id,
                [
                    'title' => 'Producto Actualizado',
                    'sku' => 'NEW-001',
                    'product_categorie_id' => $datos['product_categorie_id'],
                    'price_general' => 35000,
                    'price_company' => 30000,
                    'description' => 'Producto actualizado',
                    'is_discount' => 0,
                    'max_discount' => 0,
                    'is_gift' => 0,
                    'disponibilidad' => 1,
                    'state' => 1,
                    'state_stock' => 1,
                    'warranty_day' => 30,
                    'tax_selected' => 0,
                    'importe_iva' => 0,
                ]
            );

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 200,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'title' => 'Producto Actualizado',
            'sku' => 'NEW-001',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ELIMINAR
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function administrador_puede_eliminar_producto()
    {
        $datos = $this->datosProducto();

        $product = Product::create([
            'title' => 'Producto Eliminar',
            'sku' => 'DELETE-001',
            'product_categorie_id' => $datos['product_categorie_id'],
            'price_general' => 20000,
            'price_company' => 18000,
            'description' => 'Producto para eliminar',
            'is_discount' => 0,
            'max_discount' => 0,
            'is_gift' => 0,
            'disponibilidad' => 1,
            'state' => 1,
            'state_stock' => 1,
            'warranty_day' => 30,
            'tax_selected' => 0,
            'importe_iva' => 0,
        ]);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/products/' . $product->id);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 200,
        ]);

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }
}