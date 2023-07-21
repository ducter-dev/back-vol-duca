<?php

namespace App\Http\Controllers;

use App\Http\Resources\PermissionResource;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PermisoController extends Controller
{
    use ApiResponder;

    public function index()
    {
        $items = Permission::paginate(15);
        $permisos = PermissionResource::collection($items)->additional([
            'status' => 'success',
            "message" => 'Información consultada correctamente.',
        ]);
        
        return $permisos;
    
    }

    public function all()
    {
        $items = Permission::all();
        $permisos = PermissionResource::collection($items)->additional([
            'status' => 'success',
            "message" => 'Información consultada correctamente.',
        ]);
        
        return $permisos;
    
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $permiso = Permission::create($request->only('name', 'description', 'guard_name'));
            DB::commit();
            return $this->success('Registro guardado correctamente.', [
                'permiso' => new PermissionResource($permiso)
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            $data = json_encode($request->all());
            return $this->error('No se creó Permiso');
        }
    }

    public function show($id_permission)
    {   
        $permiso = Permission::where('id', $id_permission)->first();

        return $this->success('Registro consultado correctamente.', [
            'permiso' => new PermissionResource($permiso)
        ]);
    }

    public function update(Request $request, $id_permission)
    {
        DB::beginTransaction();
        try {
            $permiso = Permission::where('id', $id_permission)->first();
            $permiso->update($request->only('name', 'guard_name', 'description'));
            DB::commit();
            return $this->success('Registro actualizado correctamente.', [
                'permiso' => new PermissionResource($permiso)
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            $data = json_encode($request->all());
            return $this->error('No se actualizó Permiso');
        }
    }

    public function destroy(Permission $permiso)
    {
        if ($permiso->roles()->exists()) {
            return $this->error('Este registro no se puede eliminar porque tiene relación con otros.');
        }

        DB::beginTransaction();

        try {

            $permiso->delete();

            DB::commit();

            return $this->success('Registro eliminado correctamente.', [
                'permiso' => new PermissionResource($permiso)
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error("Error al eliminar el registro, error:{$e->getMessage()}.");
        }
    }

    public function restore(Permission $permission)
    {
        DB::beginTransaction();

        try {

            $permission->restore();

            DB::commit();

            return $this->success('Registro restaurado correctamente.');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error("Error al restaurar el registro, error:{$e->getMessage()}.");
        }
    }
}
