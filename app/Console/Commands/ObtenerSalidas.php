<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Balance;
use App\Models\Salida;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        DB::beginTransaction();
        try {
            $tz = config('app.timezone');
            $now = Carbon::now($tz);
            
            # Obtener  la fecha anterior e insertar el balance
            $lastDay = $now->subDay(1);
            $lastDay = $lastDay->format('Y-m-d');
            
            /* Obtener el balance de duca */
            $balance = Balance::where('fecha', $lastDay)->first();
            
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
                    $salida->densidad = $item->densidad;
                    $salida->save();
                    $this->info("✔ Se registró la salida de Gas  $$item->pg Llenadera $item->llenadera_llenado");
                }
                DB::commit();
                Log::info("✔ Se registraron las salidas de Gas");
                return $this->info("✔ Se registraron las salidas de Gas");
            } else {
                $this->info("!No existen salidas para la fecha $lastDay!") ;
                Log::critical("!No existen salidas para la fecha $lastDay!");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al registrar salidas {$e->getMessage()}.");
            $this->info("Error al registrar salidas {$e->getMessage()}.");
        }
    }
}
