// carrito.js - Manejo completo del carrito de compras

// Función para obtener el token de localStorage
function obtenerToken() {
    const token = localStorage.getItem('token');  // Ajusta la clave si es diferente
    if (!token) {
        console.warn('Token no encontrado en localStorage');
        mostrarToast('Error: Debes iniciar sesión para ver el carrito', 'error');
        return null;
    }
    return token;
}

// Función principal para cargar el carrito
async function cargarCarrito() {
    console.log('Debug: Iniciando carga del carrito...');

    const token = obtenerToken();
    if (!token) return;

    try {
        const respuesta = await fetch(`${apiUrl}/carrito/listar`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        if (!respuesta.ok) {
            throw new Error(`Error del servidor: ${respuesta.status}`);
        }

        const resultado = await respuesta.json();
        console.log('Debug: Respuesta del endpoint listar:', resultado);

        if (resultado.status !== 'success') {
            throw new Error(resultado.mensaje || 'Error al cargar carrito');
        }

        // Obtener items y total
        const items = resultado.data.items || [];
        const totalGeneral = resultado.data.total_general || 0;

        // Generar HTML para items
        const contenedorItems = document.getElementById('contenedor-items-carrito');
        if (!contenedorItems) {
            console.error('Debug: Contenedor #contenedor-items-carrito no encontrado');
            return;
        }

        if (items.length === 0) {
            contenedorItems.innerHTML = `
  <div class="flex flex-col items-center justify-center text-center py-16 px-4 bg-white dark:bg-background-dark rounded-xl shadow-sm border border-dashed border-gray-300 dark:border-gray-600">
    <i class="bi bi-cart-x text-5xl text-gray-400 mb-4"></i>
    <p class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Tu carrito está vacío</p>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Aún no has agregado productos.</p>
    <a href="principal.php" class="inline-block px-5 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors">
      ¡Empieza a comprar!
    </a>
  </div>
`;
            const pagar = document.getElementById("btn-pagar");
            pagar.disabled = true;
            pagar.style.backgroundColor = 'gray';
            actualizarResumen(0);
            return;
        }

        let htmlItems = '';
        items.forEach(item => {
            htmlItems += generarHtmlItem(item);
        });

        contenedorItems.innerHTML = htmlItems;

        // Actualizar resumen
        actualizarResumen(totalGeneral);

        // Agregar event listeners a los nuevos items (después de insertar HTML)
        agregarEventListenersItems();

        console.log('Debug: Carrito cargado exitosamente con', items.length, 'items');
        mostrarToast('Carrito cargado correctamente', 'success');

    } catch (error) {
        console.error('Debug: Error en cargarCarrito:', error);
        mostrarToast(`Error al cargar carrito: ${error.message}`, 'error');
    }
}

// Función para generar HTML de un item del carrito
function generarHtmlItem(item) {
    const { id_item, id_producto, nombre, stock, precio_unitario, subtotal, cantidad, imagen_principal } = item;
    const precioFormateado = parseFloat(precio_unitario).toFixed(2);
    const subtotalFormateado = parseFloat(subtotal).toFixed(2);

    return `
        <div class="flex flex-col sm:flex-row items-center bg-white dark:bg-background-dark/50 p-4 rounded-lg shadow-sm gap-4">
            <img alt="${nombre}" class="w-24 h-24 object-cover rounded-lg" src="${imagen_principal}" />
            <div class="flex-grow text-center sm:text-left">
                <a class="font-semibold text-lg text-primary hover:font-bold" href="#" onclick="verProducto(${id_producto}); return false;">${nombre}</a>
                <p class="text-sm text-green-600 font-medium">En Stock (${stock} unidades)</p>
                <p class="text-md text-black font-medium">Bs ${precioFormateado}</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="btn-decrease p-1 rounded-full bg-background-light dark:bg-background-dark hover:bg-gray-200 dark:hover:bg-gray-700" data-id-item="${id_item}">
                    <span class="material-symbols-outlined text-base">remove</span>
                </button>
                <span class="font-semibold w-8 text-center cantidad-item" data-id-item="${id_item}">${cantidad}</span>
                <button class="btn-increase p-1 rounded-full bg-background-light dark:bg-background-dark hover:bg-gray-200 dark:hover:bg-gray-700" data-id-item="${id_item}">
                    <span class="material-symbols-outlined text-base">add</span>
                </button>
            </div>
            <p class="font-bold w-24 text-center sm:text-right subtotal-item" data-id-item="${id_item}">Bs ${subtotalFormateado}</p>
            <button class="btn-eliminar text-red-500 hover:text-red-700 dark:hover:text-red-400" data-id-item="${id_item}">
                <span class="material-symbols-outlined">delete</span>
            </button>
        </div>
    `;
}

// Función para agregar event listeners a todos los items (llamar después de generar HTML)
function agregarEventListenersItems() {
    console.log('Debug: Agregando event listeners a items...');

    // Botones decrease
    document.querySelectorAll('.btn-decrease').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const idItem = parseInt(e.target.closest('.btn-decrease').dataset.idItem);
            const spanCantidad = document.querySelector(`.cantidad-item[data-id-item="${idItem}"]`);
            let nuevaCantidad = parseInt(spanCantidad.textContent) - 1;
            if (nuevaCantidad < 1) nuevaCantidad = 1;  // Mínimo 1
            await actualizarCantidad(idItem, nuevaCantidad);
        });
    });

    // Botones increase
    document.querySelectorAll('.btn-increase').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const idItem = parseInt(e.target.closest('.btn-increase').dataset.idItem);
            const spanCantidad = document.querySelector(`.cantidad-item[data-id-item="${idItem}"]`);
            let nuevaCantidad = parseInt(spanCantidad.textContent) + 1;
            // Opcional: Limitar por stock, pero como el backend lo maneja, solo actualizamos
            await actualizarCantidad(idItem, nuevaCantidad);
        });
    });

    // Botones eliminar
    document.querySelectorAll('.btn-eliminar').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const idItem = parseInt(e.target.closest('.btn-eliminar').dataset.idItem);
            if (confirm('¿Estás seguro de eliminar este item del carrito?')) {
                await eliminarItem(idItem);
            }
        });
    });
}

// Función para actualizar cantidad de un item
async function actualizarCantidad(idItem, nuevaCantidad) {
    console.log(`Debug: Actualizando cantidad para id_item ${idItem} a ${nuevaCantidad}`);

    const token = obtenerToken();
    if (!token) return;

    try {
        const respuesta = await fetch(`${apiUrl}/carrito/actualizar-cantidad`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id_item: idItem,
                cantidad: nuevaCantidad
            })
        });

        if (!respuesta.ok) {
            throw new Error(`Error del servidor: ${respuesta.status}`);
        }

        const resultado = await respuesta.json();
        console.log('Debug: Respuesta de actualizar-cantidad:', resultado);

        if (resultado.status !== 'success') {
            throw new Error(resultado.mensaje || 'Error al actualizar cantidad');
        }

        // Recargar carrito para actualizar todo (incluyendo subtotales y total)
        await cargarCarrito();
        mostrarToast('Cantidad actualizada correctamente', 'success');
        await actualizarBadgeCarrito();

    } catch (error) {
        console.error('Debug: Error en actualizarCantidad:', error);
        mostrarToast(`Error al actualizar: ${error.message}`, 'error');
    }
}

// Función para eliminar un item
async function eliminarItem(idItem) {
    console.log(`Debug: Eliminando id_item ${idItem}`);

    const token = obtenerToken();
    if (!token) return;

    try {
        const respuesta = await fetch(`${apiUrl}/carrito/eliminar-item`, {
            method: 'DELETE',  // O usa DELETE si prefieres
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id_item: idItem
            })
        });

        if (!respuesta.ok) {
            throw new Error(`Error del servidor: ${respuesta.status}`);
        }

        const resultado = await respuesta.json();
        console.log('Debug: Respuesta de eliminar-item:', resultado);

        if (resultado.status !== 'success') {
            throw new Error(resultado.mensaje || 'Error al eliminar item');
        }

        // Recargar carrito
        await cargarCarrito();
        mostrarToast('Item eliminado del carrito', 'success');
        await actualizarBadgeCarrito();

    } catch (error) {
        console.error('Debug: Error en eliminarItem:', error);
        mostrarToast(`Error al eliminar: ${error.message}`, 'error');
    }
}

// Función para actualizar el resumen (subtotal y total)
function actualizarResumen(totalGeneral) {
    const subtotalSpan = document.getElementById('subtotal-resumen');
    const totalSpan = document.getElementById('total-pagar');

    if (subtotalSpan) {
        subtotalSpan.textContent = `Bs ${parseFloat(totalGeneral).toFixed(2)}`;
    }
    if (totalSpan) {
        totalSpan.textContent = `Bs ${parseFloat(totalGeneral).toFixed(2)}`;
    }

    console.log(`Debug: Resumen actualizado a Bs ${totalGeneral}`);
}

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    console.log('Debug: DOM cargado, iniciando carrito...');
    cargarCarrito();
});