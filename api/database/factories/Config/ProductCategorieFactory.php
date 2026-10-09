<?php

namespace Database\Factories\Config;

use App\Models\Config\ProductCategorie;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductCategorieFactory extends Factory
{
    protected $model = ProductCategorie::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'imagen' => null,
            'state' => true,
        ];
    }
}