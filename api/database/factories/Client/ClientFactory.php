<?php

namespace Database\Factories\Client;

use App\Models\User;
use App\Models\Client\Client;
use Database\Factories\Config\SucursaleFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        $name = $this->faker->firstName();
        $surname = $this->faker->lastName();

        return [
            'name' => $name,
            'surname' => $surname,
            'full_name' => $name . ' ' . $surname,
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'type_client' => 'natural',
            'type_document' => 'DNI',
            'n_document' => $this->faker->unique()->numerify('########'),
            'user_id' => User::factory(),
            'sucursale_id' => SucursaleFactory::new(),
            'state' => true,
            'gender' => $this->faker->randomElement(['M', 'F']),
        ];
    }
}