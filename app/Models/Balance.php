<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Balance extends Model
{
    use HasFactory;

    protected $table = 'balances_duca.balances';

    protected $fillable = [
        'fecha',
        'entradas',
        'salidas',
        'inventarioInicial',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function dictamenes()
    {
        return $this->hasMany(Dictamen::class);
    }

    public function archivos() {
        return $this->hasMany(Archivo::class);
    }

    public function densidad() {
        return $this->hasOne(Densidad::class);
    }

    public function recibos() {
        return $this->hasMany(Recibo::class);
    }
    
}
