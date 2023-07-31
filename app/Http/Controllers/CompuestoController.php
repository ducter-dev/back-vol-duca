<?php

namespace App\Http\Controllers;

use App\Http\Resources\CompuestoResource;
use App\Models\Bitacora;
use App\Models\Compuesto;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompuestoController extends Controller
{
    use ApiResponder;
    
    public function index()
    {
        $compuestos = Compuesto::paginate(15);
        $compuestos = CompuestoResource::collection($compuestos)->additional([
            'status' => 'success',
            "message" => 'Información consultada correctamente.',
        ]);
        
        return $compuestos;
    }

    public function all()
    {
        $compuestos = Compuesto::all();
        $compuestos = CompuestoResource::collection($compuestos)->additional([
            'status' => 'success',
            "message" => 'Información consultada correctamente.',
        ]);
        
        return $compuestos;
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'descripcion' => 'required|string|max:255',
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

            $compuesto = new Compuesto($request->all());
            $compuesto->save();

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'agregó el compuesto ' . $compuesto->id;
            $bitacora->descripcion3 = '';
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();

            $resource = new CompuestoResource($compuesto);

            return $this->success('compuesto registrado correctamente.', [
                'compuesto' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al registrar el registro, error:{$th->getMessage()}.");
        }
    }

    public function show($id_compuesto)
    {
        try {
            $compuesto = Compuesto::where('id', $id_compuesto)->first();

            if ($compuesto == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }
            
            $resource = new CompuestoResource($compuesto);

            return $this->success('Información consultada correctamente.', [
                'compuesto' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al mostrar el registro, error:{$th->getMessage()}.");
        }
    }

    public function update(Request $request, $id_compuesto)
    {
        try {
            $rules = [
                'descripcion' => 'required|string|max:255',
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

            $compuesto = Compuesto::where('id', $id_compuesto)->first();
            
            if ($compuesto == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }
            
            $compuesto->descripcion = $request->descripcion;
            #$compuesto->load('productos');
            $compuesto->save();

            $cambios = '';
            foreach ($compuesto->getChanges() as $key => $value) {
                $cambios .= $key . ' - ' . $value . ' | ';
            }

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'modificó el compuesto ' . $compuesto->id;
            $bitacora->descripcion3 = $cambios;
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();

            $resource = new CompuestoResource($compuesto);

            return $this->success('Registro actualizado correctamente.', [
                'compuesto' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al actualizar el registro, error:{$th->getMessage()}.");
        }
    }

    public function destroy(Request $request, $id_compuesto)
    {
        try {
            $compuesto = Compuesto::where('id', $id_compuesto)->first();

            if ($compuesto == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }

            $compuesto->delete();

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'eliminó el compuesto ' . $compuesto->id;
            $bitacora->descripcion3 = '';
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();

            $resource = new CompuestoResource($compuesto);

            return $this->success('Registro borrado correctamente.', [
                'compuesto' => $resource
            ]);
            
        } catch (\Throwable $th) {
            return $this->error("Error al eliminar el registro, error:{$th->getMessage()}.");
        }
    }
}
