<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductoResource;
use App\Models\Bitacora;
use App\Models\Producto;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{
    use ApiResponder;
    
    public function index()
    {
        $productos = Producto::all();

        $productos = ProductoResource::collection($productos)->additional([
            'status' => 'success',
            "message" => 'Información consultada correctamente.',
        ]);
        
        return $productos;
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'descripcion' => 'required|string|max:255',
                'clave' => 'required|string|max:255',
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

            $producto = new Producto($request->all());
            $producto->save();

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'agregó el producto ' . $producto->id;
            $bitacora->descripcion3 = '';
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();

            $resource = new ProductoResource($producto);

            return $this->success('Producto registrado correctamente.', [
                'producto' => $resource
            ]);
        } catch (\Throwable $th) {
            return $this->error("Error al registrar el registro, error:{$th->getMessage()}.");
        }
    }

    public function show($id_producto)
    {
        try {
            $producto = Producto::where('id', $id_producto)->first();
            if ($producto == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }
            #$producto->productosCompuestos;

            $resource = new ProductoResource($producto);

            return $this->success('Información consultada correctamente.', [
                'producto' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al mostrar el registro, error:{$th->getMessage()}.");
        }
    }

    public function update(Request $request, $id_producto)
    {
        try {
            $rules = [
                'descripcion' => 'required|string|max:255',
                'clave' => 'required|string|max:255',
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

            $producto = Producto::where('id', $id_producto)->first();
            if ($producto == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }
            $producto->descripcion = $request->descripcion;
            $producto->clave = $request->clave;
            $producto->save();

            $cambios = '';
            foreach ($producto->getChanges() as $key => $value) {
                $cambios .= $key . ' - ' . $value . ' | ';
            }

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'modificó el producto ' . $producto->id;
            $bitacora->descripcion3 = $cambios;
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();

            $resource = new ProductoResource($producto);

            return $this->success('Registro actualizado correctamente.', [
                'producto' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al actualizar el registro, error:{$th->getMessage()}.");
        }
    }

    public function destroy(Request $request, $id_producto)
    {
        try {
            $producto = Producto::where('id', $id_producto)->first();
            if ($producto == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }
            $producto->delete();

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'eliminó el producto ' . $producto->id;
            $bitacora->descripcion3 = '';
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();

            $resource = new ProductoResource($producto);

            return $this->success('Registro borrado correctamente.', [
                'producto' => $resource
            ]);
            
        } catch (\Throwable $th) {
            return $this->error("Error al eliminar el registro, error:{$th->getMessage()}.");
        }
    }
}
