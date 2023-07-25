<?php

namespace App\Http\Controllers;

use App\Http\Resources\RevisionResource;
use App\Models\Bitacora;
use App\Models\Revision;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RevisionController extends Controller
{
    use ApiResponder;

    public function index()
    {
        $revisiones = Revision::paginate(15);

        $revisiones = RevisionResource::collection($revisiones)->additional([
            'status' => 'success',
            "message" => 'Información consultada correctamente.',
        ]);
        
        return $revisiones;
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'descripcion' => 'required|string|max:255',
                'inicio' => 'required|date_format:Y-m-d H:i:s',
                'periodo' => 'required|numeric',
                'proxima' => 'required|date_format:Y-m-d H:i:s',
                'estado' => 'required|numeric',
            ];

            $validator = Validator::make( $request->all(), $rules, $messages = [
                'required' => 'El campo :attribute es requerido.',
                'numeric' => 'El campo :attribute debe ser númerico.',
                'string' => 'El campo :attribute debe ser tipo texto.',
                'max' => 'El campo :attribute excede el tamaño requerido (:max).',
                'date_format' => 'El campo :attribute debe tener formato fecha (Y-m-d) ó formato fecha hora (YYYY-MM-DD HH:mm:ss)',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                return $this->error("Error al registrar el registro", $errors);
            }

            $revision = new Revision($request->all());
            $revision->save();

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'agregó revisión ' . $revision->id;
            $bitacora->descripcion3 = '';
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();


            $resource = new RevisionResource($revision);

            return $this->success('Revisión registrado correctamente.', [
                'revision' => $resource
            ]);


        } catch (\Throwable $th) {
            return $this->error("Error al registrar el registro, error:{$th->getMessage()}.");
        }
    }

    public function show($id_revision)
    {
        try {
            $revision = Revision::where('id', $id_revision)->first();
            
            if ($revision == NULL) {
                return $this->error("Error, NO se encontró el registro.");
            }

            $resource = new RevisionResource($revision);

            return $this->success('Información consultada correctamente.', [
                'revision' => $resource
            ]);
            
        } catch (\Throwable $th) {
            return $this->error("Error al mostrar el registro, error:{$th->getMessage()}.");
        }
    }

    public function update(Request $request, $id_revision)
    {
        try {
            $rules = [
                'descripcion' => 'required|string|max:255',
                'inicio' => 'required|date_format:Y-m-d H:i:s',
                'periodo' => 'required|numeric',
                'proxima' => 'required|date_format:Y-m-d H:i:s',
                'estado' => 'required|numeric',
            ];

            $validator = Validator::make( $request->all(), $rules, $messages = [
                'required' => 'El campo :attribute es requerido.',
                'numeric' => 'El campo :attribute debe ser númerico.',
                'string' => 'El campo :attribute debe ser tipo texto.',
                'max' => 'El campo :attribute excede el tamaño requerido (:max).',
                'date_format' => 'El campo :attribute debe tener formato fecha (Y-m-d) ó formato fecha hora (YYYY-MM-DD HH:mm:ss)',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                return $this->error("Error al actualizar el registro", $errors);
            }

            $revision = Revision::where('id', $id_revision)->first();
            
            if ($revision == NULL) {
                return $this->error("Error, NO se encontró el registro.");
            }
            
            $revision->descripcion = $request->descripcion;
            $revision->inicio = $request->inicio;
            $revision->periodo = $request->periodo;
            $revision->proxima = $request->proxima;
            $revision->estado = $request->estado;
            $revision->save();

            $cambios = '';
            foreach ($revision->getChanges() as $key => $value) {
                $cambios .= $key . ' - ' . $value . ' | ';
            }

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'modificó la revisión ' . $revision->id;
            $bitacora->descripcion3 = $cambios;
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();
            
            $resource = new RevisionResource($revision);

            return $this->success('Registro actualizado correctamente.', [
                'revision' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al actualizar el registro, error:{$th->getMessage()}.");
        }
    }

    public function destroy(Request $request, $id_revision)
    {
        try {
            $revision = Revision::where('id', $id_revision)->first();
            if ($revision == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }
            $revision->delete();

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'eliminó la revisión ' . $revision->id;
            $bitacora->descripcion3 = '';
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();

            $resource = new RevisionResource($revision);

            return $this->success('Registro borrado correctamente.', [
                'revision' => $resource
            ]);
        } catch (\Throwable $th) {
            return $this->error("Error al eliminar el registro, error:{$th->getMessage()}.");
        }
    }
}
