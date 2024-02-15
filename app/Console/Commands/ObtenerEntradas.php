<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Balance;
use App\Models\Entrada;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ObtenerEntradas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'entradas:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para obtener las entradas de gas del ducto EB00';

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
            $balanceTPA = 0;
    
            /* Obtener el balance de duca */
            $balance = Balance::where('fecha', $lastDay)->first();
            
            /* Obtener el balance de tpa */
            $balanceTPA = DB::connection('mysqlTPA')->table('balances')
                        ->select()
                        ->whereRaw("fecha = ?", [$lastDay])
                        ->first();
            
            /* Obtener las salidas del ducto EB00 */
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
                    $this->info("✔ Se registró la entrada de Gas de la fecha $$item->fecha_hora_fin");
                }
                DB::commit();
                Log::info("✔ Registros guardado correctamente de fecha: {$lastDay}.");
                return $this->info("✔ Registros guardado correctamente de fecha: {$lastDay}.");
            } else {
                $this->info("!No existen entradas para la fecha $lastDay!") ;
                Log::critical("!No existen entradas para la fecha $lastDay!");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            $this->info("Error al registrar entradas: {$e->getMessage()}.");
        }
    }
}
