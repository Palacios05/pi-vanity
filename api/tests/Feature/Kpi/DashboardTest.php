<?php

namespace Tests\Feature\Kpi;

use Tests\TestCase;
use App\Models\User;
use App\Models\Sale\Sale;
use App\Models\Client\Client;
use App\Models\Config\Sucursale;
use Database\Factories\Client\ClientFactory;
use Database\Factories\Config\SucursaleFactory;
use Database\Factories\Config\ProductCategorieFactory;
use Database\Factories\Product\ProductFactory;
use Database\Factories\Purchase\PurchaseFactory;
use Database\Factories\Sale\SaleDetailFactory;
use Spatie\Permission\Models\Permission;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $seller;
    protected Sucursale $sucursale;
    protected Client $client;
    protected array $headers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = User::factory()->create([
            'role_id' => 9,
        ]);

        $this->sucursale = SucursaleFactory::new()->create(['state' => true]);

        $this->client = ClientFactory::new()->create([
            'sucursale_id' => $this->sucursale->id,
        ]);

        $permission = Permission::firstOrCreate([
            'name' => 'dashboard',
            'guard_name' => 'api',
        ]);

        $authUser = User::factory()->create();
        $authUser->givePermissionTo($permission);

        // JWTAuth::fromUser() solo firma el token; a diferencia de
        // auth('api')->login(), no deja al guard "logueado" para el resto
        // del test.
        $token = JWTAuth::fromUser($authUser);

        $this->headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
    }

    /**
     * Crea una venta y, si se especifica 'created_at', fuerza esa fecha
     * con un UPDATE SQL directo (bypaseando Eloquent por completo). Esto
     * es necesario porque, en este proyecto, actualizar 'created_at' vía
     * Eloquent (mass assignment o forceFill+save) no persiste el valor
     * esperado — probablemente por un evento del modelo o un cast que
     * interfiere con el timestamp.
     */
    protected function makeSale(array $overrides = []): Sale
    {
        $createdAt = $overrides['created_at'] ?? null;
        unset($overrides['created_at'], $overrides['updated_at']);

        $sale = Sale::factory()->create(array_merge([
            'user_id' => $this->seller->id,
            'client_id' => $this->client->id,
            'sucursale_id' => $this->sucursale->id,
            'state_sale' => 1,
            'state_payment' => 1,
        ], $overrides));

        if ($createdAt) {
            DB::table('sales')
                ->where('id', $sale->id)
                ->update([
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
        }

        return $sale->fresh();
    }

    /** @test */
    public function no_autenticado_no_puede_acceder_al_dashboard()
    {
        $response = $this->postJson('/api/kpi/information_general');

        $response->assertStatus(401);
    }

    /** @test */
    public function usuario_sin_permiso_dashboard_no_puede_acceder()
    {
        $userSinPermiso = User::factory()->create();
        $token = JWTAuth::fromUser($userSinPermiso);

        $response = $this->postJson('/api/kpi/information_general', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function information_general_calcula_totales_y_variacion_correctamente()
    {
        $now = now();
        $lastMonth = now()->subMonth();

        // Mes actual: 300 en ventas
        $this->makeSale(['total' => 200, 'created_at' => $now]);
        $this->makeSale(['total' => 100, 'created_at' => $now]);

        // Mes anterior: 100 en ventas -> variación esperada = ((300-100)/100)*100 = 200%
        $this->makeSale(['total' => 100, 'created_at' => $lastMonth]);

        PurchaseFactory::new()->create([
            'total' => 50,
            'state' => 3,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $response = $this->postJson('/api/kpi/information_general', [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'variation_percentage_purchase',
                'purchase_total_month_before',
                'purchase_total_month_current',
                'variation_percentage_sucursal_most_sale',
                'sucursales_most_sales_month_current',
                'variation_percentage_total_sales',
                'total_sales_month_current',
            ])
            ->assertJson([
                'total_sales_month_current' => 300,
                'variation_percentage_total_sales' => 200,
                'purchase_total_month_current' => 50,
            ]);
    }

    /** @test */
    public function sales_payment_total_pending_calcula_porcentajes_correctamente()
    {
        $now = now();

        $this->makeSale(['total' => 100, 'state_payment' => 3, 'created_at' => $now]);
        $this->makeSale(['total' => 50, 'state_payment' => 1, 'created_at' => $now]);
        $this->makeSale(['total' => 50, 'state_payment' => 2, 'created_at' => $now]);

        $response = $this->postJson('/api/kpi/sales_payment_total_pending', [], $this->headers);

        $response->assertStatus(200)
            ->assertJson([
                'num_sales_month_current' => 3,
                'num_sales_payment_total_month_current' => 1,
                'num_sales_payment_pending_month_current' => 2,
                'percentage_sale_payment_complete' => round(1 / 3 * 100, 2),
                'percentage_sale_pending' => round(2 / 3 * 100, 2),
            ]);
    }

    /** @test */
    public function sucursales_reporte_sales_devuelve_reporte_por_cada_sucursal()
    {
        $now = now();

        $otraSucursal = SucursaleFactory::new()->create(['state' => true]);

        $this->makeSale(['total' => 100, 'paid_out' => 100, 'debt' => 0, 'created_at' => $now]);
        $this->makeSale([
            'sucursale_id' => $otraSucursal->id,
            'total' => 200,
            'paid_out' => 50,
            'debt' => 150,
            'created_at' => $now,
        ]);

        $response = $this->postJson('/api/kpi/sucursales_reporte_sales', [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'sucursales')
            ->assertJsonFragment(['id' => $this->sucursale->id, 'sales_total_sucursal' => 100])
            ->assertJsonFragment(['id' => $otraSucursal->id, 'sales_total_sucursal' => 200]);
    }

    /** @test */
    public function client_most_sales_identifica_al_cliente_con_mas_ventas()
    {
        $now = now();

        $otroCliente = ClientFactory::new()->create(['sucursale_id' => $this->sucursale->id]);

        $this->makeSale(['client_id' => $this->client->id, 'total' => 100, 'created_at' => $now]);
        $this->makeSale(['client_id' => $otroCliente->id, 'total' => 500, 'created_at' => $now]);

        $response = $this->postJson('/api/kpi/client_most_sales', [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonPath('client_most_sale.client_most_sale_id', $otroCliente->id)
            // Postgres devuelve las columnas decimal como string ("500.00"),
            // por eso comparamos contra el string, no el entero 500.
            ->assertJsonPath('client_most_sale.total_sales', '500.00');
    }

    /** @test */
    public function sales_x_month_of_year_agrupa_ventas_por_mes_del_anio_indicado()
    {
        $year = 2025;

        $this->makeSale([
            'total' => 100,
            'created_at' => "$year-01-15",
        ]);
        $this->makeSale([
            'total' => 200,
            'created_at' => "$year-02-10",
        ]);

        $response = $this->postJson('/api/kpi/sales_x_month_of_year', [
            'year' => $year,
        ], $this->headers);

        $response->assertStatus(200)
            ->assertJson([
                'total_sales_year_current' => 300,
            ])
            ->assertJsonCount(2, 'sale_x_month_of_year_current');
    }

    /** @test */
    public function categories_most_sales_devuelve_top_categorias_y_sus_productos()
    {
        $now = now();

        $categoria = ProductCategorieFactory::new()->create();
        $producto = ProductFactory::new()->create(['product_categorie_id' => $categoria->id]);

        $sale = $this->makeSale(['created_at' => $now]);

        $saleDetail = SaleDetailFactory::new()->create([
            'sale_id' => $sale->id,
            'product_id' => $producto->id,
            'product_categorie_id' => $categoria->id,
            'total' => 150,
        ]);

        DB::table('sale_details')
            ->where('id', $saleDetail->id)
            ->update([
                'created_at' => $now,
                'updated_at' => $now,
            ]);

        $response = $this->postJson('/api/kpi/categories_most_sales', [
            'year' => $now->format('Y'),
            'month' => $now->format('m'),
        ], $this->headers);

        $response->assertStatus(200)
            ->assertJsonPath('categories_products.0.id', $categoria->id)
            ->assertJsonPath('categories_products.0.total_sales', 150)
            ->assertJsonPath('categories_products.0.products.0.product_id', $producto->id);
    }
}