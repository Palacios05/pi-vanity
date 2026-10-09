<?php

namespace Database\Factories\Config;

use App\Models\Config\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Unidad', 'Caja', 'Paquete']),
            'description' => $this->faker->sentence(),
            'state' => true,
        ];
    }
}