<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;


class AuthController extends Controller
{

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register()
{
    Gate::authorize('create', User::class);

    $validator = Validator::make(request()->all(), [
        'name' => 'required',
        'surname' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8',
    ]);

    if($validator->fails()){
        return response()->json($validator->errors()->toJson(), 400);
    }

    $user = new User;
    $user->name = request()->name;
    $user->surname = request()->surname;
    $user->email = request()->email;
    $user->password = request()->password;
    $user->sucursale_id = request()->sucursale_id;
    $user->save();

    return response()->json($user, 201);
}


    /**
     * Login User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Logout user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Refresh token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Token response.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $user = auth('api')->user();

        $permissions = $user->getAllPermissions()->map(function($permission) {
            return $permission->name;
        });


        return response()->json([

            'access_token' => $token,

            'token_type' => 'bearer',

            'expires_in' => auth('api')->factory()->getTTL() * 60,


            "user" => [

                "full_name" => $user->name . ' ' . $user->surname,

                "email" => $user->email,


                "avatar" => $user->avatar
                    ? env("APP_URL") . "storage/" . $user->avatar
                    : null,


                "sucursale_id" => $user->sucursale_id,


                "sucursale" => $user->sucursale
                    ? [
                        "name" => $user->sucursale->name,
                    ]
                    : null,


                "role" => $user->role
                    ? [
                        "id" => $user->role->id,
                        "name" => $user->role->name
                    ]
                    : null,


                "permissions" => $permissions,

            ]

        ]);
    }
}