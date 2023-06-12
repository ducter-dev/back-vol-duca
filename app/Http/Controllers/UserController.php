<?php

namespace App\Http\Controllers;

use App\Events\LoginEvent;
use App\Events\LogoutEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;
use App\Http\Requests\AuthenticatedSessionRequest;
use App\Traits\ApiResponder;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    use ApiResponder;
    
    public function index()
    {
        $users = User::all();
        #$users->load('bitacoras');
        #$users->load('perfil');
        #$users->load('contrasenas');
        $users = UserResource::collection($users)->additional([
            'status' => 'success',
            "message" => 'Información consultada correctamente.',
        ]);
        
        return $users;
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

            #$user->load('bitacoras');
            #$user->load('empresa');
            #$user->load('perfil');

            return response()->json([
                'data' => $user
            ],200);
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

    public function login(AuthenticatedSessionRequest $request)
    {
        $user = User::firstWhere('usuario', request('usuario'));
        $tokenName = null;

        if (isset($user->id)) {
            $model = $user;
            $tokenName = 'user_auth_token';
        } else {
            throw ValidationException::withMessages([
                'email' => 'Estas credenciales no coinciden con nuestros registros.'
            ]);
        }

        if (Hash::check(request('password'), $user->contrasena)) {

            try {
                $tz = config('app.timezone');
                $now = Carbon::now($tz);
                $minutesToAdd = config('sanctum.expiration');
                //dd($user->tokens());
                /* if ($user->tokens()->whereTime('expires_at', '>', $now->format('YmdHis'))->count() > 0) {
                    throw ValidationException::withMessages([
                        'account' => 'Actualmente tiene una sesión activa.'
                    ]);
                } */
                
                $token = $user->createToken($tokenName, $user->getAllPermissionsSlug()->toArray(), $now->addMinutes($minutesToAdd));
                //dd($token);
                //dd($token->plainTextToken);
                $user->access_token = $token->plainTextToken;
                $user->sign_in_at =  $token->accessToken->created_at->format('Y-m-d H:i:s');
                $user->sign_in_expires_at =  $token->accessToken->expires_at->format('Y-m-d H:i:s');
    
                event(new LoginEvent($user));
    
                $resource = new UserResource($user);
                return $this->success('Inicio de sesión exitoso.', [
                    'user' => $resource
                ]);
            } catch (\Exception $e) {
                return $this->error("Error al inicair sesión, error:{$e->getMessage()}.");
            }
        } else {
            throw ValidationException::withMessages([
                'password' => 'No matchea las passowrds'
            ]);
        }
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
