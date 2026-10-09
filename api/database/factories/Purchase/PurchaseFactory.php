<?php

namespace Database\Factories\Purchase;

use App\Models\User;
use App\Models\Purchase\Purchase;
use Database\Factories\Config\WarehouseFactory;
use Database\Factories\Config\SucursaleFactory;
use Database\Factories\Config\ProviderFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition(): array
    {
        return [
            'warehouse_id' => WarehouseFactory::new(),
            'user_id' => User::factory(),
            'sucursale_id' => SucursaleFactory::new(),
            'date_emision' => now(),
            'state' => 3,
            'type_comprobant' => 'Factura',
            'n_comprobant' => $this->faker->unique()->numerify('F001-####'),
            'provider_id' => ProviderFactory::new(),
            'total' => 0,
            'importe' => 0,
            'igv' => 0,
        ];
    }
}