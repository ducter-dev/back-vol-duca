<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Balance;

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
                $this->info("Balance creado.");
            }
            
        } catch (\Throwable $th) {
            $this->info("Error al crear balance diario.");
        }
    }
}
