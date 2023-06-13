<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Balance extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';

    protected $fillable = [
        'fecha',
        'entradas',
        'salidas',
        'inventarioInicial',
    ];
    
}
