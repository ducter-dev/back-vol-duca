<?php

namespace App\Http\Controllers;

use App\Http\Resources\BitacoraResource;
use App\Models\Bitacora;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;

class BitacoraController extends Controller
{
    use ApiResponder;

    public function index()
    {
        $bitacoras = Bitacora::paginate(20);
        $bitacoras->load('usuario');
        $bitacoras->load('evento');
        $bitacoras = BitacoraResource::collection($bitacoras)->additional([
            'status' => 'success',
            "message" => 'Información consultada correctamente.',
        ]);
        
        return $bitacoras;
    }


    public function destroy($id_bitacora, Request $request)
    {
        try {
            $bitacora = Bitacora::where('id', $id_bitacora)->first();
            $bitacora->delete();

            $resource = new BitacoraResource($bitacora);

            return $this->success('Registro borrado correctamente.', [
                'archivo' => $resource
            ]);
            

        } catch (\Throwable $th) {
            return $this->error("Error al crear archivo, error:{$th->getMessage()}.");
        }
    }
}
