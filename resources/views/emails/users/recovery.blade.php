<x-mail::message>
# {{__('Bienvenido: ')}} {{ $data['name'] }}

{{__('Sus credenciales de acceso son las siguientes')}}:

**{{__('Usuario')}}:** {{ $data['usuario'] }}

**{{__('Contraseña')}}:** {{ $data['password']  }}

<x-mail::button :url="config('redirections.login')">
Acceder
</x-mail::button>
</x-mail::message>
