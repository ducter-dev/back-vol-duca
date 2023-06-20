<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EntradasController extends Controller
{
    public function balance()
    {
        return $this->belongsTo(Balance::class,'balance_id', 'id_balance');
    }
}
