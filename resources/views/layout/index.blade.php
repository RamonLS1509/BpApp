<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gasolineras App')</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen">
    @include('components.navbar')

    <main class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('components.filter')
            @yield('content')
            @include('components.map')
        </div>
    </main>


    {{-- Inclusión del JS de Leaflet (DEBE ir ANTES de tu script) --}}
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    {{-- Inclusión del script específico del mapa, si es necesario --}}
    @yield('scripts')
</body>
</html>
