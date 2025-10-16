// ordenes.js - Manejo de órdenes en perfil de usuario

// Función para obtener token
function obtenerToken() {
    const token = localStorage.getItem('token');
    if (!token) {
        console.warn('Token no encontrado');
        mostrarToast('Error: Inicia sesión para ver órdenes', 'error');
        return null;
    }
    return token;
}

// Función para formatear fecha
function formatearFecha(fechaStr) {
    const fecha = new Date(fechaStr);
    const opciones = { day: 'numeric', month: 'long', year: 'numeric' };
    return fecha.toLocaleDateString('es-ES', opciones);
}

// Función para badge de estado
function getBadgeEstado(estado) {
    const clases = {
        'pendiente': 'bg-yellow-500/20 text-yellow-500',
        'enviado': 'bg-blue-500/20 text-blue-500',
        'entregado': 'bg-success/20 text-success',
        'cancelado': 'bg-danger/20 text-danger'
    };
    const texto = {
        'pendiente': 'Pendiente',
        'enviado': 'Enviado',
        'entregado': 'Entregado',
        'cancelado': 'Cancelado'
    };
    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${clases[estado] || 'bg-gray-500/20 text-gray-500'}">${texto[estado] || estado}</span>`;
}

// Cargar y mostrar órdenes
async function cargarOrdenes() {
    const token = obtenerToken();
    if (!token) return;

    try {
        const respuesta = await fetch(`${apiUrl}/ventas/comprador`, {
            method: 'GET',
            headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json' }
        });

        if (!respuesta.ok) throw new Error(`Error ${respuesta.status}`);

        const resultado = await respuesta.json();
        console.log('Debug: Datos de órdenes:', resultado);

        if (resultado.status !== 'success' || !resultado.data.length) {
            document.getElementById('contenedor-ordenes-lista').innerHTML = '<p class="text-center text-gray-500 py-8">No hay órdenes disponibles.</p>';
            return;
        }

        // Agrupar por id_venta
        const gruposOrdenes = new Map();
        resultado.data.forEach(fila => {
            if (!gruposOrdenes.has(fila.id_venta)) {
                gruposOrdenes.set(fila.id_venta, {
                    infoVenta: { ...fila },  // Info compartida
                    productos: []
                });
            }
            gruposOrdenes.get(fila.id_venta).productos.push({
                id_detalle: fila.id_detalle,
                id_producto: fila.id_producto,
                nombre: fila.nombre,  // Usar el nuevo campo "nombre"
                cantidad: fila.cantidad,
                precio_unit: parseFloat(fila.precio_unit).toFixed(2),
                subtotal: parseFloat(fila.subtotal).toFixed(2),
                ruta: fila.ruta
            });
        });

        // Guardar global para modal
        window.gruposOrdenes = gruposOrdenes;

        // Generar lista de cards
        let htmlLista = '';
        gruposOrdenes.forEach((grupo, idVenta) => {
            const primerProd = grupo.productos[0];
            htmlLista += `
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 orden-card" data-order-id="${idVenta}" data-estado="${grupo.infoVenta.estado}">
                    <div class="flex flex-wrap justify-between items-center gap-4 mb-4">
                        <div>
                            <p class="font-bold text-gray-800 dark:text-white">Pedido #${idVenta}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Fecha: ${formatearFecha(grupo.infoVenta.fecha)}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-gray-800 dark:text-white">Bs. ${parseFloat(grupo.infoVenta.total_venta).toFixed(2)}</p>
                            ${getBadgeEstado(grupo.infoVenta.estado)}
                        </div>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <div class="flex items-center gap-4">
                            <img alt="Producto Preview" class="w-16 h-16 rounded-md object-cover" src="${primerProd.ruta}" />
                            <div>
                                <p class="font-semibold text-gray-800 dark:text-white">${primerProd.nombre}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Cantidad: ${primerProd.cantidad} | Vendedor: ${grupo.infoVenta.nombre_vendedor}</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end mt-4 gap-2">
                        <label class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white text-sm font-semibold hover:bg-gray-300 dark:hover:bg-gray-600 cursor-pointer ver-detalles-btn" data-order-id="${idVenta}">Ver Detalles</label>
                        <button class="px-4 py-2 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primary/90 volver-comprar-btn" data-product-id="${primerProd.id_producto}">Volver a Comprar</button>
                    </div>
                </div>
            `;
        });

        document.getElementById('contenedor-ordenes-lista').innerHTML = htmlLista;

        // Event listeners para cards
        agregarEventListenersOrdenes();

        console.log('Debug: Órdenes cargadas y agrupadas:', gruposOrdenes.size);

    } catch (error) {
        console.error('Debug: Error cargando órdenes:', error);
        mostrarToast(`Error al cargar órdenes: ${error.message}`, 'error');
        document.getElementById('contenedor-ordenes-lista').innerHTML = '<p class="text-center text-red-500 py-8">Error al cargar. <a href="#" class="underline">Reintentar</a></p>';
    }
}

// Agregar event listeners a cards generadas
function agregarEventListenersOrdenes() {
    // Ver Detalles
    document.querySelectorAll('.ver-detalles-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const idVenta = e.target.dataset.orderId;
            mostrarModalDetalles(idVenta);
            document.getElementById('order-details-modal').checked = true;  // Abrir modal
        });
    });

    // Volver a Comprar (solo primer producto por orden)
    document.querySelectorAll('.volver-comprar-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const idProducto = e.target.dataset.productId;
            verProducto(idProducto);
        });
    });
}

// Mostrar modal con detalles de orden
function mostrarModalDetalles(idVenta) {
    const grupo = window.gruposOrdenes.get(parseInt(idVenta));
    if (!grupo) {
        mostrarToast('Orden no encontrada', 'error');
        return;
    }

    const infoVenta = grupo.infoVenta;
    document.getElementById('modal-titulo').textContent = `Pedido #${idVenta}`;

    let htmlDetalles = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <p class="font-bold text-gray-800 dark:text-white">Pedido #${idVenta}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Fecha: ${formatearFecha(infoVenta.fecha)}</p>
            </div>
            <div class="text-left md:text-right">
                <p class="font-bold text-gray-800 dark:text-white">Bs. ${parseFloat(infoVenta.total_venta).toFixed(2)}</p>
                ${getBadgeEstado(infoVenta.estado)}
            </div>
        </div>
        <div class="space-y-6">
            <div>
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 border-b pb-2 border-gray-200 dark:border-gray-700">Lista de Productos</h4>
                <div class="space-y-4">
    `;

    grupo.productos.forEach(prod => {
        htmlDetalles += `
            <div class="flex items-center gap-4">
                <img alt="Producto" class="w-20 h-20 rounded-md object-cover" src="${prod.ruta}" />
                <div>
                    <p class="font-semibold text-gray-800 dark:text-white">${prod.nombre}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Cantidad: ${prod.cantidad}</p>
                    <p class="text-sm font-bold text-gray-800 dark:text-white">Bs. ${prod.precio_unit} (Subtotal: Bs. ${prod.subtotal})</p>
                </div>
            </div>
        `;
    });

    htmlDetalles += `
                </div>
            </div>
            <div>
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 border-b pb-2 border-gray-200 dark:border-gray-700">Detalles de Envío</h4>
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    <p><span class="font-semibold">Tipo:</span> ${infoVenta.tipo_entrega.replace(/^\w/, c => c.toUpperCase())}</p>
                    <p><span class="font-semibold">Dirección:</span> ${infoVenta.direccion_entrega}</p>
                    <p><span class="font-semibold">Teléfono:</span> ${infoVenta.telefono_contacto}</p>
                </div>
            </div>
            <div>
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 border-b pb-2 border-gray-200 dark:border-gray-700">Información de Pago</h4>
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    <p><span class="font-semibold">Método:</span> ${infoVenta.tipo_pago.replace(/^\w/, c => c.toUpperCase())} ${infoVenta.tipo_pago === 'tarjeta' ? '(Visa **** 1111)' : ''}</p>
                    <p><span class="font-semibold">Estado del Pago:</span> ${infoVenta.comprobante_pago ? 'Pagado (Comprobante: ' + infoVenta.comprobante_pago + ')' : 'Pendiente'}</p>
                </div>
            </div>
        </div>
    `;

    document.getElementById('modal-body-detalles').innerHTML = htmlDetalles;

    // Event listener para botón Volver a Comprar en modal (usa primer producto)
    document.getElementById('btn-volver-comprar').onclick = () => {
        const primerProd = grupo.productos[0];
        window.location.href = `producto.php?id=${primerProd.id_producto}`;
    };
}

// Filtros y búsqueda
function inicializarFiltros() {
    const buscador = document.getElementById('buscador-ordenes');
    const filtro = document.getElementById('filtro-estado');

    function filtrarOrdenes() {
        const termino = buscador.value.toLowerCase();
        const estadoFiltro = filtro.value.toLowerCase();

        document.querySelectorAll('.orden-card').forEach(card => {
            const idVenta = card.dataset.orderId;
            const estado = card.dataset.estado.toLowerCase();
            const textoCard = card.textContent.toLowerCase();

            const coincideBusq = textoCard.includes(termino);
            const coincideEstado = !estadoFiltro || estado === estadoFiltro;

            card.style.display = (coincideBusq && coincideEstado) ? 'block' : 'none';
        });
    }

    buscador.addEventListener('input', filtrarOrdenes);
    filtro.addEventListener('change', filtrarOrdenes);
}

// Inicializar al cargar
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('ordenes-content')) {  // Solo si tab activo
        cargarOrdenes();
        inicializarFiltros();

        // Cerrar modal al click backdrop (opcional)
        document.getElementById('order-details-backdrop').addEventListener('click', (e) => {
            if (e.target.id === 'order-details-backdrop') {
                document.getElementById('order-details-modal').checked = false;
            }
        });
    }
});