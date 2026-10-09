<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password = null;


    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),

            'surname' => fake()->lastName(),

            'email' => fake()->unique()->safeEmail(),

            'password' => static::$password ??= Hash::make('password'),

            'role_id' => null,

            'sucursale_id' => null,

            'phone' => fake()->phoneNumber(),

            'state' => true,

        ];
    }
}