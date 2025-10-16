{{-- Código del menú con la paleta de BP --}}
<nav class="bg-[#00704A] shadow-lg"> {{-- Fondo: Verde Esmeralda (Color principal de BP) --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo y Nombre de la App --}}
            <div class="flex items-center">
                <a href="{{ url('/') }}" class="flex-shrink-0 text-[#FFC72C] text-xl font-extrabold tracking-wider">
                    ⛽ Gasolineras
                    <span class="text-white font-bold ml-1">BP</span>
                </a>
            </div>

            {{-- Elementos Principales del Menú (Sin Filtro) --}}
            <div class="hidden md:flex md:items-center">

                {{-- Enlace Acerca de --}}
                <a href="#"
                   class="text-white hover:bg-[#005a3b] px-3 py-2 rounded-md text-sm font-medium">
                    Acerca de
                </a>
            </div>
        </div>
    </div>
</nav>
