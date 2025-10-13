document.addEventListener('DOMContentLoaded', function () {
    const contenedorPrincipal = document.getElementById('producto-detalle-principal');
    // Determinar la URL base de la API de manera dinámica
    const host = window.location.hostname;
    const apiUrlBase = `http://${host}/emarket_bolivia/backend/public/`;
    const apiUrl = `http://${host}/emarket_bolivia/backend/public`; // Base para los endpoints de API

    // 1. Obtener los datos del producto de sessionStorage
    const productoData = sessionStorage.getItem('productoDetalle');
    if (!productoData) {
        // Mostrar un mensaje de error si no hay datos
        contenedorPrincipal.innerHTML = `
            <div class="lg:col-span-2 p-6 bg-secondary/50 rounded-lg text-center">
                <p class="text-lg font-semibold text-danger">⚠️ No se encontraron detalles del producto.</p>
                <a href="index.html" class="text-primary hover:underline mt-2 inline-block">Volver al inicio</a>
            </div>
        `;
        return;
    }

    const producto = JSON.parse(productoData);
    const {
        nombre,
        descripcion,
        marca,
        precio,
        stock,
        estado_producto,
        razon_social,
        rutas_imagenes,
        precio_promocional,
        evento_asociado,
        id_vendedor,
        promedio_calificacion, // String: "0.00" o "4.50"
        total_opiniones,       // Integer: 0 o 125
        color,
        modelo,
        peso,
        dimensiones,
        sku,
        codigo
    } = producto;

    // --- Funciones de Utilidad ---

    /**
     * Formatea el precio: Bs X,XXX.XX. Si termina en .00, omite los decimales.
     * @param {string} rawPrecio - Precio como string.
     * @returns {string} Precio formateado.
     */
    const formatPrice = (rawPrecio) => {
        const num = parseFloat(rawPrecio);
        if (isNaN(num)) return `Bs ${rawPrecio}`;

        const isInteger = num % 1 === 0;
        const formatted = isInteger ? Math.floor(num).toLocaleString('es-BO') : num.toFixed(2).toLocaleString('es-BO');

        return `Bs ${formatted}`;
    };

    /**
     * Calcula el porcentaje de descuento.
     * @returns {number | null} Porcentaje redondeado o null si no aplica.
     */
    const calculateDiscount = () => {
        if (!precio_promocional || !precio) return null;
        const original = parseFloat(precio);
        const promo = parseFloat(precio_promocional);

        if (original <= 0 || promo >= original) return null;

        const discount = ((original - promo) / original) * 100;
        // Redondear a entero para el badge si es .00, sino un decimal (opcional: mejor solo entero como pediste)
        return Math.floor(discount);
    };

    /**
     * Formatea una fecha a DD/MM/AAAA.
     * @param {string} fechaString - Fecha en formato YYYY-MM-DD.
     * @returns {string} Fecha formateada.
     */
    const formatDate = (fechaString) => {
        if (!fechaString) return 'Fecha no definida';
        try {
            return new Date(fechaString).toLocaleDateString('es-BO', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
        } catch (e) {
            return fechaString; // Retorna el string original si hay error
        }
    };

    // --- Preparación de Variables ---
    // Función para renderizar las estrellas basada en la calificación
    const renderStars = (calificacion) => {
        const fullStars = parseInt(calificacion);
        const emptyStars = 5 - fullStars;
        let starsHtml = '';

        // Estrellas llenas (color amarillo)
        for (let i = 0; i < fullStars; i++) {
            // Aplica el color con !important directamente al span
            starsHtml += `<span class="material-symbols-outlined text-amarillo">star</span>`;
        }
        // Estrellas vacías/gris
        for (let i = 0; i < emptyStars; i++) {
            starsHtml += `<span class="material-symbols-outlined text-content-subtle opacity-50">star</span>`;
        }

        // Quita text-star-yellow del div contenedor y usa text-gray-800 para que el modo oscuro no lo afecte
        return `<div class="flex items-center text-gray-800 dark:text-gray-200">${starsHtml}</div>`;
    };
    /**
     * Crea la URL completa. Si la ruta ya incluye 'http', la devuelve tal cual.
     * De lo contrario, le antepone la URL base del API.
     */
    const generarUrlImagen = (ruta) => {
        // Verificar si la ruta ya es una URL absoluta (útil para ejemplos como picsum)
        if (ruta.startsWith('http://') || ruta.startsWith('https://')) {
            return ruta;
        }
        // Si es una ruta relativa del backend, la combina con la URL base
        return apiUrlBase + ruta;
    };

    // Generar el array de URLs completas para todas las imágenes
    const imagenes = rutas_imagenes && rutas_imagenes.length > 0 ?
        rutas_imagenes.map(generarUrlImagen) :
        ['https://via.placeholder.com/600x600?text=Sin+imagen']; // Imagen de reemplazo

    const hasPromotion = precio_promocional && parseFloat(precio_promocional) < parseFloat(precio);
    const discountPercent = hasPromotion ? calculateDiscount() : null;

    // 1. Convertir a número
    const ratingAverage = parseFloat(promedio_calificacion) || 0;
    const ratingTotal = parseInt(total_opiniones) || 0;

    // Redondear el promedio para la visualización en estrellas (ej: 4.5 -> 5, 4.4 -> 4)
    const displayStars = Math.round(ratingAverage);

    // --- Lógica de renderizado de estrellas para inyección en el encabezado ---
    const fullStars = displayStars;
    const emptyStars = 5 - fullStars;

    let starsHtmlHeader = '';

    // Estrellas llenas
    // Se envuelve todo en el div contenedor para que los colores funcionen
    starsHtmlHeader += `<div class="flex items-center">`;
    starsHtmlHeader += `${'<span class="material-symbols-outlined text-xl text-amarillo">star</span>'.repeat(fullStars)}`;
    // Estrellas vacías
    starsHtmlHeader += `${'<span class="material-symbols-outlined text-xl text-black/30 dark:text-gray-600">star</span>'.repeat(emptyStars)}`;
    starsHtmlHeader += `</div>`;

    const reviewsCountText = `(${ratingTotal} opiniones)`;

    // --- Generación de HTML ---

    let miniaturasHTML = '';
    imagenes.forEach((imgUrl, index) => {
        // Clases para resaltar la primera imagen por defecto
        const defaultClasses = index === 0 ? 'border-primary ring-2 ring-primary/50' : 'border-secondary hover:border-primary transition-colors';

        miniaturasHTML += `
            <div class="min-w-[4.5rem] md:min-w-[6rem]">
                <div class="imagen-miniatura cursor-pointer aspect-square rounded border-1 ${defaultClasses}"
                    style='background-image: url("${imgUrl}"); background-size: cover; background-position: center;'
                    data-url="${imgUrl}" data-index="${index}">
                </div>
            </div>
        `;
    });

    // Lógica para la sección de precios
    let precioHtml;
    let promoBadgeHtml = '';

    if (hasPromotion) {
        // Precio promocional y precio tachado
        precioHtml = `
            <span class="text-4xl font-bold text-danger">${formatPrice(precio_promocional)}</span>
            <span class="text-xl font-medium text-content-subtle line-through">${formatPrice(precio)}</span>
        `;
        // Badge de descuento
        promoBadgeHtml = `<div class="absolute top-4 left-4 bg-danger text-white text-xs font-bold uppercase px-3 py-1 rounded-full z-10">-${discountPercent}%</div>`;
    } else {
        // Solo precio normal
        precioHtml = `
            <span class="text-4xl font-bold text-primary ">${formatPrice(precio)}</span>
        `;
    }

    // Lógica para la sección de promoción adicional
    let eventoHtml = '';
    if (evento_asociado && hasPromotion) {
        const {
            nombre_evento,
            condiciones,
            fecha_inicio,
            fecha_vencimiento
        } = evento_asociado;

        eventoHtml = `
            <div class="p-3 bg-danger/10 text-danger rounded-lg border border-danger/20">
                <h3 class="text-base font-bold mb-1 flex items-center gap-2">
                    <span class="material-symbols-outlined text-xl">local_offer</span> Promoción: ${nombre_evento}
                </h3>
                <p class="text-sm"><strong>Condiciones:</strong> ${condiciones}</p>
                <p class="text-sm"><strong>Vigencia:</strong> Del ${formatDate(fecha_inicio)} al ${formatDate(fecha_vencimiento)}</p>
            </div>
        `;
    }

    // Construcción del HTML final
    contenedorPrincipal.innerHTML = `
        <div class="flex flex-col gap-4">
            <div class="relative w-full aspect-[4/3] rounded-lg overflow-hidden shadow-lg">
                ${promoBadgeHtml}
                <div id="imagen-principal" class="w-full h-full bg-center bg-no-repeat bg-cover"
                    style='background-image: url("${imagenes[0]}");'>
                </div>
            </div>
            <div id="contenedor-miniaturas" class="flex flex-row gap-4 overflow-x-auto whitespace-nowrap pb-2">
                ${miniaturasHTML}
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <div>
                <span class="text-sm font-semibold text-content-subtle">${marca}</span>
                <h1 class="text-3xl md:text-4xl font-bold text-azul">${nombre}</h1>
            </div>

            <div class="flex items-center gap-2">
            <div id="product-rating-stars" class="flex items-center text-yellow-500">
                ${starsHtmlHeader} </div>
                <a id="product-review-count" 
                    class="text-sm font-medium text-content-subtle text-black hover:text-primary"
                    href="#reviews">
                    ${reviewsCountText} </a>
            </div>

            <div class="flex items-baseline gap-3">
                ${precioHtml}
            </div>

            ${eventoHtml}

            <div class="flex items-center gap-2 ${stock > 0 ? 'text-success' : 'text-danger'}">
                <span class="material-symbols-outlined">${stock > 0 ? 'check_circle' : 'cancel'}</span>
                <span class="text-sm font-semibold">${stock > 0 ? `En Stock (${stock} unidades)` : 'Agotado'}</span>
            </div>
            
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-semibold mb-2 text-black">
                        Vendido por:
                        <a href="productos-vendedor.php?vendedor_id=${id_vendedor}" class="text-primary font-bold no-underline hover:underline ">
                            ${razon_social}
                        </a>
                    </h3>
                </div>
                <div>
                    <h3 class="text-sm font-semibold mb-2 text-black">Estado: <span class="text-content-subtle font-normal">${estado_producto}</span></h3>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-4">
                <div class="flex items-center border border-secondary rounded">
                    <button id="btn-decrease" class="px-3 py-2 text-content-subtle hover:bg-secondary transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-black border-2 border-gray-200 " ${stock < 1 ? 'disabled' : ''}>-</button>
                    <input id="product-quantity" class="w-12 text-center border-x border-y-0 border-secondary focus:ring-0 focus:border-x-primary disabled:bg-secondary text-black"
                        type="text" value="1" min="1" max="${stock}" ${stock < 1 ? 'disabled' : ''}/>
                    <button id="btn-increase" class="px-3 py-2 text-content-subtle hover:bg-secondary transition-colors disabled:opacity-50 disabled:cursor-not-allowed border-2 border-gray-200 " ${stock < 1 ? 'disabled' : ''}>+</button>
                </div>
                <button
                    class="w-full flex-1 text-white font-bold py-3 px-6 rounded-lg hover:bg-primary/90 transition-transform transform hover:scale-105 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed fondo-azul" ${stock < 1 ? 'disabled' : ''}" >
                    <span class="material-symbols-outlined">add_shopping_cart</span>
                    <span>Añadir al Carrito</span>
                </button>
            </div>
            <button
                class="w-full bg-primary/20 font-bold py-3 px-6 rounded-lg hover:bg-primary/30 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-azul" ${stock < 1 ? 'disabled' : ''} ">
                Comprar Ahora
            </button>
        </div>
    `;

    // --- Lógica Funcional ---

    // 2. Funcionalidad de Miniaturas
    const imagenPrincipalDiv = document.getElementById('imagen-principal');
    const miniaturas = document.querySelectorAll('.imagen-miniatura');

    miniaturas.forEach(thumb => {
        thumb.addEventListener('click', function () {
            const newUrl = this.getAttribute('data-url');

            // 1. Cambiar la imagen principal
            imagenPrincipalDiv.style.backgroundImage = `url("${newUrl}")`;

            // 2. Cambiar el resaltado de la miniatura
            miniaturas.forEach(t => {
                t.classList.remove('border-primary', 'ring-2', 'ring-primary/50');
                t.classList.add('border-secondary');
            });

            this.classList.remove('border-secondary');
            this.classList.add('border-primary', 'ring-2', 'ring-primary/50');
        });
    });

    // 3. Funcionalidad del Contador de Cantidad
    const btnDecrease = document.getElementById('btn-decrease');
    const btnIncrease = document.getElementById('btn-increase');
    const quantityInput = document.getElementById('product-quantity');
    const maxStock = parseInt(stock);

    if (maxStock > 0) {
        // Inicializar botones (deshabilitar si es 1)
        if (parseInt(quantityInput.value) <= 1) {
            btnDecrease.disabled = true;
        }

        btnDecrease.addEventListener('click', () => {
            let current = parseInt(quantityInput.value);
            if (current > 1) {
                current--;
                quantityInput.value = current;
                btnIncrease.disabled = false;
            }
            if (current <= 1) {
                btnDecrease.disabled = true;
            }
        });

        btnIncrease.addEventListener('click', () => {
            let current = parseInt(quantityInput.value);
            if (current < maxStock) {
                current++;
                quantityInput.value = current;
                btnDecrease.disabled = false;
            }
            if (current >= maxStock) {
                btnIncrease.disabled = true;
            }
        });

        quantityInput.addEventListener('change', () => {
            let value = parseInt(quantityInput.value);

            if (isNaN(value) || value < 1) {
                value = 1;
            } else if (value > maxStock) {
                value = maxStock;
            }

            quantityInput.value = value;
            btnDecrease.disabled = value <= 1;
            btnIncrease.disabled = value >= maxStock;
        });
    }
    function parseJwt(token) {
        try {
            const base64Url = token.split('.')[1]; // Parte del payload
            const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
            const jsonPayload = decodeURIComponent(
                atob(base64)
                    .split('')
                    .map(c => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2))
                    .join('')
            );
            return JSON.parse(jsonPayload);
        } catch (error) {
            console.error('Error al decodificar el token JWT:', error);
            return null;
        }
    }
    function obtenerIdUsuarioDelToken() {
        const token = localStorage.getItem('token');
        if (!token) return null;

        const payload = parseJwt(token);
        return payload?.sub ?? null;
    }

    // ==========================================================
    // 4. Lógica de Pestañas (Tabs)
    // ==========================================================

    const tabsNav = document.querySelectorAll('nav[aria-label="Tabs"] a');
    const tabsContentContainer = document.getElementById('tabs-content');

    // Función para renderizar una única reseña
    const renderReviewCard = (review) => {
        // 1. Usar el nuevo flag 'verificada' (1 o 0)
        const esVerificada = review.verificada === 1;

        // NOTA: El avatar se genera con un placeholder temporal ya que el JSON no lo incluye
        const avatarUrl = `https://i.pravatar.cc/48?u=${review.id_usuario}`;
        const token = localStorage.getItem('token');

        if (token) {
            const payload = parseJwt(token);
            if (payload && typeof payload.sub === 'number') {
                const idUsuarioActual = payload.sub; // ¡Este es tu id_usuario!
                console.log('ID del usuario autenticado:', idUsuarioActual);
            } else {
                console.warn('El token no contiene un "sub" válido.');
            }
        } else {
            console.warn('No hay token en localStorage.');
        }

        const idUsuarioActual = obtenerIdUsuarioDelToken();
        const esMiReseña = review.id_usuario === idUsuarioActual;
        console.log(review.id_producto)
        let botonesGestion = '';
        if (esMiReseña) {
            botonesGestion = `
            <div
                class="absolute top-4 right-4 flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <button class="js-edit-review text-gray-500 dark:text-gray-400 hover:text-primary data-review-id="${review.id_review}">
                    <span class="material-symbols-outlined">edit</span>
                </button>
                <button class="js-delete-review text-gray-500 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400" data-review-id="${review.id_review}">
                    <span class="material-symbols-outlined">delete</span>
                </button>
            </div>
            `;
        }
        return `
        <div class="group relative bg-background-light p-6 rounded-lg shadow-md border border-surface-light ">
            <div class="flex items-start gap-4">
                <img alt="Avatar de ${review.nombre_usuario}" class="h-12 w-12 rounded-full object-cover" src="${avatarUrl}" />
                <div class="flex-1 ">
                    <div>
                        <h3 class="text-lg font-bold text-primary max-w-[80%]">
                            ${review.titulo || 'Opinión del Cliente'} 
                        </h3>
                        <p class="text-sm text-text-muted-light text-black/70 mb-2">${review.nombre_usuario} - ${formatDate(review.fecha_creacion)}</p>
                    </div>
                    ${renderStars(review.calificacion)}
                    ${esVerificada ? `
                    <div class="flex items-center gap-2 mt-2">
                                <span class="material-symbols-outlined text-sm text-verde">verified</span>
                                <span class="text-xs font-semibold text-verde">Compra Verificada</span>
                            </div>
                    ` : ''}
                    ${botonesGestion}
                    <p class="mt-3 text-text-light leading-relaxed text-black" >${review.comentario}</p>
                </div>
            </div>
        </div>
    `;
    };
    // Función para buscar y renderizar las reseñas
    const fetchAndRenderReviews = async (productId) => {
        const loadingHtml = '<p class="text-center p-6 text-content-subtle">Cargando opiniones...</p>';
        tabsContentContainer.innerHTML = loadingHtml;

        try {
            const response = await fetch(`${apiUrl}/reviews/producto/${productId}`);
            if (!response.ok) throw new Error('Error al cargar las reseñas');

            const result = await response.json();
            const reviews = result.status === 'success' ? result.data : [];


            if (reviews.length > 0) {
                // 1. Controles de Filtrado/Escritura
                const controlsHtml = `
                    <div class="pb-6 border-b border-surface-light  mb-8">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <select class="w-full py-2 px-3 bg-background-light  border border-surface-dark  rounded-lg focus:ring-primary focus:border-primary">
                                    <option>Más reciente</option>
                                    <option>Más útil</option>
                                    <option>Mejor Calificación</option>
                                    <option>Peor Calificación</option>
                                </select>
                            </div>
                            <div>
                                <select class="w-full py-2 px-3 bg-background-light  border border-surface-dark  rounded-lg focus:ring-primary focus:border-primary">
                                    <option>Filtrar por calificación</option>
                                    <option>Ver solo 5 estrellas</option>
                                    <option>Ver solo 4 estrellas</option>
                                    <option>Ver solo 3 estrellas</option>
                                    <option>Ver solo 2 estrellas</option>
                                    <option>Ver solo 1 estrella</option>
                                </select>
                            </div>
                <button
                    class="w-full bg-primary text-white font-bold py-3 px-4 rounded-lg hover:bg-primary/90 transition-colors duration-300 flex items-center justify-center gap-2 js-open-review-modal">
                    <span class="material-symbols-outlined">edit</span>
                    Escribir una Opinión
                </button>
            </div>
        </div>
    `;
                // 2. Reseñas Dinámicas
                const reviewsHtml = result.data.map(renderReviewCard).join('');

                tabsContentContainer.innerHTML = controlsHtml + `<div class="space-y-8">${reviewsHtml}</div>`;
            } else {
                tabsContentContainer.innerHTML = `
                    <div class="p-6 bg-secondary  rounded-lg text-center">
                        <p class="text-content  text-black">Aún no hay opiniones de clientes. ¡Sé el primero en dejar una reseña!</p>
                        <button
    class="w-full bg-primary text-white font-bold py-3 px-4 rounded-lg hover:bg-primary/90 transition-colors duration-300 flex items-center justify-center gap-2 js-open-review-modal">
    Escribir una Opinión
</button>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error al obtener las reseñas:', error);
            tabsContentContainer.innerHTML = `
                <div class="p-6 bg-danger/10 text-danger rounded-lg text-center">
                    <p>No se pudieron cargar las opiniones. Intenta de nuevo.</p>
                </div>
            `;
        }
    };

    // Función para generar el contenido de cada pestaña
    const generateTabContent = (tabName, product) => {
        switch (tabName) {
            case 'descripcion':
                // Usar la descripción del JSON, envuelta en las clases 'prose'
                // para mantener el estilo de fuente y márgenes del diseño base.
                const descriptionText = product.descripcion || 'No se ha proporcionado una descripción detallada para este producto.';
                return `
                <div class="prose prose-lg  max-w-none text-black">
                    <p>${descriptionText}</p>
                </div>
            `;
            case 'especificaciones':
                // Crea un HTML limpio para los atributos técnicos
                let specsHtml = '';

                // Lista de atributos que quieres mostrar
                const technicalAttributes = [
                    { label: 'Modelo', value: product.modelo },
                    { label: 'Color', value: product.color },
                    { label: 'Peso', value: product.peso },
                    { label: 'Dimensiones', value: product.dimensiones },
                    { label: 'SKU (Identificador)', value: product.sku },
                    { label: 'Código de Producto', value: product.codigo }
                ];

                // Generar la lista de especificaciones
                const listItems = technicalAttributes
                    .filter(attr => attr.value) // Oculta si el valor es nulo/vacío
                    .map(attr => `<li><strong>${attr.label}:</strong> ${attr.value}</li>`)
                    .join('');

                // Si hay atributos, los muestra junto a los detalles clave existentes
                if (listItems.length > 0) {
                    specsHtml = `
            <div class="prose max-w-none text-black">
                <h3 class="font-bold title-tab">Detalles Clave</h3>
                <ul>
                    <li><strong>Marca:</strong> ${product.marca}</li>
                    <li><strong>Estado:</strong> ${product.estado_producto}</li>
                    <li><strong>Stock:</strong> ${product.stock} unidades</li>
                    <li><strong>Vendido por:</strong> ${product.razon_social}</li>
                </ul>
            </div>
            
            <div class="prose mt-8 max-w-none text-black">
                <h3 class="font-bold title-tab">Especificaciones Técnicas</h3>
                <ul>
                    ${listItems}
                </ul>
            </div>
        `;
                } else {
                    // Opción si no hay especificaciones extra
                    specsHtml = `<p class="p-4 bg-secondary/50 rounded-lg text-black">No se han proporcionado especificaciones técnicas detalladas para este producto.</p>`;
                }

                return specsHtml;
            case 'opiniones':
                // Establece el HTML de carga y dispara la carga de la API
                fetchAndRenderReviews(product.id_producto);
                // Retorna un div vacío para que fetchAndRenderReviews lo llene asíncronamente
                return '';
            default:
                return '';
        }
    };

    // Función para cambiar la pestaña activa
    const setActiveTab = (tabName) => {
        // 1. Manejar clases de la navegación
        tabsNav.forEach(tab => {
            const currentTabName = tab.getAttribute('data-tab');
            if (currentTabName === tabName) {
                // Activar la pestaña
                tab.classList.remove('border-transparent', 'text-content-subtle', 'hover:text-primary', 'hover:border-primary/50');
                tab.classList.add('border-azul', 'font-bold', 'text-azul');
            } else {
                // Desactivar otras pestañas
                tab.classList.remove('border-azul', 'font-bold', 'text-azul');
                tab.classList.add('border-transparent', 'text-content-subtle', 'hover:text-primary', 'hover:border-gray-400');
            }
        });

        // La pestaña 'opiniones' maneja su propia inyección de contenido asíncrona.
        const content = generateTabContent(tabName, producto);
        if (tabName !== 'opiniones') {
            tabsContentContainer.innerHTML = content;
        }
    };

    // Asignar eventos a las pestañas
    tabsNav.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            const tabName = tab.getAttribute('data-tab');
            setActiveTab(tabName);
        });
    });

    // ==========================================================
    // 5. Lógica del Modal y Envío de Reseñas
    // ==========================================================
    // -----------------------------------------------------------------------
    // Elementos del DOM del Modal
    // -----------------------------------------------------------------------
    const reviewModal = document.getElementById('reviewModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const reviewForm = reviewModal.querySelector('form');
    const apiUrlCrear = `${apiUrl}/reviews/crear`;
    let modoEdicion = false;
    let idResenaEdicion = null;
    let reviewIdToDelete = null;
    const confirmDeleteModal = document.getElementById('confirmDeleteModal');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');


    // -----------------------------------------------------------------------
    // Funciones del Modal
    // -----------------------------------------------------------------------
    const showModal = (datos = null) => {
        reviewModal.classList.remove('hidden');
        reviewModal.classList.add('flex');

        if (datos) {
            // Modo edición
            modoEdicion = true;
            idResenaEdicion = datos.id;

            // Precargar formulario
            document.getElementById('review-title').value = datos.titulo || '';
            document.getElementById('review-body').value = datos.comentario || '';

            // Seleccionar la calificación en las estrellas
            const ratingInputs = document.querySelectorAll('input[name="rating"]');
            ratingInputs.forEach(input => {
                if (parseInt(input.value) === datos.calificacion) {
                    input.checked = true;
                }
            });
            const titleModal = document.getElementById('tituloModal');
            titleModal.innerHTML = 'Actualiza tu Opinión';
            // Cambiar texto del botón
            const submitBtn = reviewForm.querySelector('button[type="submit"]');
            submitBtn.innerHTML = `
            <span class="material-symbols-outlined">edit</span>
            Actualizar Reseña
        `;
        } else {
            // Modo creación
            modoEdicion = false;
            idResenaEdicion = null;
            reviewForm.reset();
            const titleModal = document.getElementById('tituloModal');
            titleModal.innerHTML = 'Escribe tu Opinión';
            const submitBtn = reviewForm.querySelector('button[type="submit"]');
            submitBtn.innerHTML = `
            <span class="material-symbols-outlined">send</span>
            Publicar Reseña
        `;
        }
    };
    const hideModal = () => {
        reviewModal.classList.add('hidden');
        reviewModal.classList.remove('flex');
        reviewForm.reset(); // Limpiar el formulario al cerrar
        // Opcional: limpiar mensajes de error
    };


    // ==========================================================
    // Función de Actualización de Calificación Principal
    // ==========================================================

    /**
     * Obtiene el promedio de calificación y el total de opiniones del producto
     * y actualiza el HTML principal (estrellas y contador).
     * @param {number} productId - El ID del producto a actualizar.
     */
    const updateProductRating = async (productId) => {
        const ratingApiUrl = `${apiUrl}/productos/calificacion/${productId}`;

        try {
            const response = await fetch(ratingApiUrl);
            if (!response.ok) throw new Error('Error al cargar la calificación actualizada');

            const result = await response.json();

            // Verifica si los datos son válidos y contienen el array
            const data = result.status === 'success' && result.data.length > 0 ? result.data[0] : null;

            if (data) {
                // 1. Convertir a número (usa los datos frescos de la API)
                const ratingAverage = parseFloat(data.promedio_calificacion) || 0;
                const ratingTotal = parseInt(data.total_opiniones) || 0;

                // 2. Lógica de renderizado de estrellas (igual que en tu encabezado)
                const displayStars = Math.round(ratingAverage);
                const fullStars = displayStars;
                const emptyStars = 5 - fullStars;

                let starsHtml = '';

                // Contenedor de estrellas
                starsHtml += `${'<span class="material-symbols-outlined text-xl text-amarillo">star</span>'.repeat(fullStars)}`;
                starsHtml += `${'<span class="material-symbols-outlined text-xl text-black/30 dark:text-gray-600">star</span>'.repeat(emptyStars)}`;

                const reviewsCountText = `(${ratingTotal} opiniones)`;

                // 3. Actualizar elementos en el DOM
                const ratingStarsContainer = document.querySelector('#product-rating-stars');
                const reviewCountElement = document.querySelector('#product-review-count');

                if (ratingStarsContainer) {
                    // Actualiza solo el contenido HTML de las estrellas
                    ratingStarsContainer.innerHTML = starsHtml;
                }

                if (reviewCountElement) {
                    // Actualiza solo el contador de opiniones
                    reviewCountElement.textContent = reviewsCountText;
                }

                console.log(`Calificación del producto #${productId} actualizada: ${ratingAverage} estrellas (${ratingTotal} opiniones)`);

            }
        } catch (error) {
            console.error('No se pudo actualizar la calificación principal:', error);
            // Opcional: mostrar un mensaje de error al usuario
        }
    };


    const cargarYMostrarResenaParaEdicion = async () => {
        const token = localStorage.getItem('token');
        if (!token) {
            alert('Debes iniciar sesión para editar tu reseña.');
            return;
        }
        console.log("Id_producto: ", producto.id_producto)
        try {
            const response = await fetch(`${apiUrl}/reviews/mi-resena/${producto.id_producto}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });

            if (!response.ok) throw new Error('No se pudo cargar la reseña');

            const result = await response.json();
            if (result.status !== 'success') throw new Error(result.mensaje || 'Error desconocido');

            const review = result.data;
            showModal(review); // Abre el modal en modo edición con los datos
        } catch (error) {
            console.error('Error al cargar reseña para edición:', error);
            alert('No se pudo cargar la reseña. ¿Sigues siendo el autor?');
        }
    };


    async function eliminarReview(reviewId) {
        console.log("funcion eliminarReview");
        const token = localStorage.getItem('token');
        if (!token) {
            alert('Debes iniciar sesión.');
            return;
        }

        try {
            const response = await fetch(`${apiUrl}/reviews/${reviewId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });

            const result = await response.json();

            if (response.ok && result.status === 'success') {
                alert('Reseña eliminada con éxito.');
                setActiveTab('opiniones'); // Recargar la lista
                updateProductRating(producto.id_producto);
            } else {
                alert(`Error al eliminar: ${result.mensaje || 'No se pudo completar la acción'}`);
            }
        } catch (error) {
            console.error('Error al eliminar reseña:', error);
            alert('Error de conexión. Inténtalo más tarde.');
        }
    }
    // -----------------------------------------------------------------------
    // Event Listeners
    // -----------------------------------------------------------------------

    // Cerrar modal
    closeModalBtn.addEventListener('click', hideModal);
    cancelBtn.addEventListener('click', hideModal);
    reviewModal.addEventListener('click', (e) => {
        if (e.target.id === 'reviewModal') {
            hideModal();
        }
    });

    // === Listener para CANCELAR ===
    cancelDeleteBtn.addEventListener('click', () => {
        confirmDeleteModal.classList.add('hidden');
        reviewIdToDelete = null;
    });

    // === Listener para CONFIRMAR ===
    confirmDeleteBtn.addEventListener('click', async () => {
        console.log("idReview", reviewIdToDelete)
        if (reviewIdToDelete) {
            await eliminarReview(reviewIdToDelete);
            confirmDeleteModal.classList.add('hidden');
            reviewIdToDelete = null;
            // Opcional: recargar opiniones
            setActiveTab('opiniones');
            updateProductRating(producto.id_producto);
        }
    });
    // Ya que el botón "Escribir una Opinión" se crea DENTRO de fetchAndRenderReviews
    // se usa la delegación de eventos en el contenedor de las pestañas.
    tabsContentContainer.addEventListener('click', (e) => {
        if (e.target.closest('.js-open-review-modal')) {
            showModal();
        }
        // Manejar el botón de la sección "Aún no hay opiniones" (si aplica)
        if (e.target.classList.contains('bg-primary') && e.target.textContent.includes('Escribir una Opinión') && !e.target.closest('.js-open-review-modal')) {
            showModal();
        }
        if (e.target.closest('.js-edit-review')) {
            const reviewId = e.target.closest('.js-edit-review').dataset.reviewId;
            cargarYMostrarResenaParaEdicion(reviewId);
        }
        // Abrir modal de confirmación
        if (e.target.closest('.js-delete-review')) {
            reviewIdToDelete = e.target.closest('.js-delete-review').dataset.reviewId;
            confirmDeleteModal.classList.remove('hidden');
            console.log("review_id: ", reviewIdToDelete);
        }
    });

    // -----------------------------------------------------------------------
    // Envío del Formulario
    // -----------------------------------------------------------------------
    reviewForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(reviewForm);
        const calificacion = formData.get('rating');
        const titulo = formData.get('review-title');
        const comentario = formData.get('review-body');
        const token = localStorage.getItem('token');

        if (!token) {
            alert('Debes iniciar sesión.');
            return;
        }

        if (!calificacion || !titulo || !comentario) {
            alert('Por favor, completa todos los campos.');
            return;
        }

        const reviewData = {
            titulo: titulo,
            calificacion: parseInt(calificacion),
            comentario: comentario
        };

        try {
            let response;
            if (modoEdicion && idResenaEdicion) {
                // Modo edición → PUT
                response = await fetch(`${apiUrl}/reviews/${idResenaEdicion}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify(reviewData)
                });
            } else {
                // Modo creación → POST
                reviewData.id_producto = producto.id_producto;
                response = await fetch(`${apiUrl}/reviews/crear`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify(reviewData)
                });
            }

            const result = await response.json();

            if (response.ok && result.status === 'success') {
                const mensaje = modoEdicion ? '¡Reseña actualizada con éxito!' : '¡Reseña publicada con éxito!';
                alert(mensaje);
                hideModal();
                setActiveTab('opiniones'); // Recargar opiniones
                updateProductRating(producto.id_producto);
            } else {
                alert(`Error: ${result.mensaje || 'Operación fallida'}`);
            }
        } catch (error) {
            console.error('Error al enviar reseña:', error);
            alert('Error de conexión. Inténtalo más tarde.');
        }
    });

    // Inicializar: Mostrar la pestaña "Descripción" al cargar
    setActiveTab('descripcion');
});