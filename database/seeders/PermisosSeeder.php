<?php

namespace Database\Seeders;

use App\Enums\PermisosGroupEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Permission::create(['name' => 'ver dashboard']);
        Permission::create(['name' => 'navegar usuarios']);
        Permission::create(['name' => 'editar usuarios']);
        Permission::create(['name' => 'borrar usuarios']);
        Permission::create(['name' => 'insertar usuarios']);
        Permission::create(['name' => 'navegar empresas']);
        Permission::create(['name' => 'editar empresas']);
        Permission::create(['name' => 'borrar empresas']);
        Permission::create(['name' => 'insertar empresas']);
        Permission::create(['name' => 'ver configuracion']);


        $role1 = Role::create(['name' => 'admin']);
        $role2 = Role::create(['name' => 'supervisor']);
        $role3 = Role::create(['name' => 'operador']);
        $role4 = Role::create(['name' => 'auditor']);

        $permissionsAll = (
            [
                'ver dashboard',
                'navegar usuarios',
                'editar usuarios',
                'borrar usuarios',
                'insertar usuarios',
                'navegar empresas',
                'editar empresas',
                'borrar empresas',
                'insertar empresas',
                'ver configuracion'
            ]
        );

        foreach ($permissionsAll as $permission) {
            $role1->givePermissionTo($permission);
        }

        
        $role2->givePermissionTo(['navegar usuarios']);
        $role2->givePermissionTo(['navegar empresas']);
        $role2->givePermissionTo(['editar empresas']);
        $role2->givePermissionTo(['borrar empresas']);
        $role2->givePermissionTo(['navegar empresas']);
        $role2->givePermissionTo(['insertar empresas']);
        $role2->givePermissionTo(['insertar empresas']);

        $role3->givePermissionTo(['navegar empresas']);
        
        $role4->givePermissionTo(['navegar usuarios']);
        $role4->givePermissionTo(['navegar empresas']);

        $user = User::where('id', 1)->first();

        $user->assingRole($role1);

    }
}
