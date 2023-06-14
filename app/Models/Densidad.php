<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Densidad extends Model
{
    use HasFactory;

    protected $table = 'densidades';

    protected $fillable = [
        'densidad',
        'balance_id',
    ];

    public function balance(){
        return $this->hasOne(Balance::class, 'id','balance_id');
    }
}
