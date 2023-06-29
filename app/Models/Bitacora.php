<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha',
        'fecha_hora',
        'evento_id',
        'descripcion1',
        'descripcion2',
        'descripcion3',
        'usuario_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id');
    }

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'evento_id', 'id');
    }
}
