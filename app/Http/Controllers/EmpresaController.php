<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmpresaController extends Controller
{
    public function index()
    {
        $empresas = Empresa::all();
        $empresas->load('tanques');
        $empresas->load('productos');
        $empresas->load('productoOmision');
        return response()->json([
            'data' => $empresas,
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'version' => 'required|numeric',
                'descripcion' => 'required|string|max:255',
                'rfc_contribuyente' => 'required|string|max:50',
                'rfc_representante' => 'required|string|max:50',
                'proveedor' => 'required|string|max:50',
                'tipo_caracter' => 'required|string|max:50',
                'modalidad_permiso' => 'required|string|max:50',
                'num_permiso' => 'required|string|max:50',
                'clave_instalacion' => 'required|string|max:50',
                'descripcion_instalacion' => 'required|string|max:255',
                'geolocalizacion_latitud' => 'required|string|max:100',
                'geolocalizacion_longitud' => 'required|string|max:100',
                'numero_tanques' => 'required|numeric',
                'numero_ductos_entradas_salidas' => 'required|numeric',
                'numero_ductos_distribucion' => 'required|numeric',
                'fecha_hora_corte' => 'required|date_format:Y-m-d H:i:s',
                'producto_omision' => 'required|numeric',
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
            
            $empresa = new Empresa($request->all());
            $empresa->save();

            return response()->json([
                'data' => $empresa
            ],201);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 501);
        }
    }

    public function show($idEmpresa)
    {
        try {
            $empresa = Empresa::where('id', $idEmpresa)->first();

            if ($empresa == NULL)
            {
                return response()->json([
                    'data' => 'No se encontró la empresa seleccionada.'
                ],202);
            }
            $empresa->load('tanques');
            $empresa->load('productos');

            return response()->json([
                'data' => $empresa
            ],202);

        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 501);
        }
    }

    public function update(Request $request, $idEmpresa)
    {
        try {
            $rules = [
                'version' => 'required|numeric',
                'descripcion' => 'required|string|max:255',
                'rfc_contribuyente' => 'required|string|max:50',
                'rfc_representante' => 'required|string|max:50',
                'proveedor' => 'required|string|max:50',
                'tipo_caracter' => 'required|string|max:50',
                'modalidad_permiso' => 'required|string|max:50',
                'num_permiso' => 'required|string|max:50',
                'clave_instalacion' => 'required|string|max:50',
                'descripcion_instalacion' => 'required|string|max:255',
                'geolocalizacion_latitud' => 'required|string|max:100',
                'geolocalizacion_longitud' => 'required|string|max:100',
                'numero_tanques' => 'required|numeric',
                'numero_ductos_entradas_salidas' => 'required|numeric',
                'numero_ductos_distribucion' => 'required|numeric',
                'fecha_hora_corte' => 'required|date_format:Y-m-d H:i:s',
                'producto_omision' => 'required|numeric',
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

            $empresa = Empresa::where('id', $idEmpresa)->first();
            if ($empresa == NULL)
            {
                return response()->json([
                    'data' => 'No se encontró la empresa seleccionada.'
                ],202);
            }
            $empresa->version = $request->version;
            $empresa->descripcion = $request->descripcion;
            $empresa->rfc_contribuyente = $request->rfc_contribuyente;
            $empresa->rfc_representante = $request->rfc_representante;
            $empresa->proveedor = $request->proveedor;
            $empresa->tipo_caracter = $request->tipo_caracter;
            $empresa->modalidad_permiso = $request->modalidad_permiso;
            $empresa->num_permiso = $request->num_permiso;
            $empresa->clave_instalacion = $request->clave_instalacion;
            $empresa->descripcion_instalacion = $request->descripcion_instalacion;
            $empresa->geolocalizacion_latitud = $request->geolocalizacion_latitud;
            $empresa->geolocalizacion_longitud = $request->geolocalizacion_longitud;
            $empresa->numero_tanques = $request->numero_tanques;
            $empresa->numero_ductos_entradas_salidas = $request->numero_ductos_entradas_salidas;
            $empresa->numero_ductos_distribucion = $request->numero_ductos_distribucion;
            $empresa->fecha_hora_corte = $request->fecha_hora_corte;
            $empresa->producto_omision = $request->producto_omision;
            $empresa->save();

            $cambios = '';
            foreach ($empresa->getChanges() as $key => $value) {
                $cambios .= $key . ' - ' . $value . ' | ';
            }

            return response()->json([
                'data' => $empresa
            ],202);

        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 501);
        }
    }

    public function destroy($idEmpresa, Request $request)
    {
        try {
            $empresa = Empresa::where('id', $idEmpresa)->first();
            $empresa->delete();

            return response()->json([
                'data' => $empresa
            ],202);
            
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 501);
        }
    }
}
