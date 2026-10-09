<?php

namespace Database\Factories\Config;

use App\Models\Config\Sucursale;
use Illuminate\Database\Eloquent\Factories\Factory;

class SucursaleFactory extends Factory
{
    protected $model = Sucursale::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'state' => true,
        ];
    }
}