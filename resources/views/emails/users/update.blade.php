<x-mail::message>
# {{__('Hola: ')}} {{ $data['name'] }}
*{{__('Sus credenciales de acceso han cambiado.')}}*

{{__('Sus credenciales son las siguientes')}}:

**{{__('Usuario')}}:** {{ $data['usuario'] }}

**{{__('Contraseña')}}:** {{ $data['password']  }}

<x-mail::button :url="config('redirections.login')">
Acceder
</x-mail::button>
</x-mail::message>
