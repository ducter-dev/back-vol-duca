<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id_c',
        'cliente_id_v',
        'cantidad',
        'fecha',
    ];

    public function clienteCompra()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id_c', 'id');
    }

    public function clienteVenta()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id_v', 'id');
    }
}
