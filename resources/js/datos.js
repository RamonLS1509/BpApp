// ======================================================================
// A. INYECCIÓN DE DATOS Y VARIABLES GLOBALES
// ======================================================================
const estacionesData = window.estacionesData || [];

let map;
let allMarkers = []; // Almacena todos los marcadores para poder eliminarlos fácilmente

const COMBUSTIBLES = [
    { key: 'Precio Gasolina 95 E5', label: 'G95 E5' },
    { key: 'Precio Gasoleo A', label: 'GA' },
    { key: 'Precio Gasolina 98 E5', label: 'G98 E5' },
    { key: 'Precio Gasoleo B', label: 'GB' },
    { key: 'Precio Gases licuados del petróleo', label: 'GLP' },
    { key: 'Precio Gas Natural Comprimido', label: 'GNC' },
    { key: 'Precio Gasoleo Premium', label: 'G. Prem' },
    // Añade más si es necesario
];


// ======================================================================
// C. FUNCIÓN DE UTILIDAD: VERIFICAR HORARIO DE APERTURA (Incluye 24H)
// ======================================================================

/**
 * Determina si la gasolinera está abierta (incluyendo el caso 24H).
 * @param {string} horario - Cadena de horario.
 * @returns {boolean} True si está abierta, False si está cerrada.
 */
function isGasolineraOpen(horario) {
    if (typeof horario !== 'string' || !horario) return false;

    // Maneja horarios 24H
    if (horario.includes('24H')) {
        return true;
    }

    const match = horario.match(/(\d{1,2}):(\d{2})-(\d{1,2}):(\d{2})/);
    if (!match) return false;

    const [, startHour, startMinute, endHour, endMinute] = match;

    const now = new Date();
    const currentHour = now.getHours();
    const currentMinute = now.getMinutes();

    const startTotalMinutes = parseInt(startHour) * 60 + parseInt(startMinute);
    const endTotalMinutes = parseInt(endHour) * 60 + parseInt(endMinute);
    const currentTotalMinutes = currentHour * 60 + currentMinute;

    if (endTotalMinutes < startTotalMinutes) {
        return currentTotalMinutes >= startTotalMinutes || currentTotalMinutes < endTotalMinutes;
    } else {
        return currentTotalMinutes >= startTotalMinutes && currentTotalMinutes < endTotalMinutes;
    }
}


// ======================================================================
// E. LÓGICA DE FILTRADO Y RENDERIZADO
// ======================================================================

/**
 * Función principal para aplicar filtros y actualizar los marcadores en el mapa.
 */
function filterAndRenderStations() {
    // 1. Obtener valores de los filtros
    const fuelKey = document.getElementById('fuel-filter').value;
    const openNow = document.getElementById('open-now-filter').checked;
    const searchText = (document.getElementById('search-input').value || '').toLowerCase();

    // 2. Aplicar filtros a los datos
    const filteredStations = estacionesData.filter(estacion => {
        // --- FILTRO DE COMBUSTIBLE ---
        if (fuelKey && (!estacion[fuelKey] || estacion[fuelKey] === '')) {
            return false;
        }

        // --- FILTRO DE HORARIO ---
        if (openNow && !isGasolineraOpen(estacion.Horario)) {
            return false;
        }

        // --- FILTRO DE BÚSQUEDA POR TEXTO ---
        if (searchText) {
            const rotulo = (estacion.Rótulo || '').toLowerCase();
            const direccion = (estacion.Dirección || '').toLowerCase();
            if (!rotulo.includes(searchText) && !direccion.includes(searchText)) {
                return false;
            }
        }

        return true;
    });

    // 3. Renderizar solo las estaciones filtradas
    renderMarkers(filteredStations);
}


/**
 * Elimina los marcadores existentes y dibuja los nuevos basados en la data filtrada.
 */
function renderMarkers(data) {
    // 1. Limpiar marcadores existentes del mapa
    allMarkers.forEach(marker => map.removeLayer(marker));
    allMarkers = [];

    let bounds = L.latLngBounds();
    let markersAdded = 0;

    data.forEach(estacion => {
        const lat = parseFloat(estacion.Latitud.replace(',', '.'));
        const lon = parseFloat(estacion['Longitud (WGS84)'].replace(',', '.'));

        if (!isNaN(lat) && !isNaN(lon)) {

            // --- HTML DEL POPUP ---
            const horario = estacion.Horario || 'Horario no disponible';
            const isOpen = isGasolineraOpen(horario);
            const statusText = isOpen ?
                '<strong style="color: #4CAF50;">🟢 ABIERTA AHORA</strong>' :
                '<strong style="color: #F44336;">🔴 CERRADA AHORA</strong>';

            let preciosHtml = '';
            COMBUSTIBLES.forEach(p => {
                const precio = estacion[p.key] ? estacion[p.key].replace(',', '.') : '';
                if (precio && precio !== '') {
                    preciosHtml += `<span style="display:block; font-size: 0.9em; color:#333;">
                                            <strong style="color:#00704A;">${p.label}:</strong> ${precio} €/L
                                        </span>`;
                }
            });

            // --- BOTÓN CÓMO LLEGAR ---
            const directionsUrl = `http://googleusercontent.com/maps.google.com/maps?daddr=${lat},${lon}`;
            const directionsButtonHtml = `
                <a href="${directionsUrl}" target="_blank" style="
                    display: inline-block; margin-top: 10px; padding: 8px 15px;
                    background-color: #00704A; color: white; text-decoration: none;
                    border-radius: 4px; font-weight: bold; font-size: 0.9em;
                ">
                    📍 Cómo llegar
                </a>
            `;

            // --- DESPLEGABLE DE HORARIOS ---
            const horarioDropdownHtml = `
                <hr style="border: 0; border-top: 1px solid #ccc; margin: 5px 0;">
                <details style="font-size: 0.9em;">
                    <summary style="font-weight: bold; cursor: pointer; color: #555;">Horario</summary>
                    <p style="margin-top: 5px; padding-left: 10px;">
                        ${horario.replace(/\n/g, '<br>')}
                    </p>
                </details>
            `;


            const popupContent = `
                <strong style="color:#00704A; font-size: 1.1em;">${estacion.Rótulo}</strong><br>
                <span style="font-size: 0.9em; display: block; margin-bottom: 5px;">${estacion.Dirección}</span>

                <p style="margin: 5px 0 10px 0;">${statusText}</p>

                <hr style="border: 0; border-top: 1px solid #ccc; margin: 5px 0;">

                ${preciosHtml || '<span style="color:red; font-size:0.9em;">Precios no disponibles.</span>'}

                ${horarioDropdownHtml}
                ${directionsButtonHtml}
            `;

            // 4. Crear y almacenar el marcador
            const marker = L.marker([lat, lon]).addTo(map);
            marker.bindPopup(popupContent);
            allMarkers.push(marker);

            bounds.extend([lat, lon]);
            markersAdded++;
        }
    });

    // 5. Ajustar el mapa si es la carga inicial
    if (markersAdded > 0 && map && !map.hasBeenFitted) {
        map.fitBounds(bounds, { padding: [50, 50] });
        map.hasBeenFitted = true; // Para evitar reajustes innecesarios
    }
}


// ======================================================================
// B. LÓGICA DE INICIALIZACIÓN DEL MAPA Y CONTROLES
// ======================================================================
document.addEventListener('DOMContentLoaded', () => {
    const mapElement = document.getElementById('map');
    if (!mapElement) {
        console.error('El contenedor del mapa (#map) no existe.');
        return;
    }

    if (estacionesData.length === 0) {
        // Inicialización para mapa sin datos...
        map = L.map('map').setView([40.416775, -3.703790], 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        L.popup()
            .setLatLng([40.416775, -3.703790])
            .setContent("No se encontraron gasolineras con el filtro aplicado.")
            .openOn(map);
        return;
    }

    // Inicializar el mapa
    map = L.map('map');
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    // 1. Inicializar Filtro de Combustible (Dropdown)
    const fuelFilterSelect = document.getElementById('fuel-filter');
    if (fuelFilterSelect) {
        COMBUSTIBLES.forEach(p => {
            const option = document.createElement('option');
            option.value = p.key;
            option.textContent = p.label;
            fuelFilterSelect.appendChild(option);
        });

        // 2. Asignar Event Listeners a los controles
        fuelFilterSelect.addEventListener('change', filterAndRenderStations);
        document.getElementById('open-now-filter').addEventListener('change', filterAndRenderStations);
        document.getElementById('search-input').addEventListener('input', filterAndRenderStations);
    }

    // 3. Renderizar los marcadores iniciales (sin filtros)
    filterAndRenderStations();
});
