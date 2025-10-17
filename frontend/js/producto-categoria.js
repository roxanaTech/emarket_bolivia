// --- CONSTANTES ---
var PRODUCTOS_POR_PAGINA = 10;

// Contenedores del DOM (solo si existen, para compatibilidad con otras páginas)
const headerContainer = document.getElementById('header-container');
const productCardsContainer = document.getElementById('product-cards-container');
const paginationContainer = document.getElementById('pagination-container');
const productsContainer = document.getElementById('products-container');
const productsTitle = document.getElementById('products-title');

// Detecta si estamos en la página de categorías (para decidir AJAX vs navegación)
function isCategoriasPage() {
    return window.location.pathname.includes('categorias-productos.php');
}
console.log("isCategoriaPage: ", isCategoriasPage());
const CATEGORIAS_MAPA = {
    "1": "Electrónica",
    "2": "Hogar y Jardín",
    "3": "Moda y Accesorios",
    "4": "Deportes y Ocio",
    "5": "Automotriz",
    "6": "Libros y Medios",
    "7": "Juguetes y Juegos"
};
/**
 * Extrae todos los filtros posibles de la URL actual.
 */
function getFiltersFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    const filters = {};

    // Categoría/Subcategoría
    const subcat = urlParams.get('id_subcategoria');
    const cat = urlParams.get('id_categoria');
    if (subcat) {
        filters.subcategoriaId = subcat;
        if (cat) filters.categoriaPadreId = cat;
    } else if (cat) {
        filters.categoriaId = cat;
    }

    // Marcas (soporta marca[]=A&marca[]=B)
    const marcas = urlParams.getAll('marca[]'); // ← ¡getAll es clave!
    if (marcas.length > 0) {
        filters.marcas = marcas;
    }

    // Precio
    const precioMin = urlParams.get('precio_min');
    const precioMax = urlParams.get('precio_max');
    if (precioMin && !isNaN(precioMin)) filters.precio_min = parseFloat(precioMin);
    if (precioMax && !isNaN(precioMax)) filters.precio_max = parseFloat(precioMax);

    // Calificación
    const califMin = urlParams.get('calificacion_min');
    if (califMin && !isNaN(califMin)) filters.calificacion_min = parseInt(califMin, 10);

    // Página
    const pagina = parseInt(urlParams.get('pagina'), 10) || 1;

    return { filters, pagina };
}
/**
 * Actualiza la URL del navegador con los filtros y página actuales (sin recargar).
 * @param {Object} filters - Filtros base (incluye categoriaId/subcategoriaId, marcas, etc.)
 * @param {number} page - Página actual
 */
function updateUrlFromFilters(filters, page = 1) {
    const params = new URLSearchParams();

    // Categoría/Subcategoría (solo uno)
    if (filters.subcategoriaId) {
        params.append('id_subcategoria', filters.subcategoriaId);
        if (filters.categoriaPadreId) {
            params.append('id_categoria', filters.categoriaPadreId);
        }
    } else if (filters.categoriaId) {
        params.append('id_categoria', filters.categoriaId);
    }

    // Filtros avanzados (solo si existen)
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

    // Paginación
    params.append('pagina', page);

    const newUrl = `${window.location.pathname}?${params.toString()}`;
    window.history.pushState({ path: newUrl }, '', newUrl);
    console.log(`URL actualizada a: ${newUrl}`);
}
/**
 * Aplica los filtros desde la URL (usado en carga inicial y al recargar).
 */
async function applyFiltersFromUrl() {
    const { filters, pagina } = getFiltersFromUrl();

    if (Object.keys(filters).length === 0) {
        // No hay filtros: cargar primera categoría por defecto
        const defaultCategoryElement = document.querySelector('[data-id-categoria]');
        if (defaultCategoryElement) {
            handleCategoryClick(defaultCategoryElement);
        }
        return;
    }

    // Actualizar título
    updateTitleFromFilters(filters);

    // Cargar productos con filtros
    if (filters.marcas ||
        typeof filters.precio_min !== 'undefined' ||
        typeof filters.precio_max !== 'undefined' ||
        typeof filters.calificacion_min !== 'undefined') {
        // Hay filtros avanzados → usar endpoint de búsqueda
        await fetchProductsByAdvancedFilters(filters, pagina);
    } else {
        // Solo categoría/subcategoría → usar endpoints normales
        if (filters.subcategoriaId) {
            await fetchAndRenderProducts('subcategoria', filters.subcategoriaId, pagina, filters.categoriaPadreId);
        } else if (filters.categoriaId) {
            await fetchAndRenderProducts('categoria', filters.categoriaId, pagina);
        }
    }

    // ✅ Cargar marcas en el aside (siempre que haya categoría/subcat)
    if (filters.categoriaId || filters.subcategoriaId) {
        loadBrands(filters);
    }

    // ✅ Restaurar valores en los inputs del formulario de filtros
    restoreFilterInputs(filters);
}
/**
 * Recoge los valores actuales de los filtros del DOM.
 * @param {Object} baseFilters - Debe contener categoriaId o subcategoriaId (obligatorio)
 * @returns {Object} Objeto con los filtros aplicables para la API
 */
function getActiveFilters(baseFilters) {
    const filters = { ...baseFilters }; // Clonamos para no mutar el original

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

    return filters;
}
/**
 * Carga marcas dinámicamente basado en categoría/subcat y las renderiza en #brand-filters-container.
 * @param {object} filters - Los mismos filters de productos (categoriaId o subcategoriaId).
 */
async function loadBrands(filters) {
    if (!document.getElementById('brand-filters-container')) {
        console.warn('Contenedor de marcas no encontrado');
        return;
    }

    let endpoint = '';
    if (filters.subcategoriaId) {
        endpoint = `/productos/marcas?subcategoria=${filters.subcategoriaId}`;
    } else if (filters.categoriaId) {
        endpoint = `/productos/marcas?categoria=${filters.categoriaId}`;
    } else {
        console.error('No hay ID de categoría/subcat para cargar marcas');
        return;
    }

    const fullUrl = apiUrl + endpoint;
    console.log(`Cargando marcas desde: ${fullUrl}`);

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
                <label class="flex items-center gap-2 ">
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
                    <label class="flex items-center gap-2 text-sm">
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

        console.log(`Cargadas ${topMarcas.length} marcas top`);
    } catch (error) {
        console.error('Error al cargar marcas:', error);
        document.getElementById('brand-filters-container').innerHTML = '<p class="text-sm text-gray-500">Error al cargar marcas</p>';
    }
}
function getCategoriaNombrePorId(id) {
    return CATEGORIAS_MAP[id] || 'Categoría Desconocida';
}

/**
 * Función para actualizar título basado en filtros (reutilizable para carga inicial desde URL).
 */
function updateTitleFromFilters(filters) {
    if (!productsTitle) return;

    let titleText = '';
    if (filters.categoriaId) {
        titleText = getCategoriaNombrePorId(filters.categoriaId);
    } else if (filters.subcategoriaId) {
        const categoriaPadreId = filters.categoriaPadreId || document.querySelector(`[data-id-subcategoria="${filters.subcategoriaId}"]`)?.getAttribute('data-categoria-padre');
        const categoriaPadreNombre = getCategoriaNombrePorId(categoriaPadreId);
        const subcatElement = document.querySelector(`[data-id-subcategoria="${filters.subcategoriaId}"]`);
        const subcategoryName = subcatElement ? subcatElement.textContent.trim() : 'Subcategoría Desconocida';
        titleText = `${categoriaPadreNombre} → ${subcategoryName}`;
    }

    if (titleText) {
        productsTitle.textContent = titleText;
        console.log(`Título actualizado desde URL a: ${titleText}`);
    }
}
/**
 * Llama al endpoint /productos/buscar con los filtros aplicados.
 * @param {Object} filters - Filtros ya procesados (incluye categoriaId o subcategoriaId)
 * @param {number} page - Página a solicitar
 */
async function fetchProductsByAdvancedFilters(filters, page = 1) {
    // Validar que tengamos categoría o subcategoría
    if (!filters.categoriaId && !filters.subcategoriaId) {
        console.error('No se puede buscar sin categoría o subcategoría');
        return;
    }

    // Construir URL de búsqueda
    const params = new URLSearchParams();

    // Solo uno de estos debe ir
    if (filters.subcategoriaId) {
        params.append('id_subcategoria', filters.subcategoriaId);
    } else if (filters.categoriaId) {
        params.append('id_categoria', filters.categoriaId);
    }

    // Marcas (múltiples)
    if (filters.marcas && filters.marcas.length > 0) {
        filters.marcas.forEach(marca => params.append('marca[]', marca));
    }

    // Precio
    if (typeof filters.precio_min !== 'undefined') {
        params.append('precio_min', filters.precio_min);
    }
    if (typeof filters.precio_max !== 'undefined') {
        params.append('precio_max', filters.precio_max);
    }

    // Calificación
    if (typeof filters.calificacion_min !== 'undefined') {
        params.append('calificacion_min', filters.calificacion_min);
    }

    // Paginación
    params.append('pagina', page);
    params.append('por_pagina', PRODUCTOS_POR_PAGINA);

    const fullUrl = `${apiUrl}/productos/buscar?${params.toString()}`;
    console.log('Llamando a endpoint de búsqueda:', fullUrl);

    if (productCardsContainer) productCardsContainer.innerHTML = '<p class="text-center text-xl text-primary">Aplicando filtros...</p>';

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
            if (productCardsContainer) productCardsContainer.innerHTML = '<p class="text-center text-gray-500">No hay productos con esos filtros.</p>';
            updateHeader({ total: 0, por_pagina: PRODUCTOS_POR_PAGINA, pagina: 1 });
            return;
        }

        // Renderizar
        updateHeader(data);
        const productCardsHtml = data.productos.map(createProductCard).join('');
        if (productCardsContainer) productCardsContainer.innerHTML = productCardsHtml;

        /// Paginación (envuelta para actualizar URL)
        const originalHandler = (newPage) => fetchProductsByAdvancedFilters(filters, newPage);
        const paginationHandler = (newPage) => {
            updateUrlFromFilters(filters, newPage);
            originalHandler(newPage);
        };
        createPagination(data, paginationHandler);
        if (filters.categoriaId || filters.subcategoriaId) {
            loadBrands(filters); // ← asegura que las marcas se actualicen
        }

    } catch (error) {
        console.error('Error al aplicar filtros:', error);
        if (productCardsContainer) {
            productCardsContainer.innerHTML = `<p class="text-center text-error-light">Error: ${error.message}</p>`;
        }
    }
}
/**
 * Función wrapper para fetchAndRenderProducts usando filters (solo en categorías page).
 */
async function fetchAndRenderProductsByFilters(filters, page = 1) {
    let type, id, categoriaPadreId = null;
    if (filters.categoriaId) {
        type = 'categoria';
        id = filters.categoriaId;
    } else if (filters.subcategoriaId) {
        type = 'subcategoria';
        id = filters.subcategoriaId;
        categoriaPadreId = filters.categoriaPadreId;
    }
    if (!type || !id) {
        console.error('Filtros inválidos.');
        return;
    }
    console.log(`Iniciando fetch: ${type} ID ${id}, página ${page}`);
    await fetchAndRenderProducts(type, id, page, categoriaPadreId);
}

/**
 * Función de utilidad para manejar el clic en Categoría/Subcategoría y actualizar el título.
 * @param {HTMLElement} element - El elemento que fue clickeado.
 */
function handleCategoryClick(element) {
    console.log('handleCategoryClick llamado en:', element);
    const isCategory = element.hasAttribute('data-id-categoria');
    let titleText = '', params = '';
    let id, categoriaPadreId;  // Usa let para scoping local

    if (isCategory) {
        // CASO 1: Categoría principal
        id = element.getAttribute('data-id-categoria');
        const span = element.querySelector('span');
        titleText = span ? span.textContent.trim() : 'Categoría';
        params = `id_categoria=${id}`;
        console.log(`Categoría clickeada: ${titleText} (ID: ${id})`);
    } else {
        // CASO 2: Subcategoría
        id = element.getAttribute('data-id-subcategoria');
        const subcategoryName = element.textContent.trim();

        categoriaPadreId = element.getAttribute('data-categoria-padre');
        const categoriaPadreNombre = getCategoriaNombrePorId(categoriaPadreId);

        titleText = `${categoriaPadreNombre} → ${subcategoryName}`;
        params = `id_subcategoria=${id}&id_categoria=${categoriaPadreId}`;
        console.log(`Subcategoría clickeada: ${titleText} (ID: ${id}, Padre: ${categoriaPadreId})`);
    }
    // ACTUALIZAR LA URL DEL NAVEGADOR (sin recargar)
    const newUrl = `${window.location.pathname}?${params}`;
    window.history.pushState({ path: newUrl }, '', newUrl);

    // Actualizar título SIEMPRE (solo si existe, en categorías page)
    if (productsTitle) {
        productsTitle.textContent = titleText;
        console.log(`Título actualizado a: ${titleText}`);
    }

    if (isCategoriasPage()) {
        // En página de categorías: Llama a AJAX
        const filters = {};
        if (isCategory) {
            filters.categoriaId = id;  // Usa id directamente (más limpio)
        } else {
            // Para subcat: Prioriza subcategoriaId directamente
            filters.subcategoriaId = id;
            // Opcional: Agrega padre si lo necesitas en updateTitleFromFilters
            filters.categoriaPadreId = categoriaPadreId;
        }

        // Carga productos
        fetchAndRenderProductsByFilters(filters, 1);

        // Carga marcas (se ejecuta cada vez, después de productos)
        console.log('Llamando loadBrands con filters:', filters);  // DEBUG: Ver si llega aquí cada vez
        loadBrands(filters);
    } else {
        // En otras páginas: Navega directamente
        window.location.href = `categorias-productos.php?${params}`;
    }
}

/**
 * Configura event delegation para clics en categorías/subcategorías (funciona para ambos asides: fijo y expandable).
 */
function setupCategoryListeners() {
    // Listener global para capturar clics en cualquier aside (fijo o navbar)
    document.addEventListener('click', (event) => {
        const target = event.target.closest('[data-id-categoria], [data-id-subcategoria]');
        if (!target) return;

        console.log('Click detectado en:', event.target, 'Closest target:', target);
        event.preventDefault();
        event.stopPropagation();

        handleCategoryClick(target);

        // Resaltar activo (opcional, en el contenedor más cercano)
        const parentContainer = target.closest('aside');
        if (parentContainer) {
            parentContainer.querySelectorAll('.active-link').forEach(el => el.classList.remove('active-link'));
            target.classList.add('active-link');
        }
    });
    console.log('Event delegation configurado para categorías/subcategorías');
}
/*------------------------------------------------------------
Renderizado de Productos (solo se usa en categorias-productos.php)
--------------------------------------------------------------*/
/**
 * 1. Crea la estrella SVG.
 */
function createStarSvg(isFilled) {
    const colorClass = isFilled ? 'text-yellow-400' : 'text-gray-300';
    return `<svg class="w-5 h-5 ${colorClass}" fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                </path>
            </svg>`;
}

/**
 * 2. Genera el HTML para una tarjeta de producto.
 */
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
                    <div class="mt-4 flex items-end justify-between">
                        <div>
                            <p class="text-2xl font-bold text-primary">${oldPrice} Bs ${parseFloat(currentPrice).toFixed(2)}</p>
                            
                            <div class="flex items-center">
                                ${stockIconHtml}
                                <p class="text-sm ${stockClass} font-semibold">${stockText}</p>
                            </div>
                        </div>
                        
                        <button
                            class="bg-primary text-white font-bold py-2 px-6 rounded-lg hover:bg-primary/80 transition-colors duration-300"
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

/**
 * 3. Actualiza SOLO el contador de resultados.
 */
function updateHeader(data) {
    if (!headerContainer) return;  // Solo si existe (categorias page)

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
    console.log(`Contador actualizado: ${startCount}-${endCount} de ${total}`);
}

/**
 * 4. Genera los enlaces de paginación.
 */
function createPagination(data, handler) {
    if (!paginationContainer) return;  // Solo si existe

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
}

/**
 * 5. Función principal para obtener y mostrar productos (solo en categorias-productos.php).
 */
async function fetchAndRenderProducts(type, id, page = 1, categoriaPadreId = null) {
    if (!id || !isCategoriasPage()) return;  // Solo ejecuta si es categorias page

    // Construir el endpoint
    let endpoint = '';
    if (type === 'categoria') {
        endpoint = `/productos/listarPorCategoria/${id}?pagina=${page}&por_pagina=${PRODUCTOS_POR_PAGINA}`;
    } else if (type === 'subcategoria') {
        endpoint = `/productos/listarPorSubcategoria/${id}?pagina=${page}&por_pagina=${PRODUCTOS_POR_PAGINA}`;
    } else {
        console.error('Tipo de filtro no válido.');
        return;
    }

    const fullUrl = apiUrl + endpoint;
    console.log(`Endpoint llamado: ${fullUrl}`);

    if (productCardsContainer) productCardsContainer.innerHTML = '<p class="text-center text-xl text-primary">Cargando productos...</p>';
    if (paginationContainer) paginationContainer.innerHTML = '';

    try {
        const response = await fetch(fullUrl);
        console.log(`Respuesta HTTP: ${response.status} ${response.statusText}`);
        if (!response.ok) {
            throw new Error(`Error en la solicitud: ${response.statusText}`);
        }
        const jsonResponse = await response.json();
        console.log('Respuesta JSON:', jsonResponse);

        if (jsonResponse.status !== 'success' || !jsonResponse.data) {
            throw new Error(jsonResponse.mensaje || 'Respuesta de API inválida');
        }

        const data = jsonResponse.data;

        // 1. Limpiar contenedores
        if (productCardsContainer) productCardsContainer.innerHTML = '';
        if (paginationContainer) paginationContainer.innerHTML = '';

        if (data.productos.length === 0) {
            if (productCardsContainer) productCardsContainer.innerHTML = '<p class="text-center text-xl text-gray-500">No se encontraron productos.</p>';
            updateHeader({
                total: 0,
                por_pagina: PRODUCTOS_POR_PAGINA,
                pagina: 1
            });
            return;
        }

        // 2. Actualizar Contador
        updateHeader(data);

        // 3. Renderizar Tarjetas de Productos
        const productCardsHtml = data.productos.map(createProductCard).join('');
        if (productCardsContainer) productCardsContainer.innerHTML = productCardsHtml;
        console.log(`Renderizados ${data.productos.length} productos`);

        // 4. Renderizar Paginación
        // Paginación (envuelta para actualizar URL)
        const baseFilters = type === 'subcategoria'
            ? { subcategoriaId: id, categoriaPadreId }
            : { categoriaId: id };
        const originalHandler = (newPage) => fetchAndRenderProducts(type, id, newPage, categoriaPadreId);
        const paginationHandler = (newPage) => {
            updateUrlFromFilters(baseFilters, newPage);
            originalHandler(newPage);
        };
        createPagination(data, paginationHandler);
        createPagination(data, paginationHandler);

    } catch (error) {
        console.error('Error al obtener productos:', error);
        if (productCardsContainer) productCardsContainer.innerHTML = `<p class="text-center text-xl text-error-light">Error al cargar los productos: ${error.message}</p>`;
        // Limpiar contador en error
        const countElement = headerContainer ? headerContainer.querySelector('.results-count') : null;
        if (countElement) countElement.remove();
    }
}

/**
 * 6. Configura la carga inicial (solo en categorias-productos.php).
 */
/**
 * 6. Configura la carga inicial (solo en categorias.php).
 */
function setupEventListeners() {
    setupCategoryListeners();

    // Carga inicial: Solo si es categorias.php y hay params en URL
    if (isCategoriasPage()) {
        applyFiltersFromUrl(); // ← esto reemplaza toda la lógica anterior
    }
    // --- Botón Aplicar Filtros ---
    const applyFiltersBtn = document.getElementById('apply-filters');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', () => {
            // Determinar los filtros base (categoría o subcategoría actual)
            let baseFilters = {};
            const urlParams = new URLSearchParams(window.location.search);
            const currentSubcat = urlParams.get('id_subcategoria');
            const currentCat = urlParams.get('id_categoria');

            if (currentSubcat) {
                baseFilters.subcategoriaId = currentSubcat;
                baseFilters.categoriaPadreId = currentCat;  // ← Agrega esto
            } else if (currentCat) {
                baseFilters.categoriaId = currentCat;
            } else {
                console.warn('No se pudo determinar categoría/subcategoría actual para filtrar');
                return;
            }

            const activeFilters = getActiveFilters(baseFilters);
            console.log('Filtros activos:', activeFilters);

            // ← NUEVO: Actualiza URL antes de fetch
            updateUrlFromFilters(activeFilters, 1);

            // Si no hay ningún filtro adicional, podrías decidir si llamar a la lista normal
            // Pero aquí asumimos que el usuario quiere aplicar lo que seleccionó
            fetchProductsByAdvancedFilters(activeFilters, 1);
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

            // ← NUEVO: Construye filtros limpios y actualiza URL
            const urlParams = new URLSearchParams(window.location.search);
            const currentSubcat = urlParams.get('id_subcategoria');
            const currentCat = urlParams.get('id_categoria');

            const clearFilters = {};
            if (currentSubcat) {
                clearFilters.subcategoriaId = currentSubcat;
                clearFilters.categoriaPadreId = currentCat;
            } else if (currentCat) {
                clearFilters.categoriaId = currentCat;
            }

            updateUrlFromFilters(clearFilters, 1);

            // ← CAMBIO: Usa el wrapper para pasar categoriaPadreId si es subcat
            fetchAndRenderProductsByFilters(clearFilters, 1);
        });
    }
}
/**
* Restaura los valores de los inputs de filtros según los filtros activos.
*/
function restoreFilterInputs(filters) {
    // Marcas
    if (filters.marcas) {
        filters.marcas.forEach(marca => {
            const checkbox = document.querySelector(`#brand-filters-container input[value="${marca}"]`);
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
}

// Inicia la configuración cuando el DOM esté listo.
document.addEventListener('DOMContentLoaded', setupEventListeners);