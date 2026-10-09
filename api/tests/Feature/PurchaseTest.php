<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Config\Unit;
use App\Models\Config\Provider;
use App\Models\Config\Sucursale;
use App\Models\Config\Warehouse;
use App\Models\Config\ProductCategorie;
use App\Models\Product\Product;
use App\Models\Purchase\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdmin()
    {
        $permissions = [
            'list_purchase',
            'register_purchase',
            'edit_purchase',
            'delete_purchase',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api',
            ]);
        }

        $role = Role::firstOrCreate([
            'name' => 'Administrador',
            'guard_name' => 'api',
        ]);

        $role->givePermissionTo($permissions);

        $sucursal = Sucursale::create([
            'name' => 'Sucursal Principal',
            'address' => 'Cali',
            'state' => 1,
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id,
            'sucursale_id' => $sucursal->id,
        ]);

        $user->assignRole($role);

        return [
            $user,
            $sucursal,
        ];
    }

    protected function createPurchaseData($user, $sucursal)
    {
        $provider = Provider::create([
            'full_name' => 'Proveedor de Prueba',
            'ruc' => '900123456',
            'email' => 'proveedor@test.com',
            'phone' => '3001234567',
            'address' => 'Cali',
            'state' => 1,
        ]);

        $warehouse = Warehouse::create([
            'name' => 'Almacén Principal',
            'address' => 'Cali',
            'sucursale_id' => $sucursal->id,
            'state' => 1,
        ]);

        $unit = Unit::create([
            'name' => 'Unidad',
            'description' => 'Unidad de prueba',
            'state' => 1,
        ]);

        $category = ProductCategorie::create([
            'title' => 'Categoría de Prueba',
            'state' => 1,
        ]);

        $product = Product::create([
            'title' => 'Producto de Prueba',
            'sku' => 'PROD-001',
            'product_categorie_id' => $category->id,
            'price_general' => 30000,
            'price_company' => 27000,
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

        return [
            'provider' => $provider,
            'warehouse' => $warehouse,
            'unit' => $unit,
            'product' => $product,
        ];
    }

    public function test_administrador_puede_listar_compras(): void
    {
        [$user, $sucursal] = $this->createAdmin();

        $data = $this->createPurchaseData($user, $sucursal);

        Purchase::create([
            'warehouse_id' => $data['warehouse']->id,
            'user_id' => $user->id,
            'sucursale_id' => $sucursal->id,
            'date_emision' => now()->format('Y-m-d'),
            'state' => 1,
            'type_comprobant' => 'FACTURA',
            'n_comprobant' => 'F001-001',
            'provider_id' => $data['provider']->id,
            'total' => 30000,
            'importe' => 25210.08,
            'igv' => 4789.92,
            'description' => 'Compra de prueba',
        ]);

        $response = $this
            ->actingAs($user, 'api')
            ->getJson('/api/purchases');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'total_page',
            'purchases',
        ]);
    }

    public function test_administrador_puede_crear_compra(): void
    {
        [$user, $sucursal] = $this->createAdmin();

        $data = $this->createPurchaseData($user, $sucursal);

        $response = $this
            ->actingAs($user, 'api')
            ->postJson('/api/purchases', [
                'warehouse_id' => $data['warehouse']->id,
                'date_emision' => now()->format('Y-m-d'),
                'type_comprobant' => 'FACTURA',
                'n_comprobant' => 'F001-001',
                'provider_id' => $data['provider']->id,
                'total' => 30000,
                'importe' => 25210.08,
                'igv' => 4789.92,
                'description' => 'Compra de prueba',
                'purchase_details' => [
                    [
                        'product' => [
                            'id' => $data['product']->id,
                        ],
                        'unit_id' => $data['unit']->id,
                        'price_unit' => 30000,
                        'total' => 30000,
                        'quantity' => 1,
                    ],
                ],
            ]);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 200,
        ]);

        $this->assertDatabaseHas('purchases', [
            'n_comprobant' => 'F001-001',
            'provider_id' => $data['provider']->id,
        ]);

        $this->assertDatabaseHas('purchase_details', [
            'product_id' => $data['product']->id,
            'unit_id' => $data['unit']->id,
            'quantity' => 1,
        ]);
    }

    public function test_administrador_puede_actualizar_compra(): void
    {
        [$user, $sucursal] = $this->createAdmin();

        $data = $this->createPurchaseData($user, $sucursal);

        $purchase = Purchase::create([
            'warehouse_id' => $data['warehouse']->id,
            'user_id' => $user->id,
            'sucursale_id' => $sucursal->id,
            'date_emision' => now()->format('Y-m-d'),
            'state' => 1,
            'type_comprobant' => 'FACTURA',
            'n_comprobant' => 'F001-001',
            'provider_id' => $data['provider']->id,
            'total' => 30000,
            'importe' => 25210.08,
            'igv' => 4789.92,
            'description' => 'Compra original',
        ]);

        $response = $this
            ->actingAs($user, 'api')
            ->putJson('/api/purchases/' . $purchase->id, [
                'provider_id' => $data['provider']->id,
                'type_comprobant' => 'BOLETA',
                'n_comprobant' => 'B001-002',
                'description' => 'Compra actualizada',
            ]);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 200,
        ]);

        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'type_comprobant' => 'BOLETA',
            'n_comprobant' => 'B001-002',
            'description' => 'Compra actualizada',
        ]);
    }

    public function test_administrador_puede_eliminar_compra(): void
    {
        [$user, $sucursal] = $this->createAdmin();

        $data = $this->createPurchaseData($user, $sucursal);

        $purchase = Purchase::create([
            'warehouse_id' => $data['warehouse']->id,
            'user_id' => $user->id,
            'sucursale_id' => $sucursal->id,
            'date_emision' => now()->format('Y-m-d'),
            'state' => 1,
            'type_comprobant' => 'FACTURA',
            'n_comprobant' => 'F001-001',
            'provider_id' => $data['provider']->id,
            'total' => 30000,
            'importe' => 25210.08,
            'igv' => 4789.92,
            'description' => 'Compra de prueba',
        ]);

        $response = $this
            ->actingAs($user, 'api')
            ->deleteJson('/api/purchases/' . $purchase->id);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 200,
        ]);

        $this->assertSoftDeleted('purchases', [
            'id' => $purchase->id,
        ]);
    }

    public function test_no_permite_eliminar_compra_ya_entregada(): void
    {
        [$user, $sucursal] = $this->createAdmin();

        $data = $this->createPurchaseData($user, $sucursal);

        $purchase = Purchase::create([
            'warehouse_id' => $data['warehouse']->id,
            'user_id' => $user->id,
            'sucursale_id' => $sucursal->id,
            'date_emision' => now()->format('Y-m-d'),
            'state' => 2,
            'type_comprobant' => 'FACTURA',
            'n_comprobant' => 'F001-001',
            'provider_id' => $data['provider']->id,
            'total' => 30000,
            'importe' => 25210.08,
            'igv' => 4789.92,
            'description' => 'Compra entregada',
        ]);

        $response = $this
            ->actingAs($user, 'api')
            ->deleteJson('/api/purchases/' . $purchase->id);

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 403,
        ]);

        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'state' => 2,
            'deleted_at' => null,
        ]);
    }
}