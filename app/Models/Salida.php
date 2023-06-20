<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salida extends Model
{
    use HasFactory;

    protected $table = 'balances_duca.salidas';

    protected $fillable = [
        'balance_id',
        'fecha_hora_inicio',
        'fecha_hora_fin',
        'valor',
        'tipo',
        'pg',
        'llenadera',
        'cliente'
    ];

    public function dictamen()
    {
        return $this->hasOne(Dictamen::class, 'id', 'dictamen_id');
    }

    public function compania()
    {
        return $this->belongsTo(Cliente::class, 'cliente', 'id');
    }

}
