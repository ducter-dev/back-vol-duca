<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArchivoResource;
use App\Models\Archivo;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use App\Traits\ApiResponder;

class ArchivoController extends Controller
{
    use ApiResponder;

    public function index()
    {
        $archivos = Archivo::orderBy('balance_id', 'asc')->orderBy('estado', 'asc')->orderBy('id', 'asc')->paginate(15);
        $archivos->load('usuario');
        $archivos->load('balance');
        $archivos = ArchivoResource::collection($archivos)->additional([
            'status' => 'success',
            "message" => 'Información consultada correctamente.',
        ]);
        
        return $archivos;
    }

    public function show($id_archivo)
    {
        try {
            $archivo = Archivo::where('id', $id_archivo)->first();

            if ($archivo == NULL)
            {
                return $this->error("Error, NO se encontró el archivo.");
            }

            $archivo->load('usuario');
            $archivo->load('balance');
            $resource = new ArchivoResource($archivo);

            return $this->success('Información consultada correctamente.', [
                'archivo' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al crear archivo, error:{$th->getMessage()}.");
        }
    }

    public function update(Request $request, $id_archivo)
    {
        try {
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function destroy($id_archivo, Request $request)
    {
        try {
            $archivo = Archivo::where('id', $id_archivo)->first();

            if ($archivo->estado == 1)
            {
                return $this->error("No se puede borrar el archivo actual.");
            }

            $archivo->delete();
            $archivo->load('usuario');
            $archivo->load('balance');

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'eliminó el archivo ' . $id_archivo;
            $bitacora->descripcion3 = '';
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();

            $resource = new ArchivoResource($archivo);

            return $this->success('Archivo borrado correctamente.', [
                'archivo' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al crear archivo, error:{$th->getMessage()}.");
        }
    }
}
