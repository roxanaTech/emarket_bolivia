/**
 * detalle-producto.js
 * 
 * Módulo principal para la página de detalle de producto.
 * Responsabilidades:
 * - Renderizar el producto desde sessionStorage
 * - Gestionar miniaturas, stock, cantidad
 * - Manejar pestañas (descripción, especificaciones, opiniones)
 * - CRUD de reseñas (crear, editar, eliminar)
 * - Modales (reseña, confirmación de eliminación)
 * - Actualización dinámica de calificación promedio
 * 
 * Requisitos:
 * - Tailwind CSS vía CDN
 * - Material Symbols
 * - JWT en localStorage con `sub` = id_usuario
 */

document.addEventListener('DOMContentLoaded', function () {
    // ==================================================
    // 1. CONFIGURACIÓN INICIAL Y DATOS DEL PRODUCTO
    // ==================================================
    const contenedorPrincipal = document.getElementById('producto-detalle-principal');
    const host = window.location.hostname;
    const API_BASE = `http://${host}/emarket_bolivia/backend/public`;
    const API_URLS = {
        reviews: {
            crear: `${API_BASE}/reviews/crear`,
            producto: (id) => `${API_BASE}/reviews/producto/${id}`,
            miResena: (idProducto) => `${API_BASE}/reviews/mi-resena/${idProducto}`,
            gestion: (idReview) => `${API_BASE}/reviews/${idReview}`
        },
        calificacion: (id) => `${API_BASE}/productos/calificacion/${id}`
    };

    // Obtener producto desde sessionStorage
    const productoData = sessionStorage.getItem('productoDetalle');
    if (!productoData) {
        contenedorPrincipal.innerHTML = `
            <div class="lg:col-span-2 p-6 bg-secondary/50 rounded-lg text-center">
                <p class="text-lg font-semibold text-danger">⚠️ No se encontraron detalles del producto.</p>
                <a href="index.html" class="text-primary hover:underline mt-2 inline-block">Volver al inicio</a>
            </div>
        `;
        return;
    }
    const producto = JSON.parse(productoData);

    // ==================================================
    // 2. UTILIDADES (formateo, JWT, etc.)
    // ==================================================
    const Utils = {
        /**
         * Formatea precio en moneda local (Bs)
         */
        formatPrice: (rawPrecio) => {
            const num = parseFloat(rawPrecio);
            if (isNaN(num)) return `Bs ${rawPrecio}`;
            const isInteger = num % 1 === 0;
            const formatted = isInteger ? Math.floor(num).toLocaleString('es-BO') : num.toFixed(2).toLocaleString('es-BO');
            return `Bs ${formatted}`;
        },

        /**
         * Formatea fecha a DD/MM/AAAA
         */
        formatDate: (fechaString) => {
            if (!fechaString) return 'Fecha no definida';
            try {
                return new Date(fechaString).toLocaleDateString('es-BO', { day: '2-digit', month: '2-digit', year: 'numeric' });
            } catch (e) {
                return fechaString;
            }
        },

        /**
         * Decodifica JWT y extrae payload
         */
        parseJwt: (token) => {
            try {
                const base64Url = token.split('.')[1];
                const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
                const jsonPayload = decodeURIComponent(
                    atob(base64).split('').map(c => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)).join('')
                );
                return JSON.parse(jsonPayload);
            } catch (e) {
                console.error('Token JWT inválido', e);
                return null;
            }
        },

        /**
         * Obtiene id_usuario desde el token JWT (campo 'sub')
         */
        obtenerIdUsuarioDelToken: () => {
            const token = localStorage.getItem('token');
            if (!token) return null;
            const payload = Utils.parseJwt(token);
            return payload?.sub ?? null;
        }
    };

    // ==================================================
    // 3. RENDERIZADO DEL PRODUCTO
    // ==================================================
    class ProductRenderer {
        constructor(producto) {
            this.producto = producto;
            this.render();
            this.bindEvents();
        }

        render() {
            const { producto } = this;
            const {
                nombre, marca, precio, stock, estado_producto, razon_social, id_vendedor,
                rutas_imagenes, precio_promocional, evento_asociado, promedio_calificacion, total_opiniones
            } = producto;

            // URLs de imágenes
            const imagenes = (rutas_imagenes && rutas_imagenes.length > 0)
                ? rutas_imagenes.map(ruta => ruta.startsWith('http') ? ruta : `${API_BASE}/${ruta}`)
                : ['https://via.placeholder.com/600x600?text=Sin+imagen'];

            // Promoción
            const hasPromotion = precio_promocional && parseFloat(precio_promocional) < parseFloat(precio);
            const discountPercent = hasPromotion ? Math.floor(((parseFloat(precio) - parseFloat(precio_promocional)) / parseFloat(precio)) * 100) : null;

            // Calificación
            const ratingAverage = parseFloat(promedio_calificacion) || 0;
            const ratingTotal = parseInt(total_opiniones) || 0;
            const displayStars = Math.round(ratingAverage);
            const starsHtml = `
                <div class="flex items-center">
                    ${'<span class="material-symbols-outlined text-xl text-amarillo">star</span>'.repeat(displayStars)}
                    ${'<span class="material-symbols-outlined text-xl text-black/30 dark:text-gray-600">star</span>'.repeat(5 - displayStars)}
                </div>
            `;
            const reviewsCountText = `(${ratingTotal} opiniones)`;

            // Miniaturas
            let miniaturasHTML = '';
            imagenes.forEach((imgUrl, index) => {
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

            // Precio
            let precioHtml, promoBadgeHtml = '';
            if (hasPromotion) {
                precioHtml = `
                    <span class="text-4xl font-bold text-danger">${Utils.formatPrice(precio_promocional)}</span>
                    <span class="text-xl font-medium text-content-subtle line-through">${Utils.formatPrice(precio)}</span>
                `;
                promoBadgeHtml = `<div class="absolute top-4 left-4 bg-danger text-white text-xs font-bold uppercase px-3 py-1 rounded-full z-10">-${discountPercent}%</div>`;
            } else {
                precioHtml = `<span class="text-4xl font-bold text-primary ">${Utils.formatPrice(precio)}</span>`;
            }

            // Evento promocional
            let eventoHtml = '';
            if (evento_asociado && hasPromotion) {
                const { nombre_evento, condiciones, fecha_inicio, fecha_vencimiento } = evento_asociado;
                eventoHtml = `
                    <div class="p-3 bg-danger/10 text-danger rounded-lg border border-danger/20">
                        <h3 class="text-base font-bold mb-1 flex items-center gap-2">
                            <span class="material-symbols-outlined text-xl">local_offer</span> Promoción: ${nombre_evento}
                        </h3>
                        <p class="text-sm"><strong>Condiciones:</strong> ${condiciones}</p>
                        <p class="text-sm"><strong>Vigencia:</strong> Del ${Utils.formatDate(fecha_inicio)} al ${Utils.formatDate(fecha_vencimiento)}</p>
                    </div>
                `;
            }

            contenedorPrincipal.innerHTML = `
                <!-- Galería -->
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

                <!-- Información -->
                <div class="flex flex-col gap-4">
                    <div>
                        <span class="text-sm font-semibold text-content-subtle">${marca}</span>
                        <h1 class="text-3xl md:text-4xl font-bold text-azul">${nombre}</h1>
                    </div>
                    <div class="flex items-center gap-2">
                        <div id="product-rating-stars" class="flex items-center text-yellow-500">${starsHtml}</div>
                        <a id="product-review-count" class="text-sm font-medium text-content-subtle text-black hover:text-primary" href="#reviews">
                            ${reviewsCountText}
                        </a>
                    </div>
                    <div class="flex items-baseline gap-3">${precioHtml}</div>
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
                            <button id="btn-decrease" class="px-3 py-2 text-content-subtle hover:bg-secondary transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-black border-2 border-gray-200" ${stock < 1 ? 'disabled' : ''}>-</button>
                            <input id="product-quantity" class="w-12 text-center border-x border-y-0 border-secondary focus:ring-0 focus:border-x-primary disabled:bg-secondary text-black"
                                type="text" value="1" min="1" max="${stock}" ${stock < 1 ? 'disabled' : ''}/>
                            <button id="btn-increase" class="px-3 py-2 text-content-subtle hover:bg-secondary transition-colors disabled:opacity-50 disabled:cursor-not-allowed border-2 border-gray-200" ${stock < 1 ? 'disabled' : ''}>+</button>
                        </div>
                        <button class="w-full flex-1 text-white font-bold py-3 px-6 rounded-lg hover:bg-primary/90 transition-transform transform hover:scale-105 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed fondo-azul" ${stock < 1 ? 'disabled' : ''}>
                            <span class="material-symbols-outlined">add_shopping_cart</span>
                            <span>Añadir al Carrito</span>
                        </button>
                    </div>
                    <button class="w-full bg-primary/20 font-bold py-3 px-6 rounded-lg hover:bg-primary/30 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-azul" ${stock < 1 ? 'disabled' : ''}>
                        Comprar Ahora
                    </button>
                </div>
            `;
        }

        bindEvents() {
            // Miniaturas
            const imagenPrincipalDiv = document.getElementById('imagen-principal');
            const miniaturas = document.querySelectorAll('.imagen-miniatura');
            miniaturas.forEach(thumb => {
                thumb.addEventListener('click', function () {
                    const newUrl = this.getAttribute('data-url');
                    imagenPrincipalDiv.style.backgroundImage = `url("${newUrl}")`;
                    miniaturas.forEach(t => {
                        t.classList.remove('border-primary', 'ring-2', 'ring-primary/50');
                        t.classList.add('border-secondary');
                    });
                    this.classList.add('border-primary', 'ring-2', 'ring-primary/50');
                    this.classList.remove('border-secondary');
                });
            });

            // Cantidad
            const maxStock = parseInt(this.producto.stock);
            if (maxStock > 0) {
                const btnDecrease = document.getElementById('btn-decrease');
                const btnIncrease = document.getElementById('btn-increase');
                const quantityInput = document.getElementById('product-quantity');

                const updateButtons = () => {
                    const val = parseInt(quantityInput.value);
                    btnDecrease.disabled = val <= 1;
                    btnIncrease.disabled = val >= maxStock;
                };

                btnDecrease.addEventListener('click', () => {
                    let val = parseInt(quantityInput.value);
                    if (val > 1) quantityInput.value = val - 1;
                    updateButtons();
                });
                btnIncrease.addEventListener('click', () => {
                    let val = parseInt(quantityInput.value);
                    if (val < maxStock) quantityInput.value = val + 1;
                    updateButtons();
                });
                quantityInput.addEventListener('change', () => {
                    let val = parseInt(quantityInput.value) || 1;
                    val = Math.max(1, Math.min(val, maxStock));
                    quantityInput.value = val;
                    updateButtons();
                });
                updateButtons();
            }
        }
    }

    // ==================================================
    // 4. GESTIÓN DE PESTAÑAS
    // ==================================================
    class TabManager {
        constructor(producto) {
            this.producto = producto;
            this.tabsNav = document.querySelectorAll('nav[aria-label="Tabs"] a');
            this.tabsContentContainer = document.getElementById('tabs-content');
            this.bindEvents();
        }

        setActiveTab(tabName) {
            this.tabsNav.forEach(tab => {
                const currentTabName = tab.getAttribute('data-tab');
                if (currentTabName === tabName) {
                    tab.classList.remove('border-transparent', 'text-content-subtle', 'hover:text-primary', 'hover:border-primary/50');
                    tab.classList.add('border-azul', 'font-bold', 'text-azul');
                } else {
                    tab.classList.remove('border-azul', 'font-bold', 'text-azul');
                    tab.classList.add('border-transparent', 'text-content-subtle', 'hover:text-primary', 'hover:border-gray-400');
                }
            });

            if (tabName === 'opiniones') {
                new ReviewsManager(this.producto, this.tabsContentContainer, this.updateProductRating.bind(this));
            } else {
                this.tabsContentContainer.innerHTML = this.generateTabContent(tabName);
            }
        }

        generateTabContent(tabName) {
            const p = this.producto;
            switch (tabName) {
                case 'descripcion':
                    return `<div class="prose prose-lg max-w-none text-black"><p>${p.descripcion || 'No hay descripción.'}</p></div>`;
                case 'especificaciones':
                    const attrs = [
                        { label: 'Modelo', value: p.modelo },
                        { label: 'Color', value: p.color },
                        { label: 'Peso', value: p.peso },
                        { label: 'Dimensiones', value: p.dimensiones },
                        { label: 'SKU', value: p.sku },
                        { label: 'Código', value: p.codigo }
                    ].filter(a => a.value);
                    return `
                        <div class="prose max-w-none text-black">
                            <h3 class="font-bold">Detalles Clave</h3>
                            <ul>
                                <li><strong>Marca:</strong> ${p.marca}</li>
                                <li><strong>Estado:</strong> ${p.estado_producto}</li>
                                <li><strong>Stock:</strong> ${p.stock} unidades</li>
                                <li><strong>Vendido por:</strong> ${p.razon_social}</li>
                            </ul>
                        </div>
                        ${attrs.length ? `
                            <div class="prose mt-8 max-w-none text-black">
                                <h3 class="font-bold">Especificaciones Técnicas</h3>
                                <ul>${attrs.map(a => `<li><strong>${a.label}:</strong> ${a.value}</li>`).join('')}</ul>
                            </div>
                        ` : ''}
                    `;
                default:
                    return '';
            }
        }

        async updateProductRating(productId) {
            try {
                const res = await fetch(API_URLS.calificacion(productId));
                const data = (await res.json()).data?.[0];
                if (!data) return;

                const avg = parseFloat(data.promedio_calificacion) || 0;
                const total = parseInt(data.total_opiniones) || 0;
                const stars = Math.round(avg);
                const starsHtml = `
                    ${'<span class="material-symbols-outlined text-xl text-amarillo">star</span>'.repeat(stars)}
                    ${'<span class="material-symbols-outlined text-xl text-black/30 dark:text-gray-600">star</span>'.repeat(5 - stars)}
                `;
                document.querySelector('#product-rating-stars').innerHTML = starsHtml;
                document.querySelector('#product-review-count').textContent = `(${total} opiniones)`;
            } catch (e) {
                console.error('Error al actualizar calificación:', e);
            }
        }

        bindEvents() {
            this.tabsNav.forEach(tab => {
                tab.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.setActiveTab(tab.getAttribute('data-tab'));
                });
            });
        }
    }

    // ==================================================
    // 5. GESTIÓN DE RESEÑAS (CRUD)
    // ==================================================
    class ReviewsManager {
        constructor(producto, container, updateRatingCallback) {
            this.producto = producto;
            this.container = container;
            this.updateRating = updateRatingCallback;
            this.idUsuarioActual = Utils.obtenerIdUsuarioDelToken();
            this.fetchAndRenderReviews();
            this.bindModalEvents();
            this.allReviews = [];
            this.bindEventListeners();
        }
        bindEventListeners() {
            this.container.addEventListener('change', (e) => {
                if (e.target.id === 'sortSelect' || e.target.id === 'ratingFilter') {
                    const sort = document.getElementById('sortSelect')?.value || 'recent';
                    const rating = document.getElementById('ratingFilter')?.value || 'all';
                    this.applyFilters(sort, rating);
                }
            });
        }
        // --- Renderizado ---
        renderReviewCard(review) {
            const esMiReseña = review.id_usuario === this.idUsuarioActual;
            const avatarUrl = `https://i.pravatar.cc/48?u=${review.id_usuario}`;
            const esVerificada = review.verificada === 1;

            let botonesGestion = '';
            if (esMiReseña) {
                botonesGestion = `
                    <div class="absolute top-4 right-4 flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button class="js-edit-review text-gray-500 hover:text-primary" data-review-id="${review.id_review}">
                            <span class="material-symbols-outlined">edit</span>
                        </button>
                        <button class="js-delete-review text-gray-500 hover:text-red-500" data-review-id="${review.id_review}">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                    </div>
                `;
            }

            return `
                <div class="group relative bg-background-light p-6 rounded-lg shadow-md border border-surface-light">
                    <div class="flex items-start gap-4">
                        <img alt="Avatar" class="h-12 w-12 rounded-full object-cover" src="${avatarUrl}" />
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-primary">${review.titulo || 'Opinión del Cliente'}</h3>
                            <p class="text-sm text-black/70 mb-2">${review.nombre_usuario} - ${Utils.formatDate(review.fecha_creacion)}</p>
                            ${this.renderStars(review.calificacion)}
                            ${esVerificada ? `<div class="flex items-center gap-2 mt-2"><span class="material-symbols-outlined text-sm text-verde">verified</span><span class="text-xs font-semibold text-verde">Compra Verificada</span></div>` : ''}
                            ${botonesGestion}
                            <p class="mt-3 text-black">${review.comentario}</p>
                        </div>
                    </div>
                </div>
            `;
        }

        renderStars(calificacion) {
            const full = parseInt(calificacion);
            const empty = 5 - full;
            return `
                <div class="flex items-center text-gray-800 dark:text-gray-200">
                    ${'<span class="material-symbols-outlined text-amarillo">star</span>'.repeat(full)}
                    ${'<span class="material-symbols-outlined text-content-subtle opacity-50">star</span>'.repeat(empty)}
                </div>
            `;
        }

        async fetchAndRenderReviews() {
            this.container.innerHTML = '<p class="text-center p-6 text-content-subtle">Cargando opiniones...</p>';
            try {
                const res = await fetch(API_URLS.reviews.producto(this.producto.id_producto));
                const reviews = (await res.json()).data || [];
                this.allReviews = reviews;
                console.log(this.allReviews);

                if (reviews.length > 0) {
                    const controlsHtml = `
                        <div class="pb-6 border-b border-surface-light mb-8">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <select class="w-full py-2 px-3 bg-background-light border border-surface-dark rounded-lg focus:ring-primary focus:border-primary" id="sortSelect">
                                    <option value="recent">Más reciente</option>
                                    <option value="rating_desc">Mejor calificación</option>
                                    <option value="rating_asc">Peor calificación</option>
                                </select>
                                <select class="w-full py-2 px-3 bg-background-light border border-surface-dark rounded-lg focus:ring-primary focus:border-primary" id="ratingFilter">
                                     <option value="all">Todas las calificaciones</option>
                                    <option value="5">5 estrellas</option>
                                    <option value="4">4 estrellas</option>
                                    <option value="3">3 estrellas</option>
                                    <option value="2">2 estrellas</option>
                                    <option value="1">1 estrella</option>
                                </select>
                                <button class="w-full bg-primary text-white font-bold py-3 px-4 rounded-lg hover:bg-primary/90 transition-colors duration-300 flex items-center justify-center gap-2 js-open-review-modal">
                    <span class="material-symbols-outlined">edit</span>
                    Escribir una Opinión
                                </button>
                            </div>
                        </div>
                    `;
                    const reviewsHtml = reviews.map(r => this.renderReviewCard(r)).join('');
                    this.container.innerHTML = controlsHtml + `<div class="space-y-8">${reviewsHtml}</div>`;
                } else {
                    this.container.innerHTML = `
                        <div class="p-6 bg-secondary rounded-lg text-center">
                            <p class="text-black">Aún no hay opiniones. ¡Sé el primero!</p>
                            <button class="mt-4 bg-primary text-white font-bold py-2 px-4 rounded-lg hover:bg-primary/90 js-open-review-modal">
                                Escribir una Opinión
                            </button>
                        </div>
                    `;
                }
            } catch (e) {
                console.error('Error al cargar reseñas:', e);
                this.container.innerHTML = `<div class="p-6 bg-danger/10 text-danger rounded-lg text-center">Error al cargar opiniones.</div>`;
            }
        }


        // --- Modales ---
        bindModalEvents() {
            // Delegación de eventos
            this.container.addEventListener('click', (e) => {
                if (e.target.closest('.js-open-review-modal')) {
                    ReviewModal.show(null, this.producto.id_producto);
                }
                if (e.target.closest('.js-edit-review')) {
                    const id = e.target.closest('.js-edit-review').dataset.reviewId;
                    this.cargarYMostrarResenaParaEdicion(id);
                }
                if (e.target.closest('.js-delete-review')) {
                    const id = e.target.closest('.js-delete-review').dataset.reviewId;
                    ConfirmDeleteModal.show(id, () => this.eliminarReview(id));
                }
            });
        }

        async cargarYMostrarResenaParaEdicion(reviewId) {
            const token = localStorage.getItem('token');
            if (!token) return alert('Debes iniciar sesión.');
            console.log("id_producto: ", producto.id_producto)
            try {
                const res = await fetch(API_URLS.reviews.miResena(producto.id_producto), {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                const review = (await res.json()).data;
                ReviewModal.show(review, this.producto.id_producto);
            } catch (e) {
                alert('No se pudo cargar la reseña.');
            }
        }

        async eliminarReview(reviewId) {
            const token = localStorage.getItem('token');
            if (!token) return alert('Debes iniciar sesión.');
            try {
                const res = await fetch(API_URLS.reviews.gestion(reviewId), {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                if (res.ok) {
                    alert('Reseña eliminada.');
                    this.fetchAndRenderReviews();
                    this.updateRating(this.producto.id_producto);
                } else {
                    alert('Error al eliminar.');
                }
            } catch (e) {
                alert('Error de conexión.');
            }
        }
        //funcion de filtrado
        applyFilters(sort = 'recent', ratingFilter = 'all') {
            let filtered = this.allReviews;

            if (ratingFilter !== 'all') {
                const stars = parseInt(ratingFilter);
                filtered = filtered.filter(r => r.calificacion === stars);
            }

            if (sort === 'rating_desc') {
                filtered.sort((a, b) => b.calificacion - a.calificacion);
            } else if (sort === 'rating_asc') {
                filtered.sort((a, b) => a.calificacion - b.calificacion);
            } else {
                filtered.sort((a, b) => new Date(b.fecha_creacion) - new Date(a.fecha_creacion));
            }

            const reviewsHtml = filtered.map(r => this.renderReviewCard(r)).join('');
            // Solo actualiza la lista de reseñas, no los controles
            const reviewsList = this.container.querySelector('.space-y-8');
            if (reviewsList) {
                reviewsList.innerHTML = reviewsHtml;
            }
        }
    }

    // ==================================================
    // 6. MODAL DE RESEÑA (crear/editar)
    // ==================================================
    const ReviewModal = {
        modal: document.getElementById('reviewModal'),
        form: null,
        modoEdicion: false,
        idResena: null,

        init() {
            this.form = this.modal.querySelector('form');
            this.bindEvents();
        },

        bindEvents() {
            const closeBtn = document.getElementById('closeModalBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            closeBtn.addEventListener('click', () => this.hide());
            cancelBtn.addEventListener('click', () => this.hide());
            this.modal.addEventListener('click', (e) => {
                if (e.target === this.modal) this.hide();
            });
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        },

        show(datos = null, idProducto) {
            this.modal.classList.remove('hidden');
            this.modal.classList.add('flex');

            const titleEl = document.getElementById('tituloModal');
            const submitBtn = this.form.querySelector('button[type="submit"]');

            if (datos) {
                // Modo edición
                this.modoEdicion = true;
                this.idResena = datos.id_review;
                document.getElementById('review-title').value = datos.titulo || '';
                document.getElementById('review-body').value = datos.comentario || '';
                document.querySelectorAll('input[name="rating"]').forEach(input => {
                    input.checked = parseInt(input.value) === datos.calificacion;
                });
                titleEl.textContent = 'Actualiza tu Opinión';
                submitBtn.innerHTML = '<span class="material-symbols-outlined">edit</span> Actualizar Reseña';
            } else {
                // Modo creación
                this.modoEdicion = false;
                this.idResena = null;
                this.form.reset();
                titleEl.textContent = 'Escribe tu Opinión';
                submitBtn.innerHTML = '<span class="material-symbols-outlined">send</span> Publicar Reseña';
            }
        },

        hide() {
            this.modal.classList.add('hidden');
            this.modal.classList.remove('flex');
            this.form.reset();
        },

        async handleSubmit(e) {
            e.preventDefault();
            const token = localStorage.getItem('token');
            if (!token) return alert('Debes iniciar sesión.');

            const formData = new FormData(this.form);
            const calificacion = formData.get('rating');
            const titulo = formData.get('review-title');
            const comentario = formData.get('review-body');

            if (!calificacion || !titulo || !comentario) {
                return alert('Completa todos los campos.');
            }

            const reviewData = {
                titulo,
                calificacion: parseInt(calificacion),
                comentario
            };

            try {
                let url, method;
                if (this.modoEdicion) {
                    url = API_URLS.reviews.gestion(this.idResena);
                    method = 'PUT';
                } else {
                    url = API_URLS.reviews.crear;
                    method = 'POST';
                    reviewData.id_producto = this.productoId;
                }

                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify(reviewData)
                });

                if (res.ok) {
                    alert(this.modoEdicion ? '¡Reseña actualizada!' : '¡Reseña publicada!');
                    this.hide();
                    // Recargar opiniones y calificación
                    const tabs = new TabManager(producto);
                    tabs.setActiveTab('opiniones');
                    tabs.updateProductRating(producto.id_producto);
                } else {
                    alert('Error al guardar.');
                }
            } catch (e) {
                alert('Error de conexión.');
            }
        },

        // Para que el modal conozca el id_producto en modo creación
        setProductoId(id) {
            this.productoId = id;
        }
    };

    // ==================================================
    // 7. MODAL DE CONFIRMACIÓN DE ELIMINACIÓN
    // ==================================================
    const ConfirmDeleteModal = {
        modal: document.getElementById('confirmDeleteModal'),
        reviewId: null,
        callback: null,

        init() {
            const cancelBtn = document.getElementById('cancelDeleteBtn');
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            cancelBtn.addEventListener('click', () => this.hide());
            confirmBtn.addEventListener('click', () => {
                if (this.callback) this.callback();
                this.hide();
            });
        },

        show(reviewId, callback) {
            this.reviewId = reviewId;
            this.callback = callback;
            this.modal.classList.remove('hidden');
        },

        hide() {
            this.modal.classList.add('hidden');
            this.reviewId = null;
            this.callback = null;
        }
    };

    // ==================================================
    // 8. INICIALIZACIÓN
    // ==================================================
    // Renderizar producto
    new ProductRenderer(producto);

    // Inicializar modales
    ReviewModal.init();
    ReviewModal.setProductoId(producto.id_producto);
    ConfirmDeleteModal.init();

    // Inicializar pestañas
    const tabManager = new TabManager(producto);
    tabManager.setActiveTab('descripcion');
});