<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dictamen extends Model
{
    use HasFactory;

    protected $table = 'dictamenes';

    protected $fillable  = [
        'rfcDictamen',
        'loteDictamen',
        'folioDictamen',
        'fechaInicioDictamen',
        'fechaEmisionDictamen',
        'resultadoDictamen',
        'densidad',
        'volumen',
        'cliente_id',
        'rutaDictamen',
        'balance_id',
    ];

    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'id','cliente_id');
    }

    public function balance()
    {
        return $this->hasOne(Balance::class, 'id', 'balance_id');
    }

    public function recibos() {
        return $this->hasMany(Recibo::class);
    }
}
