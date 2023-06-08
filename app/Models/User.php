<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'usuario',
        'correo',
        'perfil_id',
        'empresa_id',
        'contrasena',
        'estado',
    ];

    protected $casts = [
        'email_verificado' => 'datetime',
    ];

    protected $hidden = [
        'contrasena',
        'recordar_token',
    ];

    const CREATED_AT = 'creado';
    const UPDATED_AT = 'actualizado';

    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['contrasena'] = bcrypt($value);
    }
}
