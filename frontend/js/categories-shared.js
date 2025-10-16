// --- CONSTANTES COMUNES (reutilizadas de producto-categoria.js) ---
var PRODUCTOS_POR_PAGINA = 10;  // Solo si usas AJAX

const CATEGORIAS_MAP = {
    "1": "Electrónica",
    "2": "Hogar y Jardín",
    "3": "Moda y Accesorios",
    "4": "Deportes y Ocio",
    "5": "Automotriz",
    "6": "Libros y Medios",
    "7": "Juguetes y Juegos"
};

function getCategoriaNombrePorId(id) {
    return CATEGORIAS_MAP[id] || 'Categoría Desconocida';
}


// Detecta si estamos en la página de categorías
function isCategoriasPage() {
    const pathname = window.location.pathname;
    console.log('Debug - Pathname actual:', pathname);
    return pathname.includes('categorias-productos.php');
}
/**
 * Función reutilizable para manejar clics (adaptada: AJAX o navegación).
 */
function handleCategoryClick(element) {
    const isCategory = element.hasAttribute('data-id-categoria');
    let titleText = '', params = '';

    if (isCategory) {
        const id = element.getAttribute('data-id-categoria');
        const span = element.querySelector('span');
        titleText = span ? span.textContent.trim() : 'Categoría';
        params = `id_categoria=${id}`;
    } else {
        const id = element.getAttribute('data-id-subcategoria');
        const subcategoryName = element.textContent.trim();
        const categoriaPadreId = element.getAttribute('data-categoria-padre');
        const categoriaPadreNombre = getCategoriaNombrePorId(categoriaPadreId);
        titleText = `${categoriaPadreNombre} → ${subcategoryName}`;
        params = `id_subcategoria=${id}&id_categoria=${categoriaPadreId}`;
    }

    if (isCategoriasPage()) {
        console.log("isCategoriaPage: ", isCategoriasPage());
        const filters = {};
        if (params.includes('id_categoria=')) filters.categoriaId = params.match(/id_categoria=(\d+)/)[1];
        else if (params.includes('id_subcategoria=')) filters.subcategoriaId = params.match(/id_subcategoria=(\d+)/)[1];
        fetchAndRenderProductsByFilters(filters, 1);  // Llama a tu función existente
    } else {
        // En otras páginas: Navega directamente
        window.location.href = `categorias-productos.php?${params}`;
    }

    // Opcional: Actualiza título local si estás en categorías (reutiliza tu lógica)
    if (isCategoriasPage() && document.getElementById('products-title')) {
        document.getElementById('products-title').textContent = titleText;
    }
}

/**
 * Configura listeners solo para el aside del navbar (evita conflictos con aside fijo en categorías).
 */
function setupNavbarCategoryListeners() {
    const sidebar = document.getElementById('sidebar-categorias');
    if (!sidebar) return;

    // Event delegation para clics en el sidebar del navbar
    sidebar.addEventListener('click', (event) => {
        const target = event.target.closest('[data-id-categoria], [data-id-subcategoria]');
        if (!target) return;

        event.preventDefault();
        handleCategoryClick(target);

        // Resaltar activo (opcional)
        sidebar.querySelectorAll('.active-link').forEach(el => el.classList.remove('active-link'));
        target.classList.add('active-link');
    });
}

// Inicializa cuando DOM esté listo
document.addEventListener('DOMContentLoaded', setupNavbarCategoryListeners);