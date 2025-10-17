// --- CONSTANTES ---
var PRODUCTOS_POR_PAGINA = 10;

// Contenedores del DOM (asumiendo estructura similar a categorias-productos.php)
const headerContainer = document.getElementById('header-container');
const productCardsContainer = document.getElementById('product-cards-container');
const paginationContainer = document.getElementById('pagination-container');
const productsTitle = document.getElementById('products-title');

/**
 * Extrae los términos de búsqueda y filtros de la URL actual.
 * @returns {Object} { palabras: array de strings, filters: object con filtros, pagina: number }
 */
function getSearchTermsFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    const palabras = urlParams.getAll('palabras[]'); // Soporta múltiples palabras
    const pagina = parseInt(urlParams.get('pagina'), 10) || 1;

    // Filtros avanzados
    const filters = {};

    // Categoría
    const categoria = urlParams.get('id_categoria');
    if (categoria) filters.categoria = parseInt(categoria, 10);

    // Marcas (múltiples)
    const marcas = urlParams.getAll('marca[]');
    if (marcas.length > 0) filters.marcas = marcas;

    // Precio
    const precioMin = urlParams.get('precio_min');
    const precioMax = urlParams.get('precio_max');
    if (precioMin && !isNaN(precioMin)) filters.precio_min = parseFloat(precioMin);
    if (precioMax && !isNaN(precioMax)) filters.precio_max = parseFloat(precioMax);

    // Calificación
    const califMin = urlParams.get('calificacion_min');
    if (califMin && !isNaN(califMin)) filters.calificacion_min = parseInt(califMin, 10);

    // Estado producto (condición)
    const estadoProducto = urlParams.get('estado_producto');
    if (estadoProducto) filters.estado_producto = estadoProducto; // ej: 'nuevo', 'usado', 'reacondicionado'

    // Disponible (boolean, 1/true para en stock)
    const disponible = urlParams.get('disponible');
    if (disponible && (disponible === '1' || disponible === 'true')) filters.disponible = 1;

    console.log('DEBUG - Términos y filtros extraídos de URL:', { palabras, filters, pagina });

    return { palabras, filters, pagina };
}

/**
 * Actualiza el título dinámico basado en los términos de búsqueda.
 * @param {array} palabras - Array de palabras de búsqueda
 */
function updateTitleFromSearch(palabras) {
    if (!productsTitle) {
        console.warn('DEBUG - Elemento #products-title no encontrado.');
        return;
    }

    if (palabras.length === 0) {
        productsTitle.textContent = 'Resultados de búsqueda';
    } else {
        const searchPhrase = palabras.join(' '); // Reconstruye la frase original
        productsTitle.textContent = `Resultados para "${searchPhrase}"`;
    }

    console.log('DEBUG - Título actualizado:', productsTitle.textContent);
}

/**
 * Carga marcas dinámicamente basado en palabras de búsqueda y filtros actuales.
 * @param {object} filters - { palabras: array, categoria, marcas, precio_min, etc. }
 */
async function loadBrands(filters) {
    if (!document.getElementById('brand-filters-container')) {
        console.warn('DEBUG - Contenedor de marcas no encontrado.');
        return;
    }

    if (!filters.palabras || filters.palabras.length === 0) {
        console.error('DEBUG - No hay palabras para cargar marcas.');
        return;
    }

    // Construir endpoint con TODOS los params (palabras + filtros aplicados)
    const params = new URLSearchParams();

    // Términos de búsqueda (múltiples)
    filters.palabras.forEach(palabra => params.append('palabras[]', palabra));

    // Filtros avanzados (para refinar marcas)
    if (filters.categoria) {
        params.append('id_categoria', filters.categoria);
    }
    if (filters.marcas && filters.marcas.length > 0) {
        filters.marcas.forEach(marca => params.append('marca[]', marca));
    }
    if (typeof filters.precio_min !== 'undefined') {
        params.append('precio_min', filters.precio_min);
    }
    if (typeof filters.precio_max !== 'undefined') {
        params.append('precio_max', filters.precio_max);
    }
    if (typeof filters.calificacion_min !== 'undefined') {
        params.append('calificacion_min', filters.calificacion_min);
    }
    if (filters.estado_producto) {
        params.append('estado_producto', filters.estado_producto);
    }
    if (filters.disponible === 1) {
        params.append('disponible', 1);
    }

    const endpoint = `/productos/marcas?${params.toString()}`;
    const fullUrl = apiUrl + endpoint;
    console.log(`DEBUG - Cargando marcas con filtros desde: ${fullUrl}`);

    try {
        const response = await fetch(fullUrl);
        if (!response.ok) throw new Error(`Error: ${response.statusText}`);
        const jsonResponse = await response.json();

        if (jsonResponse.status !== 'success' || !jsonResponse.data) {
            throw new Error(jsonResponse.mensaje || 'Respuesta inválida');
        }

        const marcas = jsonResponse.data;
        // Sort por cantidad descendente (si el backend no lo hace)
        marcas.sort((a, b) => b.cantidad - a.cantidad);

        // Limita a top 10 (o todas si <10)
        const topMarcas = marcas.slice(0, 10);

        // Renderiza HTML
        let marcasHtml = '';
        topMarcas.forEach(marca => {
            marcasHtml += `
                <label class="flex items-center gap-2">
                    <input class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                           type="checkbox" data-filter-type="brand" value="${marca.marca}" />
                    <span>${marca.marca} (${marca.cantidad})</span>  <!-- Opcional: Muestra cantidad -->
                </label>
            `;
        });

        // Si hay más de 10, agrega "Ver más"
        if (marcas.length > 10) {
            marcasHtml += `
                <button id="ver-mas-marcas" class="text-sm text-primary hover:underline mt-2">
                    Ver más marcas (${marcas.length - 10} adicionales)
                </button>
            `;
        }

        document.getElementById('brand-filters-container').innerHTML = marcasHtml;

        // Opcional: Event para "Ver más" (muestra hasta 20)
        const verMasBtn = document.getElementById('ver-mas-marcas');
        if (verMasBtn) {
            verMasBtn.addEventListener('click', () => {
                const masMarcas = marcas.slice(10, 20).map(m => `
                    <label class="flex items-center gap-2">
                        <input class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                               type="checkbox" data-filter-type="brand" value="${m.marca}" />
                        <span>${m.marca} (${m.cantidad})</span>
                    </label>
                `).join('');
                verMasBtn.insertAdjacentHTML('beforebegin', masMarcas);
                verMasBtn.textContent = 'Ver menos';
                verMasBtn.id = 'ver-menos-marcas';  // Cambia ID para toggle back
            });
        }

        console.log(`DEBUG - Cargadas ${topMarcas.length} marcas top con filtros aplicados`);
    } catch (error) {
        console.error('DEBUG - Error al cargar marcas:', error);
        document.getElementById('brand-filters-container').innerHTML = '<p class="text-sm text-gray-500">Error al cargar marcas</p>';
    }
}

/**
 * Aplica la búsqueda inicial desde la URL, incluyendo filtros y marcas.
 */
async function applySearchFromUrl() {
    const { palabras, filters, pagina } = getSearchTermsFromUrl();

    if (palabras.length === 0) {
        // No hay términos: Muestra mensaje vacío
        console.warn('DEBUG - No hay términos de búsqueda en la URL.');
        if (productCardsContainer) {
            productCardsContainer.innerHTML = '<p class="text-center text-xl text-gray-500">Ingresa un término para buscar productos.</p>';
        }
        updateTitleFromSearch([]);
        return;
    }

    // Actualizar título
    updateTitleFromSearch(palabras);

    // Preparar filtros base con palabras
    const searchFilters = { palabras, ...filters };

    // Cargar productos con términos y filtros
    await fetchSearchProducts(searchFilters, pagina);

    // Cargar marcas basadas en palabras + filtros actuales
    await loadBrands(searchFilters);

    // Restaurar valores en los inputs del formulario de filtros
    restoreFilterInputs(searchFilters);
}

/**
 * Recoge los valores actuales de los filtros del DOM.
 * @param {Object} baseFilters - Debe contener palabras (obligatorio)
 * @returns {Object} Objeto con los filtros aplicables para la API
 */
function getActiveFilters(baseFilters) {
    const filters = { ...baseFilters }; // Clonamos para no mutar el original

    // --- Categoría seleccionada ---
    const selectedCategoria = document.querySelector('input[name="categoria-filter"]:checked');
    if (selectedCategoria) {
        filters.categoria = parseInt(selectedCategoria.value, 10);
    }

    // --- Marcas seleccionadas ---
    const brandCheckboxes = document.querySelectorAll('#brand-filters-container input[type="checkbox"][data-filter-type="brand"]:checked');
    if (brandCheckboxes.length > 0) {
        filters.marcas = Array.from(brandCheckboxes).map(cb => cb.value);
    }

    // --- Rango de precios ---
    const priceMinInput = document.getElementById('price-min');
    const priceMaxInput = document.getElementById('price-max');
    if (priceMinInput?.value && !isNaN(priceMinInput.value)) {
        filters.precio_min = parseFloat(priceMinInput.value);
    }
    if (priceMaxInput?.value && !isNaN(priceMaxInput.value)) {
        filters.precio_max = parseFloat(priceMaxInput.value);
    }

    // --- Calificación mínima ---
    const selectedRating = document.querySelector('input[name="rating-filter"]:checked');
    if (selectedRating) {
        filters.calificacion_min = parseInt(selectedRating.value, 10);
    }

    // --- Estado producto (condición) ---
    const selectedCondition = document.querySelector('input[name="condition-filter"]:checked');
    if (selectedCondition) {
        filters.estado_producto = selectedCondition.value;
    }

    // --- Disponible ---
    const availabilityCheckbox = document.getElementById('availability-filter');
    if (availabilityCheckbox?.checked) {
        filters.disponible = 1;
    }

    console.log('DEBUG - Filtros activos del DOM:', filters);
    return filters;
}

/**
 * Restaura los valores de los inputs de filtros según los filtros activos.
 * @param {Object} filters - Filtros de la URL
 */
function restoreFilterInputs(filters) {
    // Categoría
    if (filters.categoria) {
        const radio = document.querySelector(`input[name="categoria-filter"][value="${filters.categoria}"]`);
        if (radio) radio.checked = true;
    }

    // Marcas
    if (filters.marcas) {
        filters.marcas.forEach(marca => {
            const checkbox = document.querySelector(`#brand-filters-container input[value="${marca}"][data-filter-type="brand"]`);
            if (checkbox) checkbox.checked = true;
        });
    }

    // Precio
    if (typeof filters.precio_min !== 'undefined') {
        document.getElementById('price-min').value = filters.precio_min;
    }
    if (typeof filters.precio_max !== 'undefined') {
        document.getElementById('price-max').value = filters.precio_max;
    }

    // Calificación
    if (typeof filters.calificacion_min !== 'undefined') {
        const radio = document.querySelector(`input[name="rating-filter"][value="${filters.calificacion_min}"]`);
        if (radio) radio.checked = true;
    }

    // Estado producto (condición)
    if (filters.estado_producto) {
        const radio = document.querySelector(`input[name="condition-filter"][value="${filters.estado_producto}"]`);
        if (radio) radio.checked = true;
    }

    // Disponible
    if (filters.disponible === 1) {
        const checkbox = document.getElementById('availability-filter');
        if (checkbox) checkbox.checked = true;
    }

    console.log('DEBUG - Filtros restaurados en el DOM');
}

/**
 * Llama al endpoint /productos/buscar con términos de búsqueda y filtros.
 * @param {Object} filters - { palabras: array, categoria, marcas, etc. }
 * @param {number} page - Página a solicitar
 */
async function fetchSearchProducts(filters, page = 1) {
    // Validar que tengamos palabras
    if (!filters.palabras || filters.palabras.length === 0) {
        console.error('DEBUG - No se puede buscar sin términos.');
        return;
    }

    // Construir URL de búsqueda
    const params = new URLSearchParams();

    // Términos de búsqueda (múltiples)
    filters.palabras.forEach(palabra => params.append('palabras[]', palabra));

    // Filtros avanzados
    if (filters.categoria) {
        params.append('id_categoria', filters.categoria);
    }
    if (filters.marcas && filters.marcas.length > 0) {
        filters.marcas.forEach(marca => params.append('marca[]', marca));
    }
    if (typeof filters.precio_min !== 'undefined') {
        params.append('precio_min', filters.precio_min);
    }
    if (typeof filters.precio_max !== 'undefined') {
        params.append('precio_max', filters.precio_max);
    }
    if (typeof filters.calificacion_min !== 'undefined') {
        params.append('calificacion_min', filters.calificacion_min);
    }
    if (filters.estado_producto) {
        params.append('estado_producto', filters.estado_producto);
    }
    if (filters.disponible === 1) {
        params.append('disponible', 1);
    }

    // Paginación
    params.append('pagina', page);
    params.append('por_pagina', PRODUCTOS_POR_PAGINA);

    const fullUrl = `${apiUrl}/productos/buscar?${params.toString()}`;
    console.log('DEBUG - Llamando a endpoint de búsqueda:', fullUrl);

    if (productCardsContainer) productCardsContainer.innerHTML = '<p class="text-center text-xl text-primary">Buscando productos...</p>';

    try {
        const response = await fetch(fullUrl);
        if (!response.ok) throw new Error(`Error: ${response.statusText}`);
        const jsonResponse = await response.json();

        if (jsonResponse.status !== 'success' || !jsonResponse.data) {
            throw new Error(jsonResponse.mensaje || 'Respuesta inválida');
        }

        const data = jsonResponse.data;

        // Limpiar
        if (productCardsContainer) productCardsContainer.innerHTML = '';
        if (paginationContainer) paginationContainer.innerHTML = '';

        if (data.productos.length === 0) {
            if (productCardsContainer) productCardsContainer.innerHTML = '<p class="text-center text-gray-500">No hay productos para estos términos y filtros.</p>';
            updateHeader({ total: 0, por_pagina: PRODUCTOS_POR_PAGINA, pagina: 1 });
            return;
        }

        // Renderizar
        updateHeader(data);
        const productCardsHtml = data.productos.map(createProductCard).join('');
        if (productCardsContainer) productCardsContainer.innerHTML = productCardsHtml;

        // Paginación (envuelta para actualizar URL)
        const originalHandler = (newPage) => fetchSearchProducts(filters, newPage);
        const paginationHandler = (newPage) => {
            updateUrlFromSearch(filters, newPage);
            originalHandler(newPage);
        };
        createPagination(data, paginationHandler);

        // Recargar marcas con filtros actuales (después de renderizar productos)
        await loadBrands(filters);

    } catch (error) {
        console.error('DEBUG - Error al buscar productos:', error);
        if (productCardsContainer) {
            productCardsContainer.innerHTML = `<p class="text-center text-error-light">Error: ${error.message}</p>`;
        }
    }
}

/**
 * Actualiza la URL del navegador con los términos de búsqueda, filtros y página (sin recargar).
 * @param {Object} filters - { palabras: array, categoria, marcas, etc. }
 * @param {number} page - Página actual
 */
function updateUrlFromSearch(filters, page = 1) {
    const params = new URLSearchParams();

    // Términos de búsqueda
    if (filters.palabras && filters.palabras.length > 0) {
        filters.palabras.forEach(palabra => params.append('palabras[]', palabra));
    }

    // Filtros avanzados
    if (filters.categoria) {
        params.append('id_categoria', filters.categoria);
    }
    if (filters.marcas && filters.marcas.length > 0) {
        filters.marcas.forEach(marca => params.append('marca[]', marca));
    }
    if (typeof filters.precio_min !== 'undefined') {
        params.append('precio_min', filters.precio_min);
    }
    if (typeof filters.precio_max !== 'undefined') {
        params.append('precio_max', filters.precio_max);
    }
    if (typeof filters.calificacion_min !== 'undefined') {
        params.append('calificacion_min', filters.calificacion_min);
    }
    if (filters.estado_producto) {
        params.append('estado_producto', filters.estado_producto);
    }
    if (filters.disponible === 1) {
        params.append('disponible', 1);
    }

    // Paginación
    params.append('pagina', page);

    const newUrl = `${window.location.pathname}?${params.toString()}`;
    window.history.pushState({ path: newUrl }, '', newUrl);
    console.log('DEBUG - URL actualizada para búsqueda:', newUrl);
}

// --- FUNCIONES ---
// 1. createProductCard (función completa con estrellas, stock, etc.)
function createProductCard(producto) {
    const isOffer = producto.precio_promocional !== null && parseFloat(producto.precio_promocional) > 0;
    const isOutOfStock = producto.stock <= 0;
    const currentPrice = isOffer ? producto.precio_promocional : producto.precio;
    const oldPrice = isOffer ? `<span class="text-sm text-gray-400 line-through mr-2">Bs ${parseFloat(producto.precio).toFixed(2)}</span>` : '';

    // Lógica de Stock e Icono
    const stockClass = isOutOfStock ? 'text-error-light' : 'text-shipping-light';
    const stockText = isOutOfStock ? 'Agotado' : `Stock ${producto.stock} unidades`;

    let stockIconHtml = '';
    if (isOutOfStock) {
        stockIconHtml = `<svg class="w-4 h-4 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                         </svg>`;
    } else {
        stockIconHtml = `<svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                         </svg>`;
    }

    // Lógica de Estrellas
    const MAX_RATING = 5;
    const rating = Math.round(parseFloat(producto.promedio_calificacion));
    let ratingHtml = '';
    for (let i = 0; i < rating; i++) {
        ratingHtml += createStarSvg(true);
    }
    for (let i = rating; i < MAX_RATING; i++) {
        ratingHtml += createStarSvg(false);
    }
    const ratingContainerClass = 'text-rating-light';
    let imgUrl = producto.imagen_principal_ruta || 'http://localhost/emarket_bolivia/frontend/img/default-avatar.png';
    console.log("imgh: ", imgUrl);
    if (!imgUrl.startsWith('http')) {
        imgUrl = apiUrl + '/' + imgUrl;
        console.log("img: ", imgUrl);
    }
    return `
    <a href="#" 
       onclick="verProducto(${producto.id_producto}); return false;" 
       class="block bg-surface-light dark:bg-surface-dark rounded-lg shadow-md overflow-hidden transition-shadow duration-300 hover:shadow-xl group">
        <div class="flex flex-col md:flex-row">
            <div class="md:w-1/3">
                <div class="h-48 md:h-full bg-cover bg-center"
                    style='background-image: url("${imgUrl}");'>
                </div>
            </div>
            <div class="p-6 md:w-2/3 flex flex-col justify-between">
                <div>
                    ${isOffer ? '<span class="text-xs font-bold uppercase text-offer-light">producto en oferta</span>' : ''}
                    
                    <h3 class="text-xl font-bold mt-2 text-primary group-hover:underline">${producto.nombre}</h3>
                    
                    <p class="text-sm text-subtle-light mt-2">${producto.estado_producto.charAt(0).toUpperCase() + producto.estado_producto.slice(1)}</p>
                    
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">${producto.descripcion}</p>
                    
                    <div class="flex items-center mt-3">
                        <div class="flex ${ratingContainerClass}">
                            ${ratingHtml}
                        </div>
                        <span class="text-subtle-light dark:text-subtle-dark ml-2 text-sm">(${producto.total_opiniones} reviews)</span>
                    </div>
                </div>
                <div class="mt-4 flex flex-col sm:flex-row sm:items-end sm:justify-between">
                    <div class="mb-3 sm:mb-0">
                        <p class="text-2xl font-bold text-primary"> Bs ${parseFloat(currentPrice).toFixed(2)} ${oldPrice}</p>
                        
                        <div class="flex items-center">
                            ${stockIconHtml}
                            <p class="text-sm ${stockClass} font-semibold">${stockText}</p>
                        </div>
                    </div>
                    
                    <button
                        class="w-full sm:w-auto bg-primary text-white font-bold py-2 px-6 rounded-lg hover:bg-primary/80 transition-colors duration-300"
                        data-id-producto="${producto.id_producto}"
                        onclick="event.stopPropagation(); event.preventDefault(); agregarAlCarrito(${producto.id_producto});">
                        Añadir al Carrito
                    </button>
                </div>
            </div>
        </div>
    </a>
`;
}

// 2. createStarSvg (auxiliar para createProductCard)
function createStarSvg(isFilled) {
    const colorClass = isFilled ? 'text-yellow-400' : 'text-gray-300';
    return `<svg class="w-5 h-5 ${colorClass}" fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                </path>
            </svg>`;
}

// 3. updateHeader (contador de resultados)
function updateHeader(data) {
    if (!headerContainer) return;

    const total = data.total;
    const porPagina = data.por_pagina;
    const paginaActual = data.pagina;
    const startCount = (paginaActual - 1) * porPagina + 1;
    const endCount = Math.min(paginaActual * porPagina, total);

    let countElement = headerContainer.querySelector('.results-count');
    if (!countElement) {
        countElement = document.createElement('p');
        countElement.className = 'results-count mt-1 text-sm text-gray-500 dark:text-gray-400';
        headerContainer.appendChild(countElement);
    }

    countElement.textContent = `${startCount}-${endCount} de ${total} resultados`;
    console.log(`DEBUG - Contador actualizado: ${startCount}-${endCount} de ${total}`);
}

// 4. createPagination (con handler personalizado)
function createPagination(data, handler) {
    if (!paginationContainer) return;

    const totalPaginas = data.total_paginas;
    const paginaActual = data.pagina;
    const MAX_PAGES_VISIBLE = 5;

    let pagesHtml = '';

    const createPageLink = (pageNumber, isActive = false, text = pageNumber) => {
        const activeClass = isActive ? 'text-white bg-primary rounded-lg' : 'text-subtle-light dark:text-subtle-dark hover:text-primary dark:hover:text-primary hover:bg-background-light dark:hover:bg-background-dark rounded-lg';
        return `<a class="px-4 py-2 ${activeClass}" href="#" data-page="${pageNumber}">${text}</a>`;
    };

    // Botón Anterior
    const prevDisabled = paginaActual === 1;
    pagesHtml += `<a class="px-4 py-2 text-subtle-light dark:text-subtle-dark hover:text-primary dark:hover:text-primary rounded-lg ${prevDisabled ? 'opacity-50 cursor-not-allowed' : ''}"
                        href="#" data-page="${paginaActual - 1}" ${prevDisabled ? 'disabled' : ''}>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path clip-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" fill-rule="evenodd"></path>
                        </svg>
                    </a>`;

    // Lógica para mostrar las páginas
    let startPage = Math.max(1, paginaActual - Math.floor(MAX_PAGES_VISIBLE / 2));
    let endPage = Math.min(totalPaginas, startPage + MAX_PAGES_VISIBLE - 1);

    if (endPage - startPage + 1 < MAX_PAGES_VISIBLE) {
        startPage = Math.max(1, endPage - MAX_PAGES_VISIBLE + 1);
    }

    if (startPage > 1) {
        pagesHtml += createPageLink(1);
        if (startPage > 2) {
            pagesHtml += `<span class="px-4 py-2 text-subtle-light dark:text-subtle-dark">...</span>`;
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        pagesHtml += createPageLink(i, i === paginaActual);
    }

    if (endPage < totalPaginas) {
        if (endPage < totalPaginas - 1) {
            pagesHtml += `<span class="px-4 py-2 text-subtle-light dark:text-subtle-dark">...</span>`;
        }
        pagesHtml += createPageLink(totalPaginas);
    }

    // Botón Siguiente
    const nextDisabled = paginaActual === totalPaginas;
    pagesHtml += `<a class="px-4 py-2 text-subtle-light dark:text-subtle-dark hover:text-primary dark:hover:text-primary rounded-lg ${nextDisabled ? 'opacity-50 cursor-not-allowed' : ''}"
                        href="#" data-page="${paginaActual + 1}" ${nextDisabled ? 'disabled' : ''}>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path clip-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" fill-rule="evenodd"></path>
                        </svg>
                    </a>`;

    paginationContainer.innerHTML = pagesHtml;

    // Agregar evento de click a los enlaces de paginación
    paginationContainer.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const page = parseInt(e.currentTarget.dataset.page);
            if (!isNaN(page) && page >= 1 && page <= totalPaginas && !e.currentTarget.hasAttribute('disabled')) {
                handler(page);
            }
        });
    });

    console.log('DEBUG - Paginación renderizada para página', paginaActual);
}

/**
 * Configura los event listeners iniciales.
 */
function setupEventListeners() {
    // Carga inicial: Aplica búsqueda desde URL
    applySearchFromUrl();

    // --- Botón Aplicar Filtros ---
    const applyFiltersBtn = document.getElementById('apply-filters');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', () => {
            // Filtros base: palabras de la URL actual (no cambian)
            const urlParams = new URLSearchParams(window.location.search);
            const palabrasBase = urlParams.getAll('palabras[]');

            if (palabrasBase.length === 0) {
                console.warn('DEBUG - No hay términos base para filtrar.');
                return;
            }

            const baseFilters = { palabras: palabrasBase };
            const activeFilters = getActiveFilters(baseFilters);
            console.log('DEBUG - Filtros activos aplicados:', activeFilters);

            // Actualiza URL y carga productos
            updateUrlFromSearch(activeFilters, 1);
            fetchSearchProducts(activeFilters, 1);
            // ← NUEVO: Recarga marcas con filtros aplicados (se ejecuta dentro de fetchSearchProducts)
        });
    }

    // --- Botón Limpiar Filtros ---
    const clearFiltersBtn = document.getElementById('clear-filters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', () => {
            // Resetear inputs
            document.querySelectorAll('#brand-filters-container input[type="checkbox"]').forEach(cb => cb.checked = false);
            document.getElementById('price-min').value = '';
            document.getElementById('price-max').value = '';
            document.querySelectorAll('input[name="rating-filter"]').forEach(r => r.checked = false);
            document.querySelectorAll('input[name="categoria-filter"]').forEach(r => r.checked = false);
            document.querySelectorAll('input[name="condition-filter"]').forEach(r => r.checked = false);
            document.getElementById('availability-filter').checked = false;

            // Filtros limpios: solo palabras base + pagina 1
            const urlParams = new URLSearchParams(window.location.search);
            const palabrasBase = urlParams.getAll('palabras[]');

            if (palabrasBase.length === 0) {
                console.warn('DEBUG - No hay términos base para limpiar.');
                return;
            }

            const clearFilters = { palabras: palabrasBase };
            updateUrlFromSearch(clearFilters, 1);
            fetchSearchProducts(clearFilters, 1);
            // ← NUEVO: Recarga marcas con filtros limpios (se ejecuta dentro de fetchSearchProducts)

            console.log('DEBUG - Filtros limpiados y recarga sin filtros.');
        });
    }

    console.log('DEBUG - Event listeners de resultados de búsqueda configurados.');
}

// Inicia la configuración cuando el DOM esté listo.
document.addEventListener('DOMContentLoaded', setupEventListeners);