<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Archivo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'ruta',
        'tipo',
        'usuario_id',
        'balance_id',
        'estado',
    ];

    
    public function usuario() {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function balance() {
        return $this->hasOne(Balance::class, 'id_balance', 'balance_id');
    }
}
