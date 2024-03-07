<?php

namespace App\Http\Controllers;

use App\Http\Resources\AlmacenResource;
use App\Models\Almacen;
use App\Models\Bitacora;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AlmacenController extends Controller
{
    use ApiResponder;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $almacenes = Almacen::paginate(15);
        $almacenes->load('cliente');

        $almacenes = AlmacenResource::collection($almacenes)->additional([
            'status' => 'success',
            "message" => 'Información consultada correctamente.',
        ]);

        return $almacenes;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $rules = [
                'cliente_id' => 'required|numeric',
                'inicio' => 'required|numeric',
                'volumen' => 'required|numeric',
                'fin' => 'required|numeric',
                'fecha' => 'required|date_format:Y-m-d'
            ];

            $validator = Validator::make( $request->all(), $rules, $messages = [
                'required' => 'El campo :attribute es requerido.',
                'numeric' => 'El campo :attribute debe ser númerico.',
                'string' => 'El campo :attribute debe ser tipo texto.',
                'max' => 'El campo :attribute excede el tamaño requerido (:max).',
                'date_format' => 'El campo :attribute debe tener formato fecha (Y-m-d) ó formato fecha hora (YYYY-MM-DD HH:mm:ss)',
            ]);

            if ($validator->fails()) {
                return response(['errors' => $validator->errors()->all()], 422);
            }

            $almacen = new Almacen($request->all());
            $almacen->save();
            $almacen->load('cliente');

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'agregó almacén ' . $almacen->id;
            $bitacora->descripcion3 = '';
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();

            $resource = new AlmacenResource($almacen);
            return $this->success('Almacén registrado correctamente.', [
                'almacen' => $resource
            ],201);
            

        }
        catch (\Exception $e) {
            return $this->error("Error al crear archivo, error:{$e->getMessage()}.");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Almacen  $almacen
     * @return \Illuminate\Http\Response
     */
    public function show($id_almacen)
    {
        try {
            $almacen = Almacen::where('id', $id_almacen)->first();

            if ($almacen == NULL) {
                return response()->json([
                    'data' => 'No se encontró la registro del préstamo de gas ingresado.'
                ],204);
            }

            $almacen->load('cliente');
            $resource = new AlmacenResource($almacen);

            return $this->success('Registro encontrado correctamente.', [
                'almacen' => $resource
            ],201);

        } catch (\Exception $e) {
            return $this->error("Error al crear registro, error:{$e->getMessage()}.");
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Almacen  $almacen
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id_almacen)
    {
        try {
            $rules = [
                'cliente_id' => 'required|numeric',
                'inicio' => 'required|numeric',
                'volumen' => 'required|numeric',
                'fin' => 'required|numeric',
                'fecha' => 'required|date_format:Y-m-d'
            ];

            $validator = Validator::make( $request->all(), $rules, $messages = [
                'required' => 'El campo :attribute es requerido.',
                'numeric' => 'El campo :attribute debe ser númerico.',
                'string' => 'El campo :attribute debe ser tipo texto.',
                'max' => 'El campo :attribute excede el tamaño requerido (:max).',
                'date_format' => 'El campo :attribute debe tener formato fecha (Y-m-d) ó formato fecha hora (YYYY-MM-DD HH:mm:ss)',
            ]);

            if ($validator->fails()) {
                return response(['errors' => $validator->errors()->all()], 422);
            }
            
            $almacen = Almacen::where('id', $id_almacen)->first();
            if ($almacen == NULL) {
                return response()->json([
                    'data' => 'No se encontró el registro con los datos ingresados.'
                ],202);
            }

            $almacen->cliente_id = $request->cliente_id;
            $almacen->fecha = $request->fecha;
            $almacen->inicio = $request->inicio;
            $almacen->volumen = $request->volumen;
            $almacen->fin = $request->fin;
            $almacen->save();
            $almacen->load('cliente');

            $cambios = '';
            foreach ($almacen->getChanges() as $key => $value) {
                $cambios .= $key . ' - ' . $value . ' | ';
            }

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'modificó el almacén ' . $almacen->id;
            $bitacora->descripcion3 = $cambios;
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();

            $resource = new AlmacenResource($almacen);

            return $this->success('Almacén actualizado correctamente.', [
                'almacen' => $resource
            ],201);
            

        } catch (\Exception $e) {
            return $this->error("Error al actualizar el registro, error:{$e->getMessage()}.");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Almacen  $almacen
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id_almacen)
    {
        try {
            $almacen = Almacen::where('id', $id_almacen)->first();
            $almacen->load('cliente');
            $almacen->delete();

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'eliminó el almacén ' . $almacen->id;
            $bitacora->descripcion3 = '';
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();

            $resource = new AlmacenResource($almacen);
            return $this->success('Almacén ' . $almacen->id . ' eliminado correctamente.', [
                'prestamo' => $resource
            ],202);

        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 501);
        }
    }
}
