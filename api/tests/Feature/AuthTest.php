<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;


    /**
     * Usuario puede iniciar sesión correctamente
     */
    public function test_usuario_puede_hacer_login()
    {

        $user = User::factory()->create([
            'name' => 'Juan Test',
            'email' => 'juan@test.com',
            'password' => Hash::make('password123'),
        ]);


        $response = $this->postJson('/api/auth/login', [

            'email' => 'juan@test.com',
            'password' => 'password123',

        ]);


        $response->assertStatus(200);


        $response->assertJsonStructure([

            'access_token',
            'user'

        ]);

    }



    /**
     * Usuario no puede iniciar sesión con contraseña incorrecta
     */
    public function test_usuario_no_puede_loguearse_con_password_incorrecta()
    {

        User::factory()->create([

            'email' => 'juan@test.com',
            'password' => Hash::make('password123'),

        ]);


        $response = $this->postJson('/api/auth/login', [

            'email' => 'juan@test.com',
            'password' => 'passwordincorrecta',

        ]);


        $response->assertStatus(401);


    }



    /**
     * No permite acceder a rutas protegidas sin token
     */
    public function test_usuario_no_puede_acceder_sin_token()
    {


        $response = $this->postJson('/api/auth/me');


        $response->assertStatus(401);


    }



    /**
     * Usuario autenticado puede acceder a su perfil
     */
    public function test_usuario_puede_obtener_su_perfil_con_token()
    {


        $user = User::factory()->create([

            'name' => 'Juan Test',
            'email' => 'juan@test.com',
            'password' => Hash::make('password123'),

        ]);


        $token = auth()->login($user);


        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer '.$token
            )
            ->postJson('/api/auth/me');


        $response->assertStatus(200);


        $response->assertJsonStructure([

            'id',
            'name',
            'email'

        ]);

    }

}