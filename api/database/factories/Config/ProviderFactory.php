<?php

namespace Database\Factories\Config;

use App\Models\Config\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProviderFactory extends Factory
{
    protected $model = Provider::class;

    public function definition(): array
    {
        return [
            'full_name' => $this->faker->company(),
            'ruc' => $this->faker->numerify('###########'),
            'state' => true,
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'email' => $this->faker->unique()->companyEmail(),
        ];
    }
}