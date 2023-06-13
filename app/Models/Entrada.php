<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entrada extends Model
{
    use HasFactory;
    
    protected $connection = 'mysql2';

    protected $fillable = [
        'balance_id',
        'fecha_hora',
        'valor'
    ];
}
