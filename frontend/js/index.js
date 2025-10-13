document.addEventListener('DOMContentLoaded', function () {
    // --- Configuración Global ---
    const host = window.location.hostname;
    const apiUrl = `http://${host}/emarket_bolivia/backend/public`;

    // --- Elementos del DOM ---
    // ESTA VARIABLE APUNTA A 'productos-destacados'
    const productosContainer = document.getElementById('productos-destacados');


    // ==========================================================
    // === LÓGICA DE CARGA Y RENDERIZADO DE PRODUCTOS ===
    // ==========================================================

    const urlProductos = `${apiUrl}/productos/listarDestacados`;
    //const bodyProductos = { id_vendedor: 10 }; // ID del vendedor destacado

    fetch(urlProductos, {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify()
    })
        .then(response => {
            if (!response.ok) throw new Error('Error en la red: ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.status === 'success' && Array.isArray(data.data)) {
                // Filtrar solo productos con imagen_principal_ruta válida
                const productosConImagen = data.data.filter(p => p.imagen_principal_ruta);

                // Tomar los primeros 4
                const productosAMostrar = productosConImagen.slice(0, 4);

                if (productosAMostrar.length === 0) {
                    productosContainer.innerHTML = '<p class="text-center text-muted">No hay productos destacados con imagen disponible.</p>';
                } else {
                    renderizarProductos(productosAMostrar);
                }
            } else {
                console.error('Error en la respuesta de productos:', data.mensaje);
                productosContainer.innerHTML = '<p class="text-center text-danger">No se pudieron cargar los productos.</p>';
            }
        })
        .catch(error => {
            console.error('Error al conectar con el backend para productos:', error);
            productosContainer.innerHTML = '<p class="text-center text-danger">Error al cargar los productos. Verifica la conexión.</p>';
        });

    function renderizarProductos(productos) {
        productosContainer.innerHTML = '';

        productos.forEach(producto => {
            let imagenUrl;

            // --- LÓGICA CLAVE DE LA RUTA ---
            if (producto.imagen_principal_ruta && producto.imagen_principal_ruta.startsWith('http')) {
                imagenUrl = producto.imagen_principal_ruta;
            } else {
                imagenUrl = `http://${host}/emarket_bolivia/backend/public/${producto.imagen_principal_ruta}`;
            }
            // --- FIN LÓGICA CLAVE ---

            const formatPrice = (price) => {
                const num = parseFloat(price);
                if (isNaN(num)) return `Bs. ${price}`;
                const formatted = num % 1 === 0 ? Math.floor(num).toLocaleString('es-BO') : num.toFixed(2).toLocaleString('es-BO');
                return `Bs. ${formatted}`;
            };

            const precioMostrar = producto.precio_promocional
                ? `<span class="line-through text-gray-500 ">${formatPrice(producto.precio)}</span>
               <strong class="ml-2 text-red-600 font-bold">${formatPrice(producto.precio_promocional)}</strong>`
                : `<span class="mb-4 text-xl font-bold text-azul">${formatPrice(producto.precio)}</span>`;

            const col = document.createElement('div');
            col.className = 'col-12 col-md-6 col-lg-3 mb-4'; // Mantenemos grid responsive

            col.innerHTML = `
            <div class="overflow-hidden rounded-lg bg-background-light dark:bg-primary/5 shadow-md hover:shadow-md hover:scale-105 transition duration-300">
            
                <!-- Imagen con fondo suave -->
                <div class="h-48 bg-gray-100 flex items-center justify-center">
                    <a href="#" onclick="verProducto(${producto.id_producto}); return false;">
                        <img src="${imagenUrl}" alt="${producto.nombre}" class="h-60 w-full object-cover" onerror="this. src='https://via.placeholder.com/300x200?text=Sin+imagen';">
                    </a>
                </div>

                <!-- Contenido -->
                <div class="p-4">
                    <input type="hidden" class="product-id" value="${producto.id_producto}">
                    
                    <!-- Título en azul oscuro -->
                    <h5 class="pt-2 mb-2 text-lg text-base font-semibold text-azul text-xl">
                        <a href="#" onclick="verProducto(${producto.id_producto}); return false;" class="hover:text-blue-700 hover:text-blue-700 transition-colors">
                            ${producto.nombre}
                        </a>
                    </h5>

                    <!-- Precio -->
                    <p class="text-sm mb-3">${precioMostrar}</p>

                    <!-- Botón Añadir al carrito -->
                    <button 
                        onclick="agregarAlCarrito(${producto.id_producto})"
                        class="w-full bg-blue-900 hover:bg-blue-800 text-white py-2 px-4 rounded-lg font-medium transition"
                    >
                        Añadir al carrito
                    </button>
                </div>
            </div>
        `;
            productosContainer.appendChild(col);
        });
    }
});

