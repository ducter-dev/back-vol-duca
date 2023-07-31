<?php

namespace App\Http\Controllers;

use App\Http\Resources\RolResources;
use App\Models\Rol;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class RolController extends Controller
{
    use ApiResponder;
    
    public function index()
    {
        $roles = Role::paginate(15);
        $roles = RolResources::collection($roles)->additional([
            'status' => 'success',
            "message" => 'Información consultada correctamente.',
        ]);
        
        return $roles;

    }

    public function all()
    {
        $roles = Role::all(15);
        $roles = RolResources::collection($roles)->additional([
            'status' => 'success',
            "message" => 'Información consultada correctamente.',
        ]);
        
        return $roles;

    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            //dd($request->only('name', 'guard_name'));
            $role = Role::create($request->only('name', 'guard_name'));
            $role->permissions()->sync($request->get('permissions'));
            dd($role);
            DB::commit();
            return $this->success('Registro guardado correctamente.', [
                'rol' => new RolResources($role)
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error("Error al eliminar el crear, error:{$e->getMessage()}.");
        }
        
    }

    public function show(Role $role)
    {
        $role->load('permissions');

        return $this->success('Registro consultado correctamente.', [
            'rol' => new RolResources($role)
        ]);
    }

    public function update(Request $request, Role $role)
    {
        DB::beginTransaction();
        try {
            $role->update($request->only('name', 'guard_name'));

            $role->permissions()->sync($request->get('permissions'));

            DB::commit();

            return $this->success('Registro actualizado correctamente.', [
                'rol' => new RolResources($role)
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            $data = json_encode($request->all());
            return $this->error('No se actualizó el Rol');
        }
    }

    public function destroy(Role $role)
    {
        if ($role->name == 'administrador') {
            return $this->error('Este registro no se puede eliminar porque tiene relación con otros.');
        }

        DB::beginTransaction();

        try {

            $role->delete();

            DB::commit();

            $resource = new RolResources($role);

            return $this->success('Registro borrado correctamente.', [
                'rol' => $resource
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error("Error al eliminar el registro, error:{$e->getMessage()}.");
        }
    }

    public function restoreRol($idRole)
    {
        DB::beginTransaction();
        try {
            $role = Role::withTrashed()->where('id', $idRole)->first();

            $role->restore();

            DB::commit();

            $resource = new RolResources($role);

            return $this->success('Registro restaurado correctamente.', [
                'rol' => $resource
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error("Error al restaurar el registro, error:{$e->getMessage()}.");
        }
    }
}
