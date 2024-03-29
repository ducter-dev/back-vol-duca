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
use App\Http\Resources\BloqueadoResource;
use App\Mail\RecoverPassword;
use App\Mail\UpdatePassword;
use App\Mail\RegisterUser;
use App\Models\Bloqueado;
use App\Models\Caducidad;
use App\Traits\ApiResponder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Str;

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
        // Generar una nueva contraseña aleatoria
        $passwordPlain = Str::random(10);
        $hash_password = Hash::make($passwordPlain);
        $role = $request->rol;
        $user = new User();
        $user->nombre = $request->nombre;
        $user->usuario = $request->usuario;
        $user->correo= $request->correo;
        $user->contrasena = $hash_password;
        $user->save();
        $user->assignRole($role);

        $hoy = date('Y-m-d H:i:s');
        $caducidad = strtotime('+2 months', strtotime($hoy));
        $caducidad = date('Y-m-d H:i:s', $caducidad);

        $contraUser = new Caducidad();
        $contraUser->contrasena = $user->contrasena;
        $contraUser->caducidad = $caducidad;
        $contraUser->estado = 1;
        $user->caducidades()->save($contraUser);

        $resource = new UserResource($user);
        
        // Falta crear link de activación de cuenta

        $registedData = [
            'name'      => $user->nombre,
            'email'     => $user->correo,
            'usuario'     => $user->usuario,
            'password'  => $passwordPlain,
            'link_activate_count' => $this->generarLinkActivarCuenta($user->id)
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
            $user->save();
            $roles = $user->roles;

            foreach ($roles as $rol) {
                $user->removeRole($rol);
            }
            $user->assignRole($role);
            
            $resource = new UserResource($user);

            return $this->success('Registro actualizado correctamente.', code:201);

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
        $ahora = $now->format('Y-m-d H:i:s');
        $minutesToAdd = config('sanctum.expiration');

        if (isset($user->id)) {
            $tokenName = 'user_auth_token' . $now->format('YmdHis');
        } else {
            return $this->error("Estas credenciales no coinciden con nuestros registros.", code:400);
        }

        if (Hash::check(request('password'), $user->contrasena)) {
            if ($user->tokens()->where('expires_at', '>', $ahora)->count() > 0) {
                return $this->error("Actualmente tiene una sesión activa.", code:401);
            }

            
            /* Verificamos que el usuario tenga la cuenta verificada */
            if (is_null($user->correo_verificado))
            {
                return $this->error("La cuenta no se encuentra verificada, revise su correo electrónico para activarla.", code:401);
            }

            /* Verificamos que la contraseña no esté caducada */
            $caducidad = Caducidad::where('usuario_id', $user->id)->where('estado', 1)->first();
            $fecha_caducidad = Carbon::parse($caducidad->caducidad);
            $ahora = Carbon::parse($ahora);

            if ($ahora->greaterThan($fecha_caducidad))
            {
                return $this->error("La contraseña ha caducado, debe generar una nueva.", code:402, data: $user->id);
            }
            
            /* Verificamos que el usuario no esté bloqueado */
            $bloqueo = Bloqueado::where('usuario_id', $user->id)->first();
            $fecha_desbloqueo = Carbon::parse($bloqueo->fecha_desbloqueo);

            if ($ahora->lessThan($fecha_desbloqueo))
            {
                return $this->error("Usuario bloqueado hasta $bloqueo->fecha_desbloqueo.", code:403);
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
                return $this->success("Inicio de sesión exitoso. Bienvenido {$user->nombre}.", [
                    'user' => $resource
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                return $this->error("Error al iniciar sesión, error:{$e->getMessage()}.",code:401);
            }
        } else {
            return $this->error("Error al iniciar sesión, revise sus credenciales", code:400);
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

    public function updatePassword(Request $request)
    {
        $rules = [
            'usuario' => 'required|numeric|min:1',
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
            return $this->error("Error al actualizar la password", $errors);
        }

        $idUser = $request['usuario'];

        $user = User::where('id', $idUser)->first();

        if (!$user) {
            return $this->error("No se encontró un usuario con esos registros.");
        }
        
        #   Hashear la password del request
        $password_hashed = Hash::make($request['contrasena']);
        
        #   Actualizar la contraseña del usuario en la base de datos
        $user->contrasena = $password_hashed;
        $user->save();


        #   Cambiar el estatus de las caducidades anteriores
        $caducidades = Caducidad::where('usuario_id', $user->id)->get();

        foreach ($caducidades as $cad) {
            $cad->estado = 2;
            $cad->save();
        }

        #   Agregar la password a las caducidades
        $hoy = date('Y-m-d H:i:s');
        $caducidad = strtotime('+2 months', strtotime($hoy));
        $caducidad = date('Y-m-d H:i:s', $caducidad);

        $contraUser = new Caducidad();
        $contraUser->contrasena = $user->contrasena;
        $contraUser->caducidad = $caducidad;
        $contraUser->estado = 1;
        $user->caducidades()->save($contraUser);

        $registedData = [
            'name'      => $user->nombre,
            'email'     => $user->correo,
            'usuario'     => $user->usuario,
            'password'  => $request['contrasena']
        ];

        $resource = new UserResource($user);

        // Enviar la nueva contraseña por correo electrónico
        Mail::to($user->correo)->send(new updatePassword($registedData));

        return $this->success('Contraseña actualizada.', [
            'usuario' => $resource
        ]);
    }

    # Crear función para recuperar password a partir del paramétro de email
    public function recoveryPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'correo' => 'required|email',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return $this->error("Error al actualizar la password", $errors);
        }

        $user = User::where('correo', $request->correo)->first();

        if (!$user) {
            return $this->error("No se encontró un usuario con ese correo electrónico.");
        }

        // Generar una nueva contraseña aleatoria
        $newPassword = Str::random(10);
        $hash_password = Hash::make($newPassword);

        // Actualizar la contraseña del usuario en la base de datos
        $user->contrasena = $hash_password;
        $user->save();
        
        $registedData = [
            'name'      => $user->nombre,
            'email'     => $user->correo,
            'usuario'     => $user->usuario,
            'password'  => $newPassword
        ];

        // Enviar la nueva contraseña por correo electrónico
        Mail::to($user->correo)->send(new RecoverPassword($registedData));

        return $this->success("Se ha enviado una nueva contraseña al correo electrónico proporcionado.");
    }

    public function activarCuenta($token)
    {
        try {
            # Llamamos a la función que desencripte el token para obtener el id del usuario
            $idUsuario = $this->desencriptarLink($token);
            $idUsuario = intval($idUsuario);

            # Ya con el id, buscamos el usuario y lo activamos
            $user = User::where('id', $idUsuario)->first();
            
            # Si no lo encontramos, lanzamos el error
            if ($user == null) {
                return $this->error("Error, NO se encontró el registro.");
            }
            # Si lo encontramos seguimos el proceso
            $tz = config('app.timezone');
            $now = Carbon::now($tz);
            $now->format('Y-m-d H:i:s');
            $user->correo_verificado = $now;
            $user->save();
    
            # Retornar el mensaje de cuenta activada
            return view('activated');

        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function generarLinkActivarCuenta($idUser)
    {
        $url = config('app.url');
        $key = config('app.key_encript');
        // Generar un token único
        $token = Str::random(32);

        $mensaje = "$token:$idUser";

        // Encriptar el token
        $encriptedToken = base64_encode($mensaje . $key);

        // Crear el enlace con el token encriptado
        $link = $url . "/api/users/activar-cuenta/" . urlencode($encriptedToken);
        return $link;
    }

    public function desencriptarLink($token)
    {
        $key = config('app.key_encript');
        $desencriptado = base64_decode(urldecode($token));
        $result = str_replace($key, "", $desencriptado);
        $parts = explode(":", $result);
        return $parts[1];
    }

    public function bloquearUsuario(Request $request)
    {
        try {
            $user = User::where('usuario', $request->usuario)->first();
        
            if ($user == null) {
                return $this->error("Error, NO se encontró el usuario.");
            }

            $bloqueos = Bloqueado::where('usuario_id', $user->id)->get();
            foreach ($bloqueos as $bloqueo) {
                $bloqueo->delete();
            }

            $fecha_bloqueo = date('Y-m-d H:i:s');
            $fecha_desbloqueo = strtotime('+15 minutes', strtotime($fecha_bloqueo));
            $fecha_desbloqueo = date('Y-m-d H:i:s', $fecha_desbloqueo);
            $bloqueo = new Bloqueado();
            $bloqueo->fecha_bloqueo = $fecha_bloqueo;
            $bloqueo->fecha_desbloqueo = $fecha_desbloqueo;
            $bloqueo->usuario_id = $user->id;
            $bloqueo->save();

            $resource = new BloqueadoResource($bloqueo);

            return $this->success('Usuario bloqueado correctamente.', [
                'bloqueo' => $resource
            ]);
        } catch (\Throwable $th) {
            return $this->error($th);
        }
        
    }
}
