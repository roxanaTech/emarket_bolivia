// Event listener para el botón de búsqueda (se ejecuta una vez el DOM esté listo)
document.addEventListener('DOMContentLoaded', function () {
    const searchBtn = document.getElementById('search-btn');
    const searchInput = document.getElementById('search-input');

    if (!searchBtn || !searchInput) {
        console.warn('Elementos de búsqueda no encontrados en el DOM. Verifica IDs.');
        return;
    }

    searchBtn.addEventListener('click', function (event) {
        event.preventDefault(); // Previene cualquier comportamiento default (si lo hay)

        const searchTerm = searchInput.value.trim();
        console.log('DEBUG - Término de búsqueda raw (trimmed):', searchTerm);

        if (!searchTerm) {
            console.warn('DEBUG - Búsqueda vacía: No se redirige.');
            // Opcional: Agrega aquí un mensaje al usuario, ej: alert('Ingresa un término de búsqueda.');
            return;
        }

        // Divide en palabras, trim cada una y filtra vacías
        const palabras = searchTerm
            .split(' ')
            .map(palabra => palabra.trim())
            .filter(palabra => palabra.length > 0);

        console.log('DEBUG - Palabras procesadas:', palabras);

        if (palabras.length === 0) {
            console.warn('DEBUG - No hay palabras válidas después del procesamiento.');
            return;
        }

        // Construye params con palabras[]
        const params = new URLSearchParams();
        palabras.forEach(palabra => {
            params.append('palabras[]', palabra);
        });

        // URL completa de redirección (ajusta la ruta base si tu sitio usa subdirectorios, ej: '/emarket_bolivia/productos-resultados.php')
        const redirectUrl = `productos-resultados.php?${params.toString()}`;
        console.log('DEBUG - URL de redirección construida:', redirectUrl);

        // Redirige
        window.location.href = redirectUrl;
    });

    console.log('DEBUG - Event listener de búsqueda configurado correctamente.');
});