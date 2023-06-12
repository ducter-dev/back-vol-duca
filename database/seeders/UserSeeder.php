<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'nombre' => 'Admin',
            'usuario' => 'Admin2019$',
            'correo' => 'admin@duca.com',
            'correo_verificado' => now(),
            'contrasena' => Hash::make('secret')
        ]);

        $UserRole = Role::query()->where('nombre', config('permisos.admin_role'))->get();

        $user->roles()->sync($UserRole);

    }
}