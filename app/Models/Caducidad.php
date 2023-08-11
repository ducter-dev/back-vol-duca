<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caducidad extends Model
{
    use HasFactory;

    protected $table = 'caducidades';

    protected $fillable = [
        'contrasena',
        'caducidad',
        'estado',
        'usuario_id',
    ];
}
