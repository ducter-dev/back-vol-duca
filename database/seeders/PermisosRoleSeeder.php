<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\Hash;

class PermisosRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $user1 = User::create([
            'nombre' =>  'Admin', 
            'usuario' =>  '4dM1n$232o', 
            'correo' =>  'michel.morales@ducter.com.mx', 
            'contrasena' =>  Hash::make('secret'), 
        ]);

        $user2 = User::create([
            'nombre' =>  'supervisor', 
            'usuario' =>  'sup3rv1s0r', 
            'correo' =>  'supervisor@ducter.com.mx', 
            'contrasena' =>  Hash::make('secret'), 
        ]);

        $user3 = User::create([
            'nombre' =>  'operador', 
            'usuario' =>  '0p3r4d0r', 
            'correo' =>  'operador@ducter.com.mx', 
            'contrasena' =>  Hash::make('secret'), 
        ]);

        $user4 = User::create([
            'nombre' =>  'audirtor', 
            'usuario' =>  '4ud1t04', 
            'correo' =>  'auditor@ducter.com.mx', 
            'contrasena' =>  Hash::make('secret'), 
        ]);

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

        $user1->assignRole($role1);
        $user2->assignRole($role2);
        $user3->assignRole($role3);
        $user4->assignRole($role4);
        
    }
}
