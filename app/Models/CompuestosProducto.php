<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompuestosProducto extends Model
{
    use HasFactory;
    
    protected $table = 'compuestos_productos';
    
    protected $fillable = [
        'producto_id',
        'compuesto_id',
        'porcentaje',
    ];
}
