<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Http\Resources\DictamenResource;
use App\Models\Bitacora;
use App\Models\Dictamen;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;

class DictamenController extends Controller
{
    use ApiResponder;
    
    public function index()
    {
        $dictamenes = Dictamen::paginate(15);
        $dictamenes->load('cliente');
        $dictamenes->load('balance');

        $dictamenes = DictamenResource::collection($dictamenes)->additional([
            'status' => 'success',
            "message" => 'Información consultada correctamente.',
        ]);
        
        return $dictamenes;
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'rfcDictamen' => 'required|string|max:50',
                'loteDictamen' => 'required|string|max:255',
                'folioDictamen' => 'required|string|max:255',
                'fechaInicioDictamen' => 'required|date_format:Y-m-d',
                'fechaEmisionDictamen' => 'required|date_format:Y-m-d',
                'resultadoDictamen' => 'required|string|max:255',
                'densidad' => 'required|numeric',
                'volumen' => 'required|numeric',
                'cliente_id' => 'required|numeric',
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

            $dictamen = new Dictamen($request->all());
            $dictamen->save();
            $dictamen->load('cliente');
            $dictamen->load('balance');
            
            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'registró el dictamen ' . $dictamen->id;
            $bitacora->descripcion3 = '';
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();


            $resource = new DictamenResource($dictamen);

            return $this->success('Dictamen registrado correctamente.', [
                'dictamen' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al registrar el registro, error:{$th->getMessage()}.");
        }
    }

    public function show($id_dictamen)
    {
        try {
            $dictamen = Dictamen::where('id', $id_dictamen)->first();
            
            if ($dictamen == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }
            $dictamen->load('cliente');
            $dictamen->load('balance');

            $resource = new DictamenResource($dictamen);

            return $this->success('Información consultada correctamente.', [
                'dictamen' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al mostrar el registro, error:{$th->getMessage()}.");
        }
    }

    public function update(Request $request, $id_dictamen)
    {
        try {
            $rules = [
                'rfcDictamen' => 'required|string|max:50',
                'loteDictamen' => 'required|string|max:255',
                'folioDictamen' => 'required|string|max:255',
                'fechaInicioDictamen' => 'required|date_format:Y-m-d',
                'fechaEmisionDictamen' => 'required|date_format:Y-m-d',
                'resultadoDictamen' => 'required|string|max:255',
                'densidad' => 'required|numeric',
                'volumen' => 'required|numeric',
                'cliente_id' => 'required|numeric',
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

            $dictamen = Dictamen::where('id', $id_dictamen)->first();
            if ($dictamen == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }
            $dictamen->rfcDictamen = $request->rfcDictamen;
            $dictamen->loteDictamen = $request->loteDictamen;
            $dictamen->folioDictamen = $request->folioDictamen;
            $dictamen->fechaInicioDictamen = $request->fechaInicioDictamen;
            $dictamen->fechaEmisionDictamen = $request->fechaEmisionDictamen;
            $dictamen->resultadoDictamen = $request->resultadoDictamen;
            $dictamen->densidad = $request->densidad;
            $dictamen->volumen = $request->volumen;
            $dictamen->cliente_id = $request->cliente_id;
            $dictamen->balance_id = $request->balance_id;
            $dictamen->save();
            $cambios = '';
            foreach ($dictamen->getChanges() as $key => $value) {
                $cambios .= $key . ' - ' . $value . ' | ';
            }
            
            $dictamen->load('cliente');
            $dictamen->load('balance');

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'modificó el dictamen ' . $dictamen->id;
            $bitacora->descripcion3 = $cambios;
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();

            $resource = new DictamenResource($dictamen);

            return $this->success('Registro actualizado correctamente.', [
                'dictamen' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al actualizar el registro, error:{$th->getMessage()}.");
        }
    }

    public function destroy(Request $request, $id_dictamen)
    {
        try {
            $dictamen = Dictamen::where('id', $id_dictamen)->first();
            if ($dictamen == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }
            $dictamen->delete();

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'eliminó el dictamen ' . $dictamen->id;
            $bitacora->descripcion3 = '';
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();

            $resource = new DictamenResource($dictamen);

            return $this->success('Registro borrado correctamente.', [
                'dictamen' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al eliminar el registro, error:{$th->getMessage()}.");
        }
    }
}
