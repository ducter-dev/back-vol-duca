<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClienteResource;
use App\Models\Bitacora;
use App\Models\Cliente;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    use ApiResponder;
    
    public function index()
    {
        $clientes = Cliente::paginate(15);
        $clientes = ClienteResource::collection($clientes)->additional([
            'status' => 'success',
            "message" => 'Información consultada correctamente.',
        ]);
        
        return $clientes;
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'rfcCliente' => 'required|string|max:30',
                'nombreCliente' => 'required|string|max:255',
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

            $cliente = new Cliente($request->all());
            $cliente->save();
            
            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'agregó el cliente ' . $cliente->id;
            $bitacora->descripcion3 = '';
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();

            $resource = new ClienteResource($cliente);

            return $this->success('Cliente registrado correctamente.', [
                'cliente' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al registrar el registro, error:{$th->getMessage()}.");
        }
    }

    public function show($id_cliente)
    {
        try {
            $cliente = Cliente::where('id', $id_cliente)->first();

            if ($cliente == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }
            
            $resource = new ClienteResource($cliente);

            return $this->success('Información consultada correctamente.', [
                'cliente' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al mostrar el registro, error:{$th->getMessage()}.");
        }
    }

    public function update(Request $request, $id_cliente)
    {
        try {
            $rules = [
                'rfcCliente' => 'required|string|max:30',
                'nombreCliente' => 'required|string|max:255',
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

            $cliente = Cliente::where('id', $id_cliente)->first();

            if ($cliente == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }
            $cliente->rfcCliente = $request->rfcCliente;
            $cliente->nombreCliente = $request->nombreCliente;
            $cliente->save();

            $cambios = '';
            foreach ($cliente->getChanges() as $key => $value) {
                $cambios .= $key . ' - ' . $value . ' | ';
            }

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'modificó el cliente ' . $cliente->id;
            $bitacora->descripcion3 = $cambios;
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();

            $resource = new ClienteResource($cliente);

            return $this->success('Registro actualizado correctamente.', [
                'cliente' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al actualizar el registro, error:{$th->getMessage()}.");
        }
    }

    public function destroy(Request $request, $id_cliente)
    {
        try {
            $cliente = Cliente::where('id', $id_cliente)->first();
            if ($cliente == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }
            $cliente->delete();

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'eliminó el cliente ' . $cliente->id;
            $bitacora->descripcion3 = '';
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();

            $resource = new ClienteResource($cliente);

            return $this->success('Registro borrado correctamente.', [
                'cliente' => $resource
            ]);


        } catch (\Throwable $th) {
            return $this->error("Error al eliminar el registro, error:{$th->getMessage()}.");
        }
    }
}
