<x-mail::message>
# {{__('Bienvenido: ')}} {{ $data['name'] }}

{{__('¡Su cuenta ha sido creada correctamente!')}}

{{__('Sus credenciales de acceso son las siguientes')}}:

**{{__('Correo electrónico')}}:** {{ $data['email'] }}

**{{__('Contraseña')}}:** {{ $data['password']  }}

@component('mail::button', ['url' => $data['link_activate_count']])
Activar cuenta
@endcomponent

</x-mail::message>
