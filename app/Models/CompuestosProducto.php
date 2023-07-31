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

    public function producto() {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function compuesto() {
        return $this->belongsTo(Compuesto::class, 'compuesto_id');
    }
}
