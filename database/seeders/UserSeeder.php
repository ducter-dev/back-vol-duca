<?php

namespace Database\Seeders;

use App\Models\User;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
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
        ])->assignRole('administrador');

        $user2 = User::create([
            'nombre' =>  'supervisor', 
            'usuario' =>  'sup3rv1s0r', 
            'correo' =>  'supervisor@ducter.com.mx', 
            'contrasena' =>  Hash::make('secret'), 
        ])->assignRole('supervisor');

        $user3 = User::create([
            'nombre' =>  'operador', 
            'usuario' =>  '0p3r4d0r', 
            'correo' =>  'operador@ducter.com.mx', 
            'contrasena' =>  Hash::make('secret'), 
        ])->assignRole('operador');

        $user4 = User::create([
            'nombre' =>  'audirtor', 
            'usuario' =>  '4ud1t04', 
            'correo' =>  'auditor@ducter.com.mx', 
            'contrasena' =>  Hash::make('secret'), 
        ])->assignRole('auditor');


    }
}