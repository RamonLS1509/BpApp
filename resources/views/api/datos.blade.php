@vite('resources/js/datos.js')
@extends('layout.index')

@section('title', 'Datos de Estaciones Filtradas')

{{-- SECCIÓN PARA PASAR VARIABLES AL LAYOUT --}}
{{-- Aunque el formulario ya no está en el navbar, las variables se siguen definiendo aquí para ser usadas localmente.
--}}
@section('navbar_vars')
    @php
        // Estas variables se definen para ser usadas por el formulario en esta misma vista
        $provincias = $provincias ?? [];
        $provinciaSeleccionada = $provinciaSeleccionada ?? null;
    @endphp
@endsection

@section('content')
    <h1 class="text-3xl font-extrabold text-gray-800 mb-6 border-b border-gray-300 pb-2">
        Mapa y Lista de Estaciones
    </h1>

    @if (isset($error))
        <p class="text-red-600 font-bold mb-4">Error: {{ $error }}</p>
    @endif

    {{-- Comprobación de que existen datos filtrados --}}
    @if (isset($data) && isset($data['ListaEESSPrecio_Filtrada']) && is_array($data['ListaEESSPrecio_Filtrada']))
    @else
        <p class="text-gray-500">No se encontraron datos de estaciones para mostrar.</p>
    @endif

@endsection

{{-- Script del mapa (Lógica Leaflet) --}}
@section('scripts')
    <script>
        window.estacionesData = {!! json_encode($data['ListaEESSPrecio_Filtrada'] ?? []) !!};
    </script>
    <script src="{{ asset('resources/js/datos.js') }}"></script>
@endsection
