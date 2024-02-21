<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Balance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CrearBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balances:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para crear balance diario.';

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
                DB::commit();
                Log::info("✔ Balance creado: {$balanceCreated->fecha}.");
                return $this->info("✔ Balance creado: {$balanceCreated->fecha}.");
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al crear balance diario: {$e->getMessage()}.");
            $this->error("Error al crear balance diario: {$e->getMessage()}.");
        }
    }
}
