// --- CONSTANTES ---
var OFERTAS_POR_PAGINA = 12;

// Contenedores del DOM
const headerContainer = document.getElementById('offer-header-container');
const offerCardsContainer = document.getElementById('offer-cards-container');
const offerPaginationContainer = document.getElementById('pagination-container');
const offersTitle = document.getElementById('offer-title');

/**
 * Extrae los filtros de la URL actual.
 * @returns {Object} { filters: object con filtros, pagina: number }
 */
function getFiltersFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    const pagina = parseInt(urlParams.get('pagina'), 10) || 1;

    const filters = {};

    // Categoría
    const categoria = urlParams.get('id_categoria');
    if (categoria) filters.categoria = parseInt(categoria, 10);

    // Rango de precios
    const precioMin = urlParams.get('precio_min');
    const precioMax = urlParams.get('precio_max');
    if (precioMin && !isNaN(precioMin)) filters.precio_min = parseFloat(precioMin);
    if (precioMax && !isNaN(precioMax)) filters.precio_max = parseFloat(precioMax);

    // Calificación
    const califMin = urlParams.get('calificacion_min');
    if (califMin && !isNaN(califMin)) filters.calificacion_min = parseInt(califMin, 10);

    console.log('DEBUG - Filtros extraídos de URL:', { filters, pagina });

    return { filters, pagina };
}

/**
 * Actualiza el título fijo para ofertas.
 */
function updateTitleForOffers() {
    if (!offersTitle) {
        console.warn('DEBUG - Elemento #offer-title no encontrado.');
        return;
    }

    offersTitle.textContent = 'Productos en Oferta';
    console.log('DEBUG - Título actualizado para ofertas');
}

/**
 * Aplica la carga inicial de ofertas desde la URL.
 */
async function loadOffersFromUrl() {
    const { filters, pagina } = getFiltersFromUrl();

    updateTitleForOffers();
    await fetchOffers(filters, pagina);
    restoreFilterInputs(filters);
}

/**
 * Recoge los valores actuales de los filtros del DOM.
 * @returns {Object} Objeto con los filtros aplicables
 */
function getActiveFilters() {
    const filters = {};

    // Categoría
    const selectedCategoria = document.getElementById('category');
    if (selectedCategoria && selectedCategoria.value) {
        filters.categoria = parseInt(selectedCategoria.value, 10);
    }

    // Rango de precios (mapeo)
    const selectedPrice = document.getElementById('price');
    if (selectedPrice && selectedPrice.value) {
        const priceValue = parseInt(selectedPrice.value);
        const priceRanges = {
            1: { min: 0, max: 100 },
            2: { min: 100, max: 500 },
            3: { min: 500, max: 1000 },
            4: { min: 1000, max: null }
        };
        const range = priceRanges[priceValue];
        if (range) {
            filters.precio_min = range.min;
            if (range.max !== null) filters.precio_max = range.max;
        }
    }

    // Calificación mínima
    const selectedRating = document.getElementById('rating');
    if (selectedRating && selectedRating.value) {
        filters.calificacion_min = parseInt(selectedRating.value, 10);
    }

    console.log('DEBUG - Filtros activos del DOM para ofertas:', filters);
    return filters;
}

/**
 * Restaura los valores de los selects según filtros de URL.
 * @param {Object} filters - Filtros de la URL
 */
function restoreFilterInputs(filters) {
    // Categoría
    if (filters.categoria) {
        document.getElementById('category').value = filters.categoria;
    }

    // Precio (mapeo inverso aproximado)
    if (filters.precio_min !== undefined) {
        let priceValue = '';
        if (filters.precio_min >= 1000) priceValue = '4';
        else if (filters.precio_min >= 500) priceValue = '3';
        else if (filters.precio_min >= 100) priceValue = '2';
        else priceValue = '1';
        document.getElementById('price').value = priceValue;
    }

    // Calificación
    if (filters.calificacion_min) {
        document.getElementById('rating').value = filters.calificacion_min;
    }

    console.log('DEBUG - Filtros restaurados en el DOM para ofertas');
}

/**
 * Llama al endpoint /productos/buscar con en_oferta=1 y filtros.
 * @param {Object} filters - Filtros
 * @param {number} page - Página
 */
async function fetchOffers(filters, page = 1) {
    const params = new URLSearchParams();
    params.append('en_oferta', 1); // Fijo para ofertas

    if (filters.categoria) params.append('id_categoria', filters.categoria);
    if (typeof filters.precio_min !== 'undefined') params.append('precio_min', filters.precio_min);
    if (typeof filters.precio_max !== 'undefined') params.append('precio_max', filters.precio_max);
    if (typeof filters.calificacion_min !== 'undefined') params.append('calificacion_min', filters.calificacion_min);

    params.append('pagina', page);
    params.append('por_pagina', OFERTAS_POR_PAGINA);

    const fullUrl = `${apiUrl}/productos/buscar?${params.toString()}`;
    console.log('DEBUG - Llamando a endpoint de ofertas:', fullUrl);

    if (offerCardsContainer) offerCardsContainer.innerHTML = '<p class="text-center text-xl text-primary col-span-full">Cargando ofertas...</p>';

    try {
        const response = await fetch(fullUrl);
        if (!response.ok) throw new Error(`Error: ${response.statusText}`);
        const jsonResponse = await response.json();

        if (jsonResponse.status !== 'success' || !jsonResponse.data) {
            throw new Error(jsonResponse.mensaje || 'Respuesta inválida');
        }

        const data = jsonResponse.data;

        if (offerCardsContainer) offerCardsContainer.innerHTML = '';
        if (offerPaginationContainer) offerPaginationContainer.innerHTML = '';

        if (data.productos.length === 0) {
            if (offerCardsContainer) offerCardsContainer.innerHTML = '<p class="text-center text-gray-500 col-span-full">No hay ofertas disponibles con estos filtros.</p>';
            updateHeader(data);
            return;
        }

        updateHeader(data);
        const offerCardsHtml = data.productos.map(createProductCard).join('');
        if (offerCardsContainer) offerCardsContainer.innerHTML = offerCardsHtml;

        const originalHandler = (newPage) => fetchOffers(filters, newPage);
        const paginationHandler = (newPage) => {
            updateUrlFromFilters(filters, newPage);
            originalHandler(newPage);
        };
        createPagination(data, paginationHandler);

    } catch (error) {
        console.error('DEBUG - Error al cargar ofertas:', error);
        if (offerCardsContainer) {
            offerCardsContainer.innerHTML = `<p class="text-center text-error-light col-span-full">Error: ${error.message}</p>`;
        }
    }
}

/**
 * Actualiza la URL con filtros y página.
 * @param {Object} filters - Filtros
 * @param {number} page - Página
 */
function updateUrlFromFilters(filters, page = 1) {
    const params = new URLSearchParams();

    if (filters.categoria) params.append('id_categoria', filters.categoria);
    if (typeof filters.precio_min !== 'undefined') params.append('precio_min', filters.precio_min);
    if (typeof filters.precio_max !== 'undefined') params.append('precio_max', filters.precio_max);
    if (typeof filters.calificacion_min !== 'undefined') params.append('calificacion_min', filters.calificacion_min);

    params.append('pagina', page);

    const newUrl = `${window.location.pathname}?${params.toString()}`;
    window.history.pushState({ path: newUrl }, '', newUrl);
    console.log('DEBUG - URL actualizada para ofertas:', newUrl);
}

/**
 * Crea la tarjeta de producto basada en el JSON.
 * @param {Object} producto - Producto del JSON
 * @returns {string} HTML de la tarjeta
 */
function createProductCard(producto) {
    const evento = producto.evento_asociado;
    if (!evento || !producto.precio_promocional) return ''; // Solo con oferta

    // Formatear valor_descuento (quitar .00)
    let valorDescuentoFormatted = evento.valor_descuento || '0';
    if (valorDescuentoFormatted.endsWith('.00')) {
        valorDescuentoFormatted = valorDescuentoFormatted.replace('.00', '');
    }

    // Badge
    let badgeHtml = '';
    if (evento.tipo_aplicacion === 'porcentaje') {
        badgeHtml = `<div class="absolute top-2 left-2 bg-accent-red text-background-light text-xs font-bold px-2 py-1 rounded">-${valorDescuentoFormatted}%</div>`;
    } else if (evento.tipo_aplicacion === 'monto_fijo' && parseFloat(valorDescuentoFormatted) > 0) {
        badgeHtml = `<div class="absolute top-2 left-2 bg-accent-red text-background-light text-xs font-bold px-2 py-1 rounded">-${valorDescuentoFormatted} Bs</div>`;
    }

    const currentPrice = parseFloat(producto.precio_promocional).toFixed(2);
    const oldPrice = `<div class="text-sm text-black/50 dark:text-background-light/50 line-through">Bs ${parseFloat(producto.precio).toFixed(2)}</div>`;

    // Stock
    const isOutOfStock = parseInt(producto.stock) <= 0;
    const stockClass = isOutOfStock ? 'text-error-light' : 'text-shipping-light';
    const stockText = isOutOfStock ? 'Agotado' : `Stock ${producto.stock} unidades`;
    let stockIconHtml = '';
    if (isOutOfStock) {
        stockIconHtml = `<svg class="w-4 h-4 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                         </svg>`;
    } else {
        stockIconHtml = `<svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>`;
    }
    const stockHtml = `<div class="flex items-center mt-1">${stockIconHtml}<p class="text-sm ${stockClass} font-semibold">${stockText}</p></div>`;

    // Rating (parseFloat para "0.00")
    const ratingValue = parseFloat(producto.promedio_calificacion).toFixed(1);
    const ratingHtml = `
        <div class="flex items-center mt-2 text-yellow-500">
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
            </svg>
            <span class="ml-1 text-sm font-medium text-secondary dark:text-background-light">${ratingValue}</span>
        </div>
    `;

    // Fecha vencimiento
    let fechaVenceHtml = '';
    if (evento.fecha_vencimiento) {
        const fecha = new Date(evento.fecha_vencimiento).toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' });
        fechaVenceHtml = `<p class="text-xs text-accent-red font-bold mt-2">La oferta vence el ${fecha}</p>`;
    }

    return `
    <a href="#" onclick="verProducto(${producto.id_producto}); return false;" class="bg-background-light dark:bg-background-dark/20 rounded-lg overflow-hidden shadow-md hover:shadow-xl transition-shadow duration-300">
        <div class="relative">
            <div class="w-full h-56 bg-cover bg-center" style='background-image: url("${producto.imagen_principal_ruta}");' aria-label="Imagen de ${producto.nombre}"></div>
            ${badgeHtml}
        </div>
        <div class="p-4 flex flex-col flex-grow">
            <h3 class="font-bold text-secondary dark:text-background-light flex-grow">${producto.nombre}</h3>
            <div class="flex items-center mt-2">
                <div class="text-xl font-bold text-secondary dark:text-background-light mr-2">Bs ${currentPrice}</div>
                ${oldPrice}
            </div>
            ${stockHtml}
            ${ratingHtml}
            ${fechaVenceHtml}
            <button
                class="w-full mt-2 bg-primary text-background-light font-bold py-2 px-4 rounded-md hover:bg-primary/90 transition-colors text-sm"
                onclick="event.stopPropagation(); event.preventDefault(); agregarAlCarrito(${producto.id_producto});"
                aria-label="Añadir ${producto.nombre} al carrito">
                Añadir al Carrito
            </button>
        </div>
    </a>`;
}

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

    countElement.textContent = `${startCount}-${endCount} de ${total} ofertas`;
    console.log(`DEBUG - Contador actualizado para ofertas: ${startCount}-${endCount} de ${total}`);
}

function createPagination(data, handler) {
    if (!offerPaginationContainer) return;

    const totalPaginas = data.total_paginas;
    const paginaActual = data.pagina;
    const MAX_PAGES_VISIBLE = 5;

    let pagesHtml = '';

    const createPageLink = (pageNumber, isActive = false, text = pageNumber) => {
        const activeClass = isActive ? 'text-white bg-primary rounded-lg' : 'text-subtle-light dark:text-subtle-dark hover:text-primary dark:hover:text-primary hover:bg-background-light dark:hover:bg-background-dark rounded-lg';
        return `<a class="px-4 py-2 ${activeClass}" href="#" data-page="${pageNumber}">${text}</a>`;
    };

    // Anterior
    const prevDisabled = paginaActual === 1;
    pagesHtml += `<a class="px-4 py-2 text-subtle-light dark:text-subtle-dark hover:text-primary dark:hover:text-primary rounded-lg ${prevDisabled ? 'opacity-50 cursor-not-allowed' : ''}"
                        href="#" data-page="${paginaActual - 1}" ${prevDisabled ? 'disabled' : ''}>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path clip-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" fill-rule="evenodd"></path>
                        </svg>
                    </a>`;

    // Páginas
    let startPage = Math.max(1, paginaActual - Math.floor(MAX_PAGES_VISIBLE / 2));
    let endPage = Math.min(totalPaginas, startPage + MAX_PAGES_VISIBLE - 1);

    if (endPage - startPage + 1 < MAX_PAGES_VISIBLE) {
        startPage = Math.max(1, endPage - MAX_PAGES_VISIBLE + 1);
    }

    if (startPage > 1) {
        pagesHtml += createPageLink(1);
        if (startPage > 2) pagesHtml += `<span class="px-4 py-2 text-subtle-light dark:text-subtle-dark">...</span>`;
    }

    for (let i = startPage; i <= endPage; i++) {
        pagesHtml += createPageLink(i, i === paginaActual);
    }

    if (endPage < totalPaginas) {
        if (endPage < totalPaginas - 1) pagesHtml += `<span class="px-4 py-2 text-subtle-light dark:text-subtle-dark">...</span>`;
        pagesHtml += createPageLink(totalPaginas);
    }

    // Siguiente
    const nextDisabled = paginaActual === totalPaginas;
    pagesHtml += `<a class="px-4 py-2 text-subtle-light dark:text-subtle-dark hover:text-primary dark:hover:text-primary rounded-lg ${nextDisabled ? 'opacity-50 cursor-not-allowed' : ''}"
                        href="#" data-page="${paginaActual + 1}" ${nextDisabled ? 'disabled' : ''}>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path clip-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" fill-rule="evenodd"></path>
                        </svg>
                    </a>`;

    offerPaginationContainer.innerHTML = pagesHtml;

    offerPaginationContainer.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const page = parseInt(e.currentTarget.dataset.page);
            if (!isNaN(page) && page >= 1 && page <= totalPaginas && !e.currentTarget.hasAttribute('disabled')) {
                handler(page);
            }
        });
    });

    console.log('DEBUG - Paginación renderizada para ofertas, página', paginaActual);
}

/**
 * Configura event listeners.
 */
function setupEventListeners() {
    loadOffersFromUrl();

    const applyFiltersBtn = document.getElementById('apply-filters');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', () => {
            const activeFilters = getActiveFilters();
            console.log('DEBUG - Filtros activos aplicados para ofertas:', activeFilters);
            updateUrlFromFilters(activeFilters, 1);
            fetchOffers(activeFilters, 1);
        });
    }

    const clearFiltersBtn = document.getElementById('clear-filters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', () => {
            document.getElementById('category').value = '';
            document.getElementById('price').value = '';
            document.getElementById('rating').value = '';

            const clearFilters = {};
            updateUrlFromFilters(clearFilters, 1);
            fetchOffers(clearFilters, 1);

            console.log('DEBUG - Filtros limpiados y recarga de ofertas.');
        });
    }

    console.log('DEBUG - Event listeners de ofertas configurados.');
}

document.addEventListener('DOMContentLoaded', setupEventListeners);