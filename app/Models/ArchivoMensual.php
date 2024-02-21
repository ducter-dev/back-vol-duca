<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivoMensual extends Model
{
    use HasFactory;

    protected $table = 'archivo_mensuales';

    protected $fillable = [
        'nombre',
        'ruta',
        'usuario_id',
        'balance_id',
        'periodo',
        'estado',
    ];

    public function usuario() {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
