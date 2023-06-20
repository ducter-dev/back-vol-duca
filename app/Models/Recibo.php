<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recibo extends Model
{
    use HasFactory;

    protected $fillable = [
        'balance_id',
        'dictamen_id',
        'recibo',
    ];

    public function dictamen() {
        return $this->belongsTo(Dictamen::class, 'dictamen_id');
    }

    public function balance()
    {
        return $this->belongsTo(Balance::class);
    }
}
