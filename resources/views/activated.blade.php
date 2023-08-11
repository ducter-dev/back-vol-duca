<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <title>Cuenta activada</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="max-w-md w-full px-6 py-8 bg-white shadow-md">
            <div class="text-center">
                <h1 class="text-3xl font-semibold text-gray-800">¡Cuenta activada!</h1>
                <p class="mt-4 text-gray-600">Tu cuenta ha sido activada exitosamente.</p>
                <a href="{{config('app.url_front')}}/auth"
                    class="mt-6 inline-block px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Ir a la aplicación</a>
            </div>
        </div>
    </div>
</body>

</html>
