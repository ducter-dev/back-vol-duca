<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Prestamo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PrestamoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($fecha)
    {
        $prestamos = Prestamo::where('fecha', $fecha)->get();
        $prestamos->load('clienteCompra');
        $prestamos->load('clienteVenta');
        return response()->json([
            'data' => $prestamos,
        ], 200);
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
                'cliente_id_c' => 'required|numeric',
                'cliente_id_v' => 'required|numeric',
                'cantidad' => 'required|numeric',
                'fecha' => 'required|date_format:Y-m-d',
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

            $prestamo = new Prestamo($request->all());
            $prestamo->save();
            $prestamo->load('clienteCompra');
            $prestamo->load('clienteVenta');

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'agregó préstamo ' . $prestamo->id;
            $bitacora->descripcion3 = '';
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();


            return response()->json([
                'data' => $prestamo
            ],201);

        }
        catch (\Exception $e) {
            return response()->json($e->getMessage(), 501);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Prestamo  $prestamo
     * @return \Illuminate\Http\Response
     */
    public function show($id_prestamo)
    {
        try {
            $prestamo = Prestamo::where('id', $id_prestamo)->first();

            if ($prestamo == NULL) {
                return response()->json([
                    'data' => 'No se encontró la registro del préstamo de gas ingresado.'
                ],204);
            }

            $prestamo->load('clienteCompra');
            $prestamo->load('clienteVenta');

            return response()->json([
                'data' => $prestamo
            ],200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 501);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Prestamo  $prestamo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id_prestamo)
    {
        try {
            $rules = [
                'cliente_id_c' => 'required|numeric',
                'cliente_id_v' => 'required|numeric',
                'cantidad' => 'required|numeric',
                'fecha' => 'required|date_format:Y-m-d',
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
            
            $prestamo = Prestamo::where('id', $id_prestamo)->first();
            if ($prestamo == NULL) {
                return response()->json([
                    'data' => 'No se encontró el registro con los datos ingresados.'
                ],202);
            }

            $prestamo->cliente_id_c = $request->cliente_id_c;
            $prestamo->cliente_id_v = $request->cliente_id_v;
            $prestamo->cantidad = $request->cantidad;
            $prestamo->fecha = $request->fecha;
            $prestamo->save();
            $prestamo->load('clienteCompra');
            $prestamo->load('clienteVenta');

            $cambios = '';
            foreach ($prestamo->getChanges() as $key => $value) {
                $cambios .= $key . ' - ' . $value . ' | ';
            }

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'modificó el préstamo ' . $prestamo->id;
            $bitacora->descripcion3 = $cambios;
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();
            
            return response()->json([
                'data' => $prestamo
            ],201);

        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 501);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Prestamo  $prestamo
     * @return \Illuminate\Http\Response
     */
    public function destroy($id_prestamo, Request $request)
    {
        try {
            $prestamo = Prestamo::where('id', $id_prestamo)->first();
            $prestamo->delete();

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'eliminó el préstamo ' . $prestamo->id;
            $bitacora->descripcion3 = '';
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();

            return response()->json([
                'data' => $prestamo
            ],202);

        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 501);
        }
    }
}
