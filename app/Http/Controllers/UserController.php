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
use App\Http\Resources\AuthResource;
use App\Mail\RegisterUser;
use App\Traits\ApiResponder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\PersonalAccessToken;

class UserController extends Controller
{
    use ApiResponder;
    
    public function index()
    {
        $users = User::paginate(15);
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
            'correo' => 'required|string|email|max:255|unique:usuarios',
            'rol' => 'required|numeric|min:1',
            'contrasena' => 'required|string|between:8,50|confirmed',
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
            $errors = $validator->errors()->all();
            return $this->error("Error al insertar el registro", $errors);
        }
        $passwordPlain = $request['contrasena'];
        $request['contrasena'] = Hash::make($request['contrasena']);
        $role = $request->rol;
        $user = new User();
        $user->nombre = $request->nombre;
        $user->usuario = $request->usuario;
        #$user->perfil_id = $request->perfil_id;
        #$user->empresa_id = $request->empresa_id;
        $user->correo= $request->correo;
        $user->contrasena = $request->contrasena;
        #$user->estado = $request->estado;
        $user->save();
        $user->assignRole($role);

        $resource = new UserResource($user);

        $registedData = [
            'name'      => $user->nombre,
            'email'     => $user->correo,
            'password'  => $passwordPlain
        ];

        Mail::to($user->correo)->send(new RegisterUser($registedData));

        return $this->success('Usuario registrado correctamente.', [
            'usuario' => $resource
        ]);
    }

    public function show($idUser)
    {
        try {
            $user = User::where('id', $idUser)->first();

            if ($user == null) {
                return $this->error("Error, NO se encontró el registro.");
            }

            $resource = new UserResource($user);

            return $this->success('Información consultada correctamente.', [
                'usuario' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al mostrar el registro, error:{$th->getMessage()}.");
        }
    }

    public function update(Request $request, $idUser)
    {
        try {
            $rules = [
                'nombre' => 'required|string|max:50',
                'usuario' => 'required|string|max:50',
                'correo' => 'required|string|email|max:255|',
                'rol' => 'required|numeric|min:1',
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
                $errors = $validator->errors()->all();
                return $this->error("Error al actualizar el registro", $errors);
            }

            $user = User::where('id', $idUser)->first();
            
            if ($user == null) {
                return $this->error("Error, NO se encontró el registro.");
            }
            $role = $request->rol;
            $user->nombre = $request->nombre;
            $user->usuario = $request->usuario;
            $user->correo= $request->correo;
            /* $user->perfil_id = $request->perfil_id;
            $user->empresa_id = $request->empresa_id;
            $user->estado = $request->estado; */
            $user->save();
            $roles = $user->roles;

            foreach ($roles as $rol) {
                $user->removeRole($rol);
            }
            $user->assignRole($role);
            
            $resource = new UserResource($user);

            return $this->success('Registro actualizado correctamente.', [
                'usuario' => $resource
            ]);

        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 501);
        }
    }

    public function destroy($idUser, Request $request)
    {
        try {
            $user = User::where('id', $idUser)->first();
            if ($user == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }
            $roles = $user->getRoleNames();

            foreach ($roles as $role) {
                $user->removeRole($role);
            }
            $user->delete();
            
            $resource = new UserResource($user);

            return $this->success('Registro borrado correctamente.', [
                'usuario' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al eliminar el registro, error:{$th->getMessage()}.");
        }
    }

    public function login(AuthenticatedSessionRequest $request)
    {
        $user = User::firstWhere('usuario', request('usuario'));
        $tokenName = null;
        $tz = config('app.timezone');
        $now = Carbon::now($tz);
        $minutesToAdd = config('sanctum.expiration');
        if (isset($user->id)) {
            $tokenName = 'user_auth_token' . $now->format('YmdHis');
        } else {
            return $this->error("Estas credenciales no coinciden con nuestros registros.");
        }

        if (Hash::check(request('password'), $user->contrasena)) {
            
            if ($user->tokens()->where('expires_at', '>', $now->format('Y-m-d H:i:s'))->count() > 0) {
                
                throw ValidationException::withMessages([
                    'account' => 'Actualmente tiene una sesión activa.'
                ]);
            }
            
            // Comenzamos con la transacción en la base de datos
            DB::beginTransaction();
            try {
                $token = $user->createToken($tokenName, $user->getAllPermissionsSlug()->toArray(), $now->addMinutes($minutesToAdd));
                //dd($token);
                //dd($token->plainTextToken);
                $user->access_token = $token->plainTextToken;
                $user->sign_in_at =  $token->accessToken->created_at->format('Y-m-d H:i:s');
                $user->sign_in_expires_at =  $token->accessToken->expires_at->format('Y-m-d H:i:s');
    
                event(new LoginEvent($user));
    
                $resource = new AuthResource($user);
                
                DB::commit();
                return $this->success('Inicio de sesión exitoso.', [
                    'user' => $resource
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                return $this->error("Error al iniciir sesión, error:{$e->getMessage()}.");
            }
        } else {
            return $this->error("No matchea las passowrds");
        }
    }

    public function logout(Request $request)
    {
        try {

            $user = auth()->user();

            $user->currentAccessToken()->delete();

            event(new LogoutEvent($user));
            return $this->success('Cierre de sesión exitoso.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function username()
    {
        return 'usuario';
    }
}
