<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    
    public function index()
    {
        $users = User::all();
        $users->load('bitacoras');
        $users->load('empresa');
        $users->load('perfil');
        $users->load('contrasenas');
        
        return response()->json([
            'data' => $users
        ]);
    }

    public function register(Request $request)
    {
        $rules = [
            'nombre' => 'required|string|max:50',
            'usuario' => 'required|string|max:50|unique:usuarios',
            #'perfil_id' => 'required|numeric|min:1',
            #'empresa_id' => 'required|numeric|min:1',
            'correo' => 'required|string|email|max:255|unique:usuarios',
            'contrasena' => 'required|string|between:8,50|confirmed',
            'estado' => 'required|numeric|min:1',
        ];

        $validator = Validator::make( $request->all(), $rules, $messages = [
            'required' => 'El campo :attribute es requerido.',
            'numeric' => 'El campo :attribute debe ser númerico.',
            'string' => 'El campo :attribute debe ser tipo texto.',
            'max' => 'El campo :attribute excede el tamaño requerido (:max).',
            'date_format' => 'El campo :attribute debe tener formato fecha (Y-m-d) ó formato fecha hora (YYYY-MM-DD HH:mm:ss)',
            'email' => 'El campo email no cumple con el formato estándar.',
            'unique' => 'El campo :attribute no se puede utilizar.',
            'between' => 'El campo :attribute debe tener entre 8 y 50 caracteres.',
            'confirmed' => 'El campo :attribute debe ser confirmada.',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }

        $request['contrasena'] = Hash::make($request['contrasena']);
        $user = new User();
        $user->nombre = $request->nombre;
        $user->usuario = $request->usuario;
        #$user->perfil_id = $request->perfil_id;
        #$user->empresa_id = $request->empresa_id;
        $user->correo= $request->correo;
        $user->contrasena = $request->contrasena;
        #$user->estado = $request->estado;
        $user->save();

        return response()->json([
            'data' => $user,
        ], 201);
    }

    public function show($idUser)
    {
        try {
            $user = User::where('id', $idUser)->first();

            if ($user == null) {
                return response()->json([
                    'data' => 'No se encontró el usuario.'
                ],202);
            }

            $user->load('bitacoras');
            $user->load('empresa');
            #$user->load('perfil');

            return response()->json([
                'data' => $user
            ],202);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 501);
        }
    }

    public function update(Request $request, $idUser)
    {
        try {
            $rules = [
                'nombre' => 'required|string|max:50',
                'usuario' => 'required|string|max:50',
                'correo' => 'required|string|email|max:255|',
                /* 'perfil_id' => 'required|numeric|min:1',
                'empresa_id' => 'required|numeric|min:1',
                'estado' => 'required|numeric|min:1', */
            ];
    
            $validator = Validator::make( $request->all(), $rules, $messages = [
                'required' => 'El campo :attribute es requerido.',
                'numeric' => 'El campo :attribute debe ser númerico.',
                'string' => 'El campo :attribute debe ser tipo texto.',
                'max' => 'El campo :attribute excede el tamaño requerido (:max).',
                'date_format' => 'El campo :attribute debe tener formato fecha (Y-m-d) ó formato fecha hora (YYYY-MM-DD HH:mm:ss)',
                'email' => 'El campo email no cumple con el formato estándar.',
                'unique' => 'El campo :attribute no se puede utilizar.',
                'between' => 'El campo :attribute debe tener entre 8 y 50 caracteres.',
                'confirmed' => 'El campo :attribute debe ser confirmada.',
            ]);

            if ($validator->fails()) {
                return response(['errors' => $validator->errors()->all()], 422);
            }

            $user = User::where('id', $idUser)->first();
            
            if ($user == null) {
                return response()->json([
                    'data' => 'No se encontró el usuario.'
                ],202);
            }

            $user->nombre = $request->nombre;
            $user->usuario = $request->usuario;
            $user->correo= $request->correo;
            /* $user->perfil_id = $request->perfil_id;
            $user->empresa_id = $request->empresa_id;
            $user->estado = $request->estado; */
            $user->save();
            
            return response()->json([
                'data' => $user
            ],202);

        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 501);
        }
    }

    public function destroy($idUser, Request $request)
    {
        try {
            $user = User::where('id', $idUser)->first();
            $user->delete();
            
            return response()->json([
                'data' => $user
            ],202);

        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 501);
        }
    }

    public function login(Request $request)
    {
        $nombreUsuario = $request->usuario;
        /* $userBloqueo = User::where('usuario', $nombreUsuario)->first(); */

        /* if ($userBloqueo) {
            $bloqueo = Bloqueado::where('usuario_id', $userBloqueo->id)->orderBy('id', 'desc')->first();
            $fecha_hora_actual = date("d-m-Y H:i:s");
            if ($bloqueo) {
                if (strtotime($bloqueo->fecha_desbloqueo) > strtotime($fecha_hora_actual)) {
                    return response()->json([
                        'message' => 'Usuario bloqueado, intente en 15 minutos.',
                    ], 401);
                }
            } 
        } */

        $credentials = $request->validate([
            $this->username() => ['required'],
            'password' => ['required']
        ]);

        $credentials = request(['usuario', 'password']);
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'No autorizado.'
            ], 401);
        }
        
        $user = $request->user();

        if ($user->estado >= 2) {
            return response()->json([
                'message' => 'Usuario inactivo por sistema.'
            ], 401);
        }

        

        $accessToken = $user->createToken('authToken')->accessToken;

        /* $bitacora = new Bitacora();
        $bitacora->fecha = date('Y-m-d');
        $bitacora->fecha_hora = date('Y-m-d H:i:s');
        $bitacora->tipoevento_id = 1;
        $bitacora->descripcion1 = 'El usuario ' . $user->usuario;
        $bitacora->descripcion2 = 'inició sesión';
        $bitacora->descripcion3 = 'desde la ip => ' . $request->ip();
        $bitacora->usuario_id = $user->id;
        $bitacora->save();

        $hoy = date('Y-m-d H:i:s');
        $contrasenaUser = ContrasenaUser::where('usuario_id', $user->id)->where('estado', 1)->orderBy('id', 'DESC')->first();
        if (!$contrasenaUser) {
            $caducidad = strtotime('+2 months', strtotime($hoy));
            $caducidad = date('Y-m-d H:i:s', $caducidad);

            $request['password'] = Hash::make($request['password']);
            $user->contrasena = $request['contrasena'];
            $contraUser = new ContrasenaUser();
            $contraUser->contrasena = $request['password'];
            $contraUser->caducidad = $caducidad;
            $contraUser->ultimoAcceso = date('Y-m-d H:i:s');
            $contraUser->estado = 1;
            $contraUser->usuario_id = $user->id;
            $user->contrasenas()->save($contraUser);
        } else {
            // Retornar si está caducada
            if ($hoy >= $contrasenaUser->caducidad) {
                return response()->json([
                    'message'=> 'La contraseña ha caducado',
                    'user' => $user
                ],401);
            }
            $contrasenaUser->ultimoAcceso = $hoy;
            $contrasenaUser->save();
        } */

        return response(['user' => Auth::user(), 'access_token' => $accessToken]);
    }

    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();
        /* $bitacora = new Bitacora();
        $bitacora->fecha = date('Y-m-d');
        $bitacora->fecha_hora = date('Y-m-d H:i:s');
        $bitacora->tipoevento_id = 1;
        $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
        $bitacora->descripcion2 = 'cerró sesión';
        $bitacora->descripcion3 = 'desde la ip => ' . $request->ip();
        $bitacora->usuario_id = $request->user()->id;
        $bitacora->save(); */

        $response = 'Ha cerrado sesión correctamente.';
        return response()->json([
            'message'=> $response,
        ],201);
    }

    public function username()
    {
        return 'usuario';
    }
}
