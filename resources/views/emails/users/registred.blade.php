<x-mail::message>
# {{__('Bienvenido: ')}} {{ $data['name'] }}

{{__('¡Su cuenta ha sido creada correctamente!')}}

{{__('Sus credenciales de acceso son las siguientes')}}:

**{{__('Correo electrónico')}}:** {{ $data['email'] }}

**{{__('Contraseña')}}:** {{ $data['password']  }}

<x-mail::button :url="config('redirections.login')">
Acceder
</x-mail::button>
</x-mail::message>
