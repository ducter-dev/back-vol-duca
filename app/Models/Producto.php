<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'descripcion',
        'clave'
    ];

    public function compuestos()
    {
        //return $this->belongsToMany(Compuesto::class, 'compuestos_productos')->withPivot('porcentaje')->as('porcentaje');
        return $this->belongsToMany(Compuesto::class, 'compuestos_productos')->withPivot('porcentaje')->as('porcentajes');
    }

    public function omisiones()
    {
        return $this->belongsTo(Empresa::class, 'producto_omision');
    }
    
}
