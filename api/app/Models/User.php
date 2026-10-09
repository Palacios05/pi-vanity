<?php

namespace App\Models;

use App\Models\Config\Sucursale;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;


    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'role_id',
        'sucursale_id',
        'phone',
        'avatar',
        'type_document',
        'n_document',
        'gender',
        'state',
    ];


    protected $hidden = [
        'password',
    ];


    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'state' => 'boolean',
        ];
    }


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }


    public function getJWTCustomClaims()
    {
        return [];
    }


    public function sucursale()
    {
        return $this->belongsTo(Sucursale::class, 'sucursale_id');
    }
}