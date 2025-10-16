{{-- Panel Superior de Controles y Filtros --}}
<div class="bg-gray-100 p-4 rounded-lg shadow-lg mb-6 flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">

    {{-- GRUPO 1: FILTRO PRINCIPAL (PROVINCIA) --}}
    <form action="{{ url('/') }}" method="GET" class="flex space-x-3 items-center w-full md:w-auto flex-shrink-0">

        <label for="provincia-select-main" class="text-gray-700 font-semibold text-sm flex-shrink-0">
            Filtrar por Provincia:
        </label>

        {{-- El select utiliza las variables $provincias y $provinciaSeleccionada --}}
        <select name="provincia" id="provincia-select-main" onchange="this.form.submit()"
            class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#00704A] focus:border-[#00704A] sm:text-sm text-gray-900 font-medium">

            {{-- Opción por defecto para mostrar todas las provincias --}}
            <option value="" @if (empty($provinciaSeleccionada)) selected @endif>
                Todas las Provincias
            </option>

            {{-- Generar las opciones con los datos pasados del controlador --}}
            @if (isset($provincias))
                @foreach ($provincias as $provincia)
                    <option value="{{ $provincia }}" @if ($provincia === $provinciaSeleccionada) selected @endif>
                        {{ $provincia }}
                    </option>
                @endforeach
            @endif
        </select>

    </form>

    {{-- GRUPO 2: FILTROS INTERACTIVOS DEL MAPA (JS) --}}
    {{-- Estos filtros no recargan la página, sino que actúan sobre el mapa con JavaScript --}}
    <div id="controls-panel" class="flex flex-wrap items-center space-x-3 space-y-2 md:space-y-0 justify-end w-full md:w-auto">

        {{-- Filtro por Combustible (Dropdown, se llena con JS) --}}
        <select id="fuel-filter" title="Filtrar por tipo de combustible"
            class="py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#00704A] focus:border-[#00704A] sm:text-sm text-gray-900 font-medium flex-grow md:flex-grow-0">
            <option value="">Todos los combustibles</option>
            {{-- Las opciones específicas se llenan dinámicamente con JavaScript --}}
        </select>

        {{-- Filtro por Estado de Apertura (Checkbox) --}}
        <label for="open-now-filter" class="flex items-center text-sm font-medium text-gray-700 whitespace-nowrap">
            <input type="checkbox" id="open-now-filter"
                class="form-checkbox h-4 w-4 text-[#00704A] rounded focus:ring-[#00704A] border-gray-300 mr-1">
            Abiertas Ahora
        </label>

        {{-- Búsqueda por Rótulo o Dirección --}}
        <input type="text" id="search-input" placeholder="🔍 Buscar por Nombre o Dirección"
            title="Buscar gasolinera por marca o calle"
            class="py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#00704A] focus:border-[#00704A] sm:text-sm text-gray-900 font-normal w-full md:w-56">

        {{-- NOTA: El botón 'Mi Ubicación' se ha ELIMINADO aquí --}}
    </div>

</div>

{{-- Indicador de filtro aplicado --}}
@if (isset($data) && isset($data['FiltroAplicado']))
    <div class="text-right text-sm font-normal text-gray-500 mb-4">
        Mostrando: {{ $data['NumeroEstacionesEncontradas'] ?? 0 }} estaciones en la provincia
        @if (!empty($provinciaSeleccionada))
            de {{ $provinciaSeleccionada }}.
        @else
            actualmente.
        @endif
    </div>
@endif
