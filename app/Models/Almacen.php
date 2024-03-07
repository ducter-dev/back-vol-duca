<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    use HasFactory;

    protected $table = 'almacenes';

    protected $fillable = [
        'cliente_id',
        'inicio',
        'volumen',
        'fecha',
        'fin',
    ];

    public function cliente() {
        return $this->belongsTo(Cliente::class);
    }
}
