<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductosEmpresa extends Model
{
    use HasFactory;

    protected $table = 'productos_empresas';

    protected $fillable = [
        'producto_id',
        'empresa_id',
        'porcentaje'
    ];
    
}
