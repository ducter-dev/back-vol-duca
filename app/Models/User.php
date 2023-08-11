<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\BasicStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $table = 'usuarios';

    protected $guard = "users";

    protected $fillable = [
        'nombre',
        'usuario',
        'correo',
        'contrasena',
        'estado',
    ];

    protected $casts = [
        'email_verificado' => 'datetime',
        'status' => BasicStatusEnum::class
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

    /**
     * A model may have multiple roles.
     */
    public function roles()
    {
        return $this->morphToMany(Role::class, 'model', 'model_has_roles', 'model_id', 'role_id');
    }

    public function getAllPermissionsSlug()
    {
        return $this->roles->map(function ($role) {
            return $role->permissions;
        })->collapse()->pluck('description')->unique();
    }

    public function caducidades()
    {
        return $this->hasMany(Caducidad::class, 'usuario_id');
    }
}
