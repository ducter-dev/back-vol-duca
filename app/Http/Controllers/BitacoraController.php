<?php

namespace App\Http\Controllers;

use App\Http\Resources\BitacoraResource;
use App\Models\Bitacora;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BitacoraController extends Controller
{
    use ApiResponder;

    public function index()
    {
        $bitacoras = Bitacora::paginate(15);
        $bitacoras = BitacoraResource::collection($bitacoras)->additional([
            'status' => 'success',
            "message" => 'Información consultada correctamente.',
        ]);
        
        return $bitacoras;
    }


    public function update(Request $request, $id_bitacora)
    {
        
        try {
            $rules = [
                'fecha' => 'required|date_format:Y-m-d',
                'fecha_hora' => 'required|date_format:Y-m-d H:i:s',
                'evento_id' => 'required|numeric',
                'descripcion1' => 'required|string|max:255',
                'descripcion2' => 'required|string|max:255',
                'descripcion3' => 'required|string|max:255',
                'usuario_id' => 'required|numeric',
            ];

            $validator = Validator::make( $request->all(), $rules, $messages = [
                'required' => 'El campo :attribute es requerido.',
                'numeric' => 'El campo :attribute debe ser númerico.',
                'string' => 'El campo :attribute debe ser tipo texto.',
                'max' => 'El campo :attribute excede el tamaño requerido((:max).',
                'date_format' => 'El campo :attribute debe tener formato fecha (Y-m-d) ó formato fecha hora (YYYY-MM-DD HH:mm:ss)',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                return $this->error("Error al actualizar el registro", $errors);
            }

            $bitacora = Bitacora::where('id', $id_bitacora)->first();

            if ($bitacora == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }
            $bitacora->fecha = $request->fecha;
            $bitacora->fecha_hora = $request->fecha_hora;
            $bitacora->evento_id = $request->evento_id;
            $bitacora->descripcion1 = $request->descripcion1;
            $bitacora->descripcion2 = $request->descripcion2;
            $bitacora->descripcion3 = $request->descripcion3;
            $bitacora->usuario_id = $request->usuario_id;
            $bitacora->save();

            $resource = new BitacoraResource($bitacora);

            return $this->success('Registro actualizado correctamente.', [
                'bitacora' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al actualizar el registro, error:{$th->getMessage()}.");
        }
        
    }


    public function destroy($id_bitacora, Request $request)
    {
        try {
            $bitacora = Bitacora::where('id', $id_bitacora)->first();
            if ($bitacora == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }
            $bitacora->delete();

            $resource = new BitacoraResource($bitacora);

            return $this->success('Registro borrado correctamente.', [
                'bitacora' => $resource
            ]);
            

        } catch (\Throwable $th) {
            return $this->error("Error al eliminar el regsitro, error:{$th->getMessage()}.");
        }
    }

    public function filtrarFecha($fecha, Request $request) {
        
        try {
            $bitacoras = Bitacora::where('fecha', $fecha)->paginate(20);
            if ($bitacoras == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }

            $bitacoras = BitacoraResource::collection($bitacoras)->additional([
                'status' => 'success',
                "message" => 'Información consultada correctamente.',
            ]);
            
            return $bitacoras;

        } catch (\Throwable $th) {
            return $this->error("Error al obtener los egistros, error:{$th->getMessage()}.");
        }
        
    }


    public function filtrarErrores(Request $request) {

        $bitacoras = Bitacora::where('evento_id', 7)->orWhere('evento_id', 9)->orWhere('evento_id', 19)->paginate(15);

        if ($bitacoras == NULL)
        {
            return $this->error("Error, NO se encontró el registro.");
        }

        $bitacoras = BitacoraResource::collection($bitacoras)->additional([
            'status' => 'success',
            "message" => 'Información consultada correctamente.',
        ]);
        
        return $bitacoras;
    }
}
