<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use \App\Http\Controllers\UserController;
use App\Models\Balance;
use App\Models\Entrada;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Artisan::command('creaBalanceDiario', function() {
    $tz = config('app.timezone');
    $now = Carbon::now($tz);
    # Obtener  la fecha anterior e insertar el balance
    $lastDay = $now->subDay(1);
    $lastDay = $lastDay->format('Y-m-d');
    
    $balance = Balance::where('fecha', $lastDay)->first();

    if ($balance == '') {
        $balanceCreated = Balance::create(
            [
                'fecha' => $lastDay,
                'entradas' => 0,
                'salidas' => 0,
                'inventarioInicial' => 0
            ]
        );
        echo "\e[93mBalance con fecha \e[96m $lastDay \e[93mha sido creado.";
    } else {
        echo "\e[91m Balance \e[96m$balance->id \e[91mcon fecha \e[96m$balance->fecha \e[91ma existe. \n";
    }
    
});

Artisan::command('crearBalancesMes', function () {
    $days = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31];
    
    foreach ($days as $day) {
        $fecha = Carbon::createFromDate(2023,6, $day);
        echo $fecha;
        echo '\n';

        $balance = Balance::where('fecha', $fecha)->first();

        if ($balance == '') {
            $balanceCreated = Balance::create(
                [
                    'fecha' => $fecha,
                    'entradas' => 0,
                    'salidas' => 0,
                    'inventarioInicial' => 0
                ]
            );
            echo "\e[93mBalance con fecha \e[96m $fecha \e[93mha sido creado.";
        } else {
            echo "\e[91m Balance \e[96m$balance->id \e[91mcon fecha \e[96m$balance->fecha \e[91ma existe. \n";
        }
    }
});


Artisan::command('obtenerEntradas', function () {
    $tz = config('app.timezone');
    $now = Carbon::now($tz);
    # Obtener  la fecha anterior e insertar el balance
    $lastDay = $now->subDay(1);
    $lastDay = $lastDay->format('Y-m-d');
    $balanceTPA = 0;

    /* Obtener las salidas del ducto EB00 */
    $balance = Balance::where('fecha', $lastDay)->first();

    $balanceTPA = DB::connection('mysqlTPA')->table('balances')
                    ->select()
                    ->whereRaw("fecha = ?", [$lastDay])
                    ->first();

    $salidasEB00 = DB::connection('mysqlTPA')->table('salidasDetalle')
                    ->select()
                    ->whereRaw("balance_id = ? AND tipo = ?", [$balanceTPA->id_balance,'d'])
                    ->get();
    
    if (count($salidasEB00) > 0) {
        foreach ($salidasEB00 as $item) {
            $entrada = new Entrada();
            $entrada->balance_id = $balance->id;
            $entrada->fecha_hora = $item->fecha_hora_fin;
            $entrada->valor = $item->valor;
            $entrada->save();
            echo "\e[93mSe registró la entrada de Gas de la fecha \e[96m$$item->fecha_hora_fin \e[39m✔ \n";

        }
    } else {
        echo "\e[91m!No existen salidas para la fecha \e[96$lastDay! \e[39m😔";
    }
});
