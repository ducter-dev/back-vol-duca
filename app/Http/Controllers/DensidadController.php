<?php

namespace App\Http\Controllers;

use App\Http\Resources\DensidadResource;
use App\Models\Bitacora;
use App\Models\Densidad;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DensidadController extends Controller
{
    use ApiResponder;

    public function index()
    {
        $densidades = Densidad::paginate(15);
        $densidades->load('balance');

        $densidades = DensidadResource::collection($densidades)->additional([
            'status' => 'success',
            "message" => 'Información consultada correctamente.',
        ]);
        
        return $densidades;
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'densidad' => 'required|numeric',
                'balance_id' => 'required|numeric',
            ];

            $validator = Validator::make( $request->all(), $rules, $messages = [
                'required' => 'El campo :attribute es requerido.',
                'numeric' => 'El campo :attribute debe ser númerico.',
                'string' => 'El campo :attribute debe ser tipo texto.',
                'max' => 'El campo :attribute excede el tamaño requerido(:max).',
                'date_format' => 'El campo :attribute debe tener formato fecha (Y-m-d) ó formato fecha hora (YYYY-MM-DD HH:mm:ss)',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                return $this->error("Error al actualizar el registro", $errors);
            }

            $densidad = new Densidad($request->all());
            $densidad->save();
            $densidad->load('balance');

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = ' agregó la densidad ' . $densidad->id;
            $bitacora->descripcion3 = '';
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();

            $resource = new DensidadResource($densidad);

            return $this->success('Densidad registrada correctamente.', [
                'compuesto' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al registrar el registro, error:{$th->getMessage()}.");
        }
    }

    public function show($id_densidad)
    {
        try {
            $densidad = Densidad::where('id', $id_densidad)->first();
            
            if ($densidad == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }
            $densidad->load('balance');

            $resource = new DensidadResource($densidad);

            return $this->success('Información consultada correctamente.', [
                'compuesto' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al mostrar el registro, error:{$th->getMessage()}.");
        }
    }

    public function update(Request $request, $id_densidad)
    {
        try {
            $rules = [
                'densidad' => 'required|numeric',
                'balance_id' => 'required|numeric',
            ];

            $validator = Validator::make( $request->all(), $rules, $messages = [
                'required' => 'El campo :attribute es requerido.',
                'numeric' => 'El campo :attribute debe ser númerico.',
                'string' => 'El campo :attribute debe ser tipo texto.',
                'max' => 'El campo :attribute excede el tamaño requerido(:max).',
                'date_format' => 'El campo :attribute debe tener formato fecha (Y-m-d) ó formato fecha hora (YYYY-MM-DD HH:mm:ss)',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                return $this->error("Error al actualizar el registro", $errors);
            }

            $densidad = Densidad::where('id', $id_densidad)->first();
            if ($densidad == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }
            $densidad->densidad = $request->densidad;
            $densidad->balance_id = $request->balance_id;
            $densidad->save();
            $densidad->load('balance');

            $cambios = '';
            foreach ($densidad->getChanges() as $key => $value) {
                $cambios .= $key . ' - ' . $value . ' | ';
            }

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = ' modificó la densidad ' . $densidad->id . " ";
            $bitacora->descripcion3 = $cambios;
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();

            $resource = new DensidadResource($densidad);

            return $this->success('Registro actualizado correctamente.', [
                'densidad' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al actualizar el registro, error:{$th->getMessage()}.");
        }
    }

    public function destroy(Request $request, $id_densidad)
    {
        try {
            $densidad = Densidad::where('id', $id_densidad)->first();
            if ($densidad == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }
            $densidad->delete();
            $densidad->load('balance');

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = ' eliminó la densidad ' . $densidad->id . " ";
            $bitacora->descripcion3 = '';
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();

            $resource = new DensidadResource($densidad);

            return $this->success('Registro borrado correctamente.', [
                'densidad' => $resource
            ]);

        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 501);
        }
    }
}
