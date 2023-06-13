<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Balance;
use App\Models\Salida;
use Illuminate\Support\Facades\DB;

class ObtenerSalidas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salidas:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para obtener los registros de las salidas de IRGE.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $tz = config('app.timezone');
            $now = Carbon::now($tz);
            
            # Obtener  la fecha anterior e insertar el balance
            $lastDay = $now->subDay(1);
            $lastDay = $lastDay->format('Y-m-d');
            $balanceTPA = 0;
            
            /* Obtener el balance de duca */
            $balance = Balance::where('fecha', $lastDay)->first();
            
            /* Obtener el balance de tpa */
            $balanceTPA = DB::connection('mysqlTPA')->table('balances')
                        ->select()
                        ->whereRaw("fecha = ?", [$lastDay])
                        ->first();
                        
            
            /* Obtener las salidas del ducto EB00 */
            $salidasIRGE = DB::connection('mysqlIRGE')->table('entrada')
                            ->select('*','embarques.llenadera_llenado', 'embarques.inicioCarga_llenado', 'embarques.finCarga_llenado')
                            ->join('embarques', function($join) use ($lastDay){
                                $join->on('entrada.noEmbarque', '=', 'embarques.embarque')
                                    ->whereRaw("entrada.fechaJornada = ?", [$lastDay]);
                            })
                            ->get();
            
            if (count($salidasIRGE) > 0) {
                foreach ($salidasIRGE as $item) {
                    
                    $salida = new Salida();
                    $salida->balance_id = $balance->id;
                    $salida->fecha_hora_inicio = $item->inicioCarga_llenado;
                    $salida->fecha_hora_fin = $item->finCarga_llenado;
                    $salida->valor = $item->masa / 1000;
                    $salida->tipo = 'l';
                    $salida->llenadera = $item->llenadera_llenado;
                    $salida->pg = $item->pg;
                    $salida->cliente = $item->compania;
                    $salida->save();
                    $this->info("\e[93mSe registró la salida de Gas  \e[96m$$item->pg \e[93mLlenadera \e[96m$item->llenadera_llenado \e[39m✔ \n");
    
                }
            } else {
                $this->info("\e[91m!No existen salidas para la fecha \e[96$lastDay! \e[39m😔\n") ;
            }
        } catch (\Throwable $th) {
            $this->info("\e[91mError al registrar salidas.\n");
        }
    }
}
