<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache; // <--- Importamos la fachada Cache

class ApiController extends Controller
{
    public function fetchAndDisplayData(Request $request)
    {
        // 1. Configuración de Filtros
        // FILTRO FIJO: Rótulo de la gasolinera (BP)
        $rotuloDeseado = 'BP';
        $rotuloDeseadoUpper = strtoupper($rotuloDeseado);

        // FILTRO DINÁMICO: Provincia (se lee de la URL, si no está, es null)
        $provinciaDeseada = $request->input('provincia');
        $provinciaDeseadaUpper = $provinciaDeseada ? strtoupper($provinciaDeseada) : null;


        // 2. OPTIMIZACIÓN: Lógica de caché para obtener los datos brutos de la API
        // Guardamos la respuesta completa de la API, incluyendo la fecha y la lista de estaciones,
        // por un tiempo de 3600 segundos (1 hora).

        $cacheKey = 'carburantes_api_completa';
        $cacheDuration = 3600; // 1 hora

        $apiData = Cache::remember($cacheKey, $cacheDuration, function () {

            // Realizar la llamada GET a la API
            $response = Http::get('https://sedeaplicaciones.minetur.gob.es/ServiciosRESTCarburantes/PreciosCarburantes/EstacionesTerrestres');

            // 3. Manejo de Errores y Validación
            if (!$response->successful() || !isset($response->json()['ListaEESSPrecio']) || !is_array($response->json()['ListaEESSPrecio'])) {
                // Si falla, retornamos un array vacío o un valor que permita manejar el error fuera.
                return [];
            }

            // Si tiene éxito, devolvemos el array JSON completo de la API
            return $response->json();
        });

        // Manejo de errores si la caché y la API fallaron
        if (empty($apiData) || !isset($apiData['ListaEESSPrecio']) || !is_array($apiData['ListaEESSPrecio'])) {
            return view('api.datos', [
                'error' => 'No se pudieron obtener datos válidos de la API (caché o llamada directa).'
            ]);
        }

        $jsonData = $apiData; // Usamos los datos obtenidos (de caché o API)
        $estaciones = collect($jsonData['ListaEESSPrecio']);

        // Obtenemos la colección de estaciones y aplicamos el filtro fijo y la limpieza de coordenadas
        $estacionesBP_limpias = $estaciones
            // 4a. Filtrar por Rótulo (fijo a BP)
            ->filter(function ($estacion) use ($rotuloDeseadoUpper) {
                $rotuloEstacion = $estacion['Rótulo'] ?? null;
                if ($rotuloEstacion) {
                    return str_contains(strtoupper($rotuloEstacion), $rotuloDeseadoUpper);
                }
                return false;
            })
            // 4c. Limpieza de Coordenadas (Cambiar ',' por '.' para JavaScript)
            ->map(function ($estacion) {
                $estacion['Latitud'] = str_replace(',', '.', $estacion['Latitud']);
                $estacion['Longitud (WGS84)'] = str_replace(',', '.', $estacion['Longitud (WGS84)']);
                return $estacion;
            })
            ->values();

        // 5. Generación y Manipulación de la Lista de Provincias
        $provinciasUnicas = $estacionesBP_limpias->pluck('Provincia')->unique()->sort()->filter();

        // Lógica para mover ÁVILA al principio:
        $targetProvince = 'ÁVILA';

        // Verificamos si la provincia existe antes de manipular
        if ($provinciasUnicas->contains($targetProvince)) {

            // 1. Quitar 'ÁVILA' de la colección
            $provinciasUnicas = $provinciasUnicas->reject(function ($provincia) use ($targetProvince) {
                return $provincia === $targetProvince;
            });

            // 2. Volvemos a ordenar alfabéticamente el resto de provincias.
            $provinciasUnicas = $provinciasUnicas->sort();

            // 3. Añadir 'ÁVILA' al principio del listado.
            $provinciasUnicas->prepend($targetProvince);
        }

        // Aseguramos que la colección final esté reindexada para la vista
        $provinciasUnicas = $provinciasUnicas->values();


        // 6. Aplicar el Filtrado DINÁMICO (siempre se ejecuta, incluso con caché)
        $estacionesFiltradas = $estacionesBP_limpias
            // 4b. Filtrar por Provincia (si se ha seleccionado una)
            ->when($provinciaDeseadaUpper, function (Collection $collection) use ($provinciaDeseadaUpper) {
                return $collection->filter(function ($estacion) use ($provinciaDeseadaUpper) {
                    $provinciaEstacion = $estacion['Provincia'] ?? null;
                    if ($provinciaEstacion) {
                        return strtoupper($provinciaEstacion) === $provinciaDeseadaUpper;
                    }
                    return false;
                });
            })
            ->values();

        // 7. Preparar la Respuesta Filtrada para la vista
        $filtroInfo = $rotuloDeseado;
        if ($provinciaDeseada) {
            $filtroInfo .= $provinciaDeseada;
        }

        // Indicamos si los datos vienen de caché o son nuevos
        $fechaData = Cache::has($cacheKey) ? 'Datos en caché' : ($jsonData['Fecha'] ?? 'Fecha no disponible');


        $respuestaFiltrada = [
            'Fecha' => $fechaData,
            'FiltroAplicado' => $filtroInfo,
            'NumeroEstacionesEncontradas' => $estacionesFiltradas->count(),
            'ListaEESSPrecio_Filtrada' => $estacionesFiltradas->toArray(),
        ];

        // 8. Retornar la Vista con los Datos Filtrados
        return view('api.datos', [
            'data' => $respuestaFiltrada,
            'title' => 'Estaciones Filtradas',
            'rotuloDeseado' => $rotuloDeseado,
            'provincias' => $provinciasUnicas->all(), // Usamos ->all() ya que ya fue reindexada con ->values()
            'provinciaSeleccionada' => $provinciaDeseada
        ]);
    }
}
