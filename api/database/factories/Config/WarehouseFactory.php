<?php

namespace Database\Factories\Config;

use App\Models\Config\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word() . ' Warehouse',
            'state' => true,
        ];
    }
}