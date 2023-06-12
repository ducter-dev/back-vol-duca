<?php

namespace Database\Seeders;

use App\Enums\PermisosGroupEnum;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'sistema',
                'guard_name' => 'admin',
                'group_name' => PermisosGroupEnum::SISTEMA,
                'display_name' => 'Sistema',
                'description' => 'Con acceso al panel de administración del sistema y herramientas para desarrolladores.'
            ],
            [
                'nombre' => 'administrador',
                'guard_name' => 'admin',
                'group_name' => PermisosGroupEnum::SISTEMA,
                'display_name' => 'Administrador',
                'description' => 'Administrador del sitio con acceso al panel de administración del sistema.'
            ],
            [
                'nombre' => 'operador',
                'guard_name' => 'operador',
                'group_name' => PermisosGroupEnum::SISTEMA,
                'display_name' => 'Operador',
                'description' => 'Rol para el operador con acceso a las funcionalidades limitadas de los operadores.'
            ]
        ];

        foreach ($roles as $rol) {
            Role::create($rol);
        }
        
        
    }
}
