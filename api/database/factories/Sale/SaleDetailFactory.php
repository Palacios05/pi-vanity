<?php

namespace Database\Factories\Sale;

use App\Models\Sale\Sale;
use App\Models\Sale\SaleDetail;
use Database\Factories\Product\ProductFactory;
use Database\Factories\Config\UnitFactory;
use Database\Factories\Config\WarehouseFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleDetailFactory extends Factory
{
    protected $model = SaleDetail::class;

    public function definition(): array
    {
        return [
            'sale_id' => Sale::factory(),
            'product_id' => ProductFactory::new(),
            'product_categorie_id' => function (array $attributes) {
                return \App\Models\Product\Product::find($attributes['product_id'])->product_categorie_id;
            },
            'unit_id' => UnitFactory::new(),
            'warehouse_id' => WarehouseFactory::new(),
            'quantity' => 1,
            'price_unit' => 0,
            'discount' => 0,
            'subtotal' => 0,
            'igv' => 0,
            'total' => 0,
            'state_attention' => 1,
            'quantity_pending' => 0,
        ];
    }
}