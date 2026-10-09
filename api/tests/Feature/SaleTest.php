<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client\Client;
use App\Models\Config\Unit;
use App\Models\Config\Warehouse;
use App\Models\Config\Sucursale;
use App\Models\Config\ProductCategorie;
use App\Models\Product\Product;
use App\Models\Product\ProductWarehouse;
use App\Models\Sale\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Sucursale $sucursale;

    protected function setUp(): void
    {
        parent::setUp();

        /*
        |--------------------------------------------------------------------------
        | Permisos
        |--------------------------------------------------------------------------
        */

        $permissions = [
            'list_sale',
            'register_sale',
            'edit_sale',
            'delete_sale',
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'api',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Rol administrador
        |--------------------------------------------------------------------------
        */

        $role = Role::create([
            'name' => 'Administrador',
            'guard_name' => 'api',
        ]);

        $role->givePermissionTo($permissions);

        /*
        |--------------------------------------------------------------------------
        | Sucursal
        |--------------------------------------------------------------------------
        */

        $this->sucursale = Sucursale::create([
            'name' => 'Sucursal Principal',
            'address' => 'Cali',
            'state' => 1,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Usuario administrador
        |--------------------------------------------------------------------------
        */

        $this->admin = User::factory()->create([
            'role_id' => $role->id,
            'sucursale_id' => $this->sucursale->id,
            'state' => 1,
        ]);

        $this->admin->assignRole($role);
    }

    /*
    |--------------------------------------------------------------------------
    | Datos necesarios para crear una venta
    |--------------------------------------------------------------------------
    */

    protected function createSaleData(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Cliente
        |--------------------------------------------------------------------------
        */

        $client = Client::create([
            'name' => 'Juan',
            'surname' => 'Perez',
            'full_name' => 'Juan Perez',
            'phone' => '3000000000',
            'email' => 'cliente@test.com',
            'type_client' => 'NORMAL',
            'type_document' => 'CC',
            'n_document' => '123456789',
            'user_id' => $this->admin->id,
            'sucursale_id' => $this->sucursale->id,
            'state' => true,
            'address' => 'Cali',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Categoría
        |--------------------------------------------------------------------------
        */

        $category = ProductCategorie::create([
            'title' => 'Categoría Test',
            'state' => 1,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Unidad
        |--------------------------------------------------------------------------
        */

        $unit = Unit::create([
            'name' => 'Unidad',
            'description' => 'Unidad',
            'state' => 1,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Bodega
        |--------------------------------------------------------------------------
        */

        $warehouse = Warehouse::create([
            'name' => 'Bodega Principal',
            'address' => 'Cali',
            'sucursale_id' => $this->sucursale->id,
            'state' => 1,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Producto
        |--------------------------------------------------------------------------
        */

        $product = Product::create([
            'title' => 'Producto Test',
            'sku' => 'PROD001',
            'product_categorie_id' => $category->id,
            'price_general' => 100000,
            'price_company' => 90000,
            'description' => 'Producto de prueba',
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

        /*
        |--------------------------------------------------------------------------
        | Stock del producto
        |--------------------------------------------------------------------------
        */

        ProductWarehouse::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'unit_id' => $unit->id,
            'stock' => 20,
            'umbral' => 5,
            'state_stock' => 1,
        ]);

        return [
            'client' => $client,
            'category' => $category,
            'unit' => $unit,
            'warehouse' => $warehouse,
            'product' => $product,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Payload de una venta
    |--------------------------------------------------------------------------
    */

    protected function getSalePayload(array $data): array
    {
        return [
            'client_id' => $data['client']->id,
            'type_client' => $data['client']->type_client,

            'subtotal' => 100000,
            'discount' => 0,
            'igv' => 0,
            'total' => 100000,

            'state_sale' => 1,
            'state_payment' => 1,

            'debt' => 100000,
            'paid_out' => 0,

            'description' => 'Venta de prueba',

            'sale_details' => [
                [
                    'product' => [
                        'id' => $data['product']->id,
                        'product_categorie_id' => $data['category']->id,
                    ],

                    'warehouse_id' => $data['warehouse']->id,
                    'unit_id' => $data['unit']->id,

                    'quantity' => 2,
                    'price' => 50000,
                    'discount' => 0,
                    'subtotal' => 100000,
                    'igv' => 0,
                    'total' => 100000,
                ],
            ],

            'payments' => [
                [
                    'method_payment' => 'EFECTIVO',
                    'amount' => 100000,
                ],
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | TEST 1
    | Administrador puede listar ventas
    |--------------------------------------------------------------------------
    */

    public function test_administrador_puede_listar_ventas()
    {
        $client = Client::create([
            'name' => 'Juan',
            'surname' => 'Perez',
            'full_name' => 'Juan Perez',
            'phone' => '3000000000',
            'email' => 'cliente@test.com',
            'type_client' => 'NORMAL',
            'type_document' => 'CC',
            'n_document' => '123456789',
            'user_id' => $this->admin->id,
            'sucursale_id' => $this->sucursale->id,
            'state' => true,
            'address' => 'Cali',
        ]);

        Sale::create([
            'user_id' => $this->admin->id,
            'client_id' => $client->id,
            'type_client' => $client->type_client,
            'sucursale_id' => $this->sucursale->id,
            'subtotal' => 100000,
            'discount' => 0,
            'igv' => 0,
            'total' => 100000,
            'state_sale' => 1,
            'state_payment' => 1,
            'state_entrega' => 1,
            'debt' => 100000,
            'paid_out' => 0,
            'description' => 'Venta prueba',
        ]);

        $token = auth()->login($this->admin);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/sales');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'total_page',
            'sales',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TEST 2
    | Administrador puede crear venta
    |--------------------------------------------------------------------------
    */

    public function test_administrador_puede_crear_venta()
    {
        $token = auth()->login($this->admin);

        $data = $this->createSaleData();

        $payload = $this->getSalePayload($data);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/sales', $payload);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 200,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Verificar venta
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseHas('sales', [
            'client_id' => $data['client']->id,
            'total' => 100000,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Verificar detalle
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseHas('sale_details', [
            'product_id' => $data['product']->id,
            'quantity' => 2,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Verificar pago
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseHas('sale_payments', [
            'method_payment' => 'EFECTIVO',
            'amount' => 100000,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Verificar descuento de stock
        |--------------------------------------------------------------------------
        */

        $this->assertDatabaseHas('product_warehouses', [
            'product_id' => $data['product']->id,
            'warehouse_id' => $data['warehouse']->id,
            'stock' => 18,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TEST 3
    | Usuario sin permiso no puede crear venta
    |--------------------------------------------------------------------------
    */

    public function test_usuario_sin_permiso_no_puede_crear_venta()
    {
        $role = Role::create([
            'name' => 'Vendedor Sin Permiso',
            'guard_name' => 'api',
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id,
            'sucursale_id' => $this->sucursale->id,
            'state' => 1,
        ]);

        $user->assignRole($role);

        $token = auth()->login($user);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/sales', []);

        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | TEST 4
    | Usuario no autenticado no puede crear venta
    |--------------------------------------------------------------------------
    */

    public function test_usuario_no_autenticado_no_puede_crear_venta()
    {
        $response = $this->postJson('/api/sales', []);

        $response->assertStatus(401);
    }

    /*
    |--------------------------------------------------------------------------
    | TEST 5
    | El stock se descuenta correctamente
    |--------------------------------------------------------------------------
    */

    public function test_el_stock_se_descuenta_correctamente()
    {
        $token = auth()->login($this->admin);

        $data = $this->createSaleData();

        $this->assertDatabaseHas('product_warehouses', [
            'product_id' => $data['product']->id,
            'stock' => 20,
        ]);

        $payload = $this->getSalePayload($data);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/sales', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('product_warehouses', [
            'product_id' => $data['product']->id,
            'stock' => 18,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TEST 6
    | Se registra correctamente el detalle de venta
    |--------------------------------------------------------------------------
    */

    public function test_se_registra_el_detalle_de_la_venta()
    {
        $token = auth()->login($this->admin);

        $data = $this->createSaleData();

        $payload = $this->getSalePayload($data);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/sales', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('sale_details', [
            'product_id' => $data['product']->id,
            'quantity' => 2,
            'warehouse_id' => $data['warehouse']->id,
            'unit_id' => $data['unit']->id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TEST 7
    | Se registra correctamente el pago
    |--------------------------------------------------------------------------
    */

    public function test_se_registra_el_pago()
    {
        $token = auth()->login($this->admin);

        $data = $this->createSaleData();

        $payload = $this->getSalePayload($data);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/sales', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('sale_payments', [
            'method_payment' => 'EFECTIVO',
            'amount' => 100000,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TEST 8
    | La venta queda completamente entregada
    |--------------------------------------------------------------------------
    */

    public function test_venta_queda_completamente_entregada()
    {
        $token = auth()->login($this->admin);

        $data = $this->createSaleData();

        $payload = $this->getSalePayload($data);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/sales', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('sales', [
            'client_id' => $data['client']->id,
            'state_entrega' => 3,
        ]);
    }
}