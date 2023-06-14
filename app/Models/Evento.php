<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    protected $fillable = [
        'descripcion',
    ];

    public function bitacoras()
    {
        return $this->belongsTo(Bitacora::class);
    }
}
