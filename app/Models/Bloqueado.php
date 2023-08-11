<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bloqueado extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'fecha_bloqueo',
        'fecha_desbloqueo',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id','id');
    }
    
}
