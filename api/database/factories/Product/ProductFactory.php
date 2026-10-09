<?php

namespace Database\Factories\Product;

use App\Models\Product\Product;
use Database\Factories\Config\ProductCategorieFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->words(3, true),
            'sku' => $this->faker->unique()->bothify('SKU-####??'),
            'imagen' => null,
            'product_categorie_id' => ProductCategorieFactory::new(),
            'price_general' => $this->faker->randomFloat(2, 10, 200),
            'price_company' => $this->faker->randomFloat(2, 5, 150),
            'state_stock' => true,
            'state' => true,
        ];
    }
}