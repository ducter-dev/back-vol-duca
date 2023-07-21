<?php

namespace App\Http\Controllers;

use App\Http\Resources\BalanceResource;
use App\Models\Balance;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    use ApiResponder;
    public function index()
    {
        $balances = Balance::all();
        $balances = BalanceResource::collection($balances)->additional([
            'status' => 'success',
            "message" => 'Información consultada correctamente.',
        ]);
        
        return $balances;
    }

    public function store(Request $request)
    {
        
    }

    public function show($id)
    {
        
    }

    public function update(Request $request, $id)
    {
        
    }

    public function destroy($id)
    {
        
    }
}
