<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compuesto extends Model
{
    use HasFactory;

    protected $fillable = [
        'descripcion',
    ];

    public function productos()
    {
        return $this->belongsToMany(Producto::class);
    }
}
