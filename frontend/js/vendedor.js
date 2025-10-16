// vendedor.js
class VendedorManager {
    constructor() {
        this.data = null;
        this.productos = []; // Almacén para productos (fetch separado)
        this.init();
    }

    async init() {
        // Carga única de datos del vendedor via API
        try {
            const idVendedor = new URLSearchParams(window.location.search).get('vendedor') || 1;
            const response = await fetch(`${apiUrl}/vendedores/perfil/${idVendedor}`);
            if (!response.ok) throw new Error('Error en API de vendedor');
            const vendedorJson = await response.json();
            if (vendedorJson.status !== 'success') throw new Error(vendedorJson.mensaje || 'Error en respuesta');
            this.data = vendedorJson.data;
            this.renderBanner();
            this.mostrarPestana('inicio'); // Por defecto: Inicio
        } catch (error) {
            console.error('Error cargando datos del vendedor:', error);
            document.querySelectorAll('.pestana').forEach(sec => sec.style.display = 'none');
            const inicioSec = document.getElementById('inicio');
            if (inicioSec) {
                inicioSec.innerHTML = '<div class="col-12"><div class="alert alert-danger">Error al cargar datos del vendedor.</div></div>';
                inicioSec.style.display = 'block';
            }
            return; // No continuar si falla
        }

        // Event listeners para pestañas
        document.querySelectorAll('.nav-link[data-pestana]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const pestana = e.target.dataset.pestana;
                this.mostrarPestana(pestana);
                // Actualiza active en nav
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                e.target.classList.add('active');
            });
        });
    }

    renderBanner() {
        document.getElementById('nombre-empresa').textContent = this.data.razon_social;
        // Opcional: Cambiar background si agregas un campo banner_url en el futuro
    }

    mostrarPestana(pestana) {
        // Oculta todas
        document.querySelectorAll('.pestana').forEach(sec => sec.style.display = 'none');
        // Muestra la seleccionada
        const seccion = document.getElementById(pestana);
        if (seccion) seccion.style.display = 'block';

        // Renderiza contenido basado en data
        switch (pestana) {
            case 'inicio':
                this.renderProductosMuestra();
                break;
            case 'perfil':
                this.renderPerfilEmpresa();
                break;
            case 'contacto':
                this.renderContacto();
                break;
        }
    }

    async renderProductosMuestra() {
        const contenedor = document.getElementById('productos-muestra');
        if (!contenedor) return;

        // Si ya cargados, no refetchear
        if (this.productos.length > 0) {
            this.generarTarjetasProductos(contenedor);
            return;
        }

        // Fetch separado para productos públicos del vendedor (máx 8)
        try {
            const idVendedor = new URLSearchParams(window.location.search).get('vendedor') || this.data.id_vendedor;
            const response = await fetch(`${apiUrl}/productos/listarPorVendedor/${idVendedor}?pagina=1&por_pagina=12`);
            console.log(response);
            if (!response.ok) throw new Error('Error en API de productos');
            const productosJson = await response.json();
            if (productosJson.status !== 'success') throw new Error(productosJson.mensaje || 'Error en respuesta');
            this.productos = productosJson.data.productos || [];
            this.generarTarjetasProductos(contenedor);
        } catch (error) {
            console.error('Error cargando productos:', error);
            contenedor.innerHTML = '<div class="col-12"><div class="alert alert-danger">Error al cargar productos.</div></div>';
        }
    }

    generarTarjetasProductos(contenedor) {
        contenedor.innerHTML = '';
        if (this.productos.length === 0) {
            contenedor.innerHTML = '<div class="col-12"><p>No hay productos publicados por este vendedor.</p></div>';
            return;
        }

        // Limitar a 8 para muestra
        const productosMuestra = this.productos.slice(0, 8);

        productosMuestra.forEach(producto => {
            const col = document.createElement('div');
            col.className = 'col-12 col-md-6 col-lg-4 col-xl-3'; // 1 móvil, 2 tablet, 3 desktop mediano, 4 extra grande// Responsive: 1 móvil, 2 tablet, 4 web
            col.innerHTML = this.createProductCard(producto);
            contenedor.appendChild(col);
        });
    }

    /**
     * Crea la tarjeta de producto adaptada para vendedor (basado en el ejemplo, pero sin filtro de oferta obligatoria).
     * Muestra: imagen, nombre, precio (promocional si hay), stock, rating (relevantes).
     * @param {Object} producto - Producto del JSON
     * @returns {string} HTML de la tarjeta
     */
    createProductCard(producto) {
        // Precio promocional si existe
        let precioHtml = `<p class="fw-bold text-success mb-0">Bs. ${parseFloat(producto.precio).toFixed(2)}</p>`;
        let oldPriceHtml = '';
        let badgeHtml = '';
        if (producto.precio_promocional && parseFloat(producto.precio_promocional) < parseFloat(producto.precio)) {
            const evento = producto.evento_asociado;
            const currentPrice = parseFloat(producto.precio_promocional).toFixed(2);
            precioHtml = `<div class="text-xl font-bold text-success mb-0">Bs ${currentPrice}</div>`;
            oldPriceHtml = `<div class="text-sm text-black/50 line-through">Bs ${parseFloat(producto.precio).toFixed(2)}</div>`;

            // Badge de descuento si aplica
            if (evento) {
                let valorDescuento = evento.valor_descuento || '0';
                if (valorDescuento.endsWith('.00')) valorDescuento = valorDescuento.replace('.00', '');
                if (evento.tipo_aplicacion === 'porcentaje') {
                    badgeHtml = `<div class="position-absolute top-2 start-2 bg-danger text-white text-xs font-bold px-2 py-1 rounded">-${valorDescuento}%</div>`;
                } else if (evento.tipo_aplicacion === 'monto_fijo' && parseFloat(valorDescuento) > 0) {
                    badgeHtml = `<div class="position-absolute top-2 start-2 bg-danger text-white text-xs font-bold px-2 py-1 rounded">-${valorDescuento} Bs</div>`;
                }
            }
        }

        // Stock
        const isOutOfStock = parseInt(producto.stock) <= 0;
        const stockClass = isOutOfStock ? 'text-danger' : 'text-success';
        const stockText = isOutOfStock ? 'Agotado' : `Stock ${producto.stock}`;
        const stockIcon = isOutOfStock ? 'bi bi-x-circle' : 'bi bi-check-circle';
        const stockHtml = `<div class="d-flex align-items-center mt-1"><i class="${stockIcon} me-1"></i><small class="${stockClass}">${stockText}</small></div>`;

        // Rating
        const ratingValue = parseFloat(producto.promedio_calificacion).toFixed(1);
        const ratingHtml = `
            <div class="d-flex align-items-center mt-1 text-warning">
                <i class="bi bi-star-fill me-1"></i>
                <small>${ratingValue} (${producto.total_opiniones} opiniones)</small>
            </div>
        `;

        return `
            <a href="#" onclick="verProducto(${producto.id_producto}); return false;" class="text-decoration-none">
                <div class="product-card shadow-sm h-100">
                    <div class="position-relative">
                        <img src="${producto.imagen_principal_ruta || 'img/no-image.png'}" alt="${producto.nombre}"
                            class="w-100 img-fluid" style="height:200px;object-fit:cover;border-radius:8px 8px 0 0;">
                        ${badgeHtml}
                    </div>
                    <div class="p-2 text-center">
                        <h6 class="text-dark mb-2">${producto.nombre}</h6>
                        <div class="text-center mb-2">
                            ${precioHtml}
                            ${oldPriceHtml}
                        </div>
                        ${stockHtml}
                        ${ratingHtml}
                    </div>
                </div>
            </a>
        `;
    }

    renderPerfilEmpresa() {
        const infoEmpresa = document.getElementById('info-empresa');
        if (infoEmpresa) {
            infoEmpresa.innerHTML = `
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <h2 class="text-2xl font-bold text-azul mb-2">Perfil de la Empresa</h2>
        <p class="text-lg font-semibold text-gray-800 mb-1">${this.data.razon_social}</p>
        <p class="text-md text-gray-600 mb-4">${this.data.descripcion_negocio}</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-700">
          <div>
            <span class="font-medium text-gray-900">Tipo de entidad:</span>
            <span class="ml-1 capitalize">${this.data.tipo_vendedor}</span>
          </div>
          <div>
            <span class="font-medium text-gray-900">NIT:</span>
            <span class="ml-1">${this.data.nit}</span>
          </div>
          <div>
            <span class="font-medium text-gray-900">Matrícula comercial:</span>
            <span class="ml-1">${this.data.matricula_comercial}</span>
          </div>
          <div>
            <span class="font-medium text-gray-900">Cuenta bancaria:</span>
            <span class="ml-1">${this.data.cuenta_bancaria}</span>
          </div>
        </div>
      </div>
    `;
        }

        const representante = document.getElementById('representante');
        if (representante) {
            const cargo = this.data.tipo_vendedor === 'individual' ? 'Propietario' : 'Representante Legal';
            representante.innerHTML = `
      <div class="mt-6 bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <h3 class="text-lg font-bold text-azul mb-2">Representante</h3>
        <p class="text-sm text-gray-700">
          <span class="font-medium text-gray-900">${cargo}:</span>
          <span class="ml-1">${this.data.representante}</span>
        </p>
      </div>
    `;
        }
    }


    renderContacto() {
        const infoContacto = document.getElementById('info-contacto');
        let imgUrl = this.data.imagen_perfil || '/default-avatar.png';
        if (!imgUrl.startsWith('https')) {
            imgUrl = apiUrl + imgUrl;
        }
        if (infoContacto) {
            const nombreRep = this.data.tipo_vendedor === 'individual' ? this.data.razon_social : 'Representante';
            infoContacto.innerHTML = `
                <div class="bg-white rounded-xl p-6 space-y-4">
        <h2 class="text-xl font-bold text-azul">Información de Contacto</h2>

        <div class="flex items-center gap-4">
          <img src="${imgUrl}" alt="Contacto" class="w-16 h-16 rounded-full object-cover border border-gray-300" />
          <div>
            <p class="text-base font-semibold text-gray-800">${this.data.representante}</p>
            <p class="text-sm text-gray-600">Representante de Ventas</p>
          </div>
        </div>
                <hr>
                <p><i class="bi bi-telephone"></i> <span class="font-semibold"> Teléfono: </span><a href="tel:${this.data.telefono_comercial}">${this.data.telefono_comercial}</a></p>
                <p><i class="bi bi-phone"></i> <span class="font-semibold"> Móvil: </span><a href="tel:${this.data.telefono_comercial}">${this.data.telefono_comercial}</a></p>
                <p><i class="bi bi-globe"></i> <span class="font-semibold"> Sitio web: </span><a href="${this.data.enlace_contacto}" target="_blank">${this.data.enlace_contacto || 'No disponible'}</a></p>
            `;
        }

        const ubicacion = document.getElementById('ubicacion');
        if (ubicacion && this.data.direcciones && this.data.direcciones.length > 0) {
            const dir = this.data.direcciones[0];
            const direccionCompleta = `${dir.calle} ${dir.numero}, ${dir.zona}, ${dir.ciudad}, ${dir.provincia}, ${dir.departamento}`;
            ubicacion.innerHTML = `
      <div class=" bg-white rounded-xl  p-6  space-y-2">
        <h2 class="text-xl font-bold text-azul">Ubicación de la Empresa</h2>
        <p class="text-sm text-gray-700"><i class="bi bi-geo-alt"></i> ${direccionCompleta}</p>
        <p class="text-xs text-gray-500">Verificado por eMarket Bolivia</p>
      </div>
    `;
        }

        const nombreEmpresaContacto = document.getElementById('nombre-empresa-contacto');
        if (nombreEmpresaContacto) {
            nombreEmpresaContacto.textContent = this.data.razon_social;
        }
    }
}

// Instancia al cargar
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => new VendedorManager());
} else {
    new VendedorManager();
}