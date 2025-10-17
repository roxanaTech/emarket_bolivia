// === js/global.js ===

// --- Configuración Global ---
var host = window.location.hostname;
var apiUrl = `http://${host}/emarket_bolivia/backend/public`;
var token = localStorage.getItem('token');

// ==========================================================
// === LÓGICA DE AUTENTICACIÓN Y RENDERIZADO DEL NAVBAR ===
// ==========================================================
// Función helper para decodificar el payload de un JWT (solo para lectura/UI, no verifica firma)
function parseJwt(token) {
    try {
        const base64Url = token.split('.')[1];
        const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        const jsonPayload = decodeURIComponent(atob(base64).split('').map(c =>
            '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)
        ).join(''));
        return JSON.parse(jsonPayload);
    } catch (e) {
        console.warn('[JWT] Error al decodificar payload:', e);
        return null;
    }
}
async function cargarPerfil() {

    if (!token) return null;

    try {
        const response = await fetch(`${apiUrl}/usuarios/perfil`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            localStorage.removeItem('token');
            return null;
        }

        const data = await response.json();
        return data.status === 'success' ? data.data : null;

    } catch (error) {
        console.error('Error al cargar el perfil:', error);
        return null;
    }

}

async function renderAuthContainer() {
    const authContainer = document.getElementById('auth-container');
    if (!authContainer) return; // Salir si no existe el contenedor

    const user = await cargarPerfil();
    authContainer.innerHTML = ''; // Limpiamos el contenido

    if (user) {
        let imgUrl = user.imagen || './img/default-avatar.png'; // Fallback si no hay imagen
        if (!imgUrl.startsWith('http')) {
            // Si es ruta relativa (uploads), concatenar apiUrl (ajusta si usas base_url)
            imgUrl = apiUrl + imgUrl;
        }
        const userName = user.nombres ? user.nombres.split(' ')[0] : 'Usuario';
        const fullUserName = user.nombres || 'Usuario'; // Para el saludo en el menú
        const userRol = user.rol || 'comprador'; // Ahora viene directo del endpoint

        // 👇 Oculta el link "Empieza a vender" si es vendedor
        const startSellingLink = document.getElementById('start-selling-link');
        if (startSellingLink) {
            if (userRol === 'vendedor') {
                startSellingLink.style.display = 'none'; // Oculta completamente
            } else {
                startSellingLink.style.display = 'block'; // Muestra si no es vendedor
            }
        }

        // Renderiza el botón del perfil con Alpine.js para el dropdown
        authContainer.innerHTML = `
            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                <!-- Botón del perfil (click para toggle dropdown) -->
                <button @click="open = !open" class="flex items-center gap-2 text-white hover:text-blue-300 transition" title="Mi Perfil">
                    <img src="${imgUrl}" alt="Avatar de ${userName}" class="w-8 h-8 rounded-full object-cover">
                    <span class="hidden md:block text-sm font-medium">${userName}</span>
                    <!-- Flecha dropdown (opcional, para UX) -->
                    <i class="bi bi-chevron-down text-xs transition-transform" :class="open ? 'rotate-180' : ''"></i>
                </button>

                <!-- Menú desplegable -->
                <div @click.away="open = false"
                     class="absolute right-0 z-10 mt-2 w-64 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                     x-cloak="" x-show="open" x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     x-transition:leave-start="transform opacity-100 scale-100">
                    <div class="py-1">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-sm font-medium text-gray-900">Hola, ${fullUserName}</p>
                        </div>
                        <a class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition"
                           href="perfilUsuario.php">
                            <span class="material-icons text-base">account_circle</span>
                            <span>Mi Cuenta</span>
                        </a>
                        <a class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition"
                           href="perfilUsuario.php">
                            <span class="material-icons text-base">shopping_bag</span>
                            <span>Mis Pedidos</span>
                        </a>
                        <hr class="my-1 border-gray-200" />
                        <!-- Opción condicional del panel de vendedor -->
                        ${userRol === 'vendedor' ? `
                        <a class="flex items-center gap-3 rounded-md bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-900 hover:bg-blue-100 transition"
                           href="panel-vendedor.php"> <!-- Ajusta la URL -->
                            <span class="material-icons text-base">store</span>
                            <span>Ir a mi Panel de Vendedor</span>
                        </a>
                        <hr class="my-1 border-gray-200" />
                        ` : ''}
                        <a class="flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition"
                           href="#" onclick="cerrarSesion(); return false;"> <!-- Llama a tu función de logout -->
                            <span class="material-icons text-base">logout</span>
                            <span>Cerrar Sesión</span>
                        </a>
                    </div>
                </div>
            </div>
        `;
    } else {
        authContainer.innerHTML = `
          <a href="login.php"
   class="inline-flex items-center justify-center px-4 py-2 rounded-full md:bg-primary/40 text-white text-sm font-semibold hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all duration-200">
   <i class="bi bi-person-circle text-base md:mr-2"></i>
   <span class="hidden md:inline">Iniciar Sesión</span>
</a>

        `;
        // 👇 Si no hay user, muestra el link "Empieza a vender"
        const startSellingLink = document.getElementById('start-selling-link');
        if (startSellingLink) {
            startSellingLink.style.display = 'block';
        }
    }
}

// Función para cerrar sesión (ajusta según tu lógica, ej. limpiar localStorage y redirigir)
function cerrarSesion() {
    localStorage.removeItem('token');
    // Opcional: llama a un endpoint de logout si lo tienes
    window.location.href = 'login.php';
}


// Función para obtener el token (reutilizable)
function obtenerToken() {
    const token = localStorage.getItem('token');  // Ajusta si la clave es diferente
    if (!token) {
        console.warn('Token no encontrado en localStorage');
        return null;
    }
    return token;
}

// Función para actualizar el badge del carrito
async function actualizarBadgeCarrito() {
    console.log('Debug: Actualizando badge del carrito...');

    const token = obtenerToken();
    if (!token) {
        // Si no hay token, ocultar o poner 0
        const badge = document.getElementById('badge-carrito');
        if (badge) badge.textContent = '0';
        return;
    }

    try {
        const respuesta = await fetch(`${apiUrl}/carrito/items`, {
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
        console.log('Debug: Respuesta del endpoint /carrito/items:', resultado);

        if (resultado.status !== 'success') {
            throw new Error(resultado.mensaje || 'Error al obtener total de items');
        }

        const totalItems = parseInt(resultado.data.total_items) || 0;
        const badge = document.getElementById('badge-carrito');

        if (badge) {
            badge.textContent = totalItems;
            // Opcional: Ocultar badge si es 0
            if (totalItems === 0) {
                badge.style.display = 'none';
            } else {
                badge.style.display = 'flex';
            }
        } else {
            console.warn('Debug: Elemento #badge-carrito no encontrado');
        }

        console.log(`Debug: Badge actualizado a ${totalItems} items`);

    } catch (error) {
        console.error('Debug: Error en actualizarBadgeCarrito:', error);
        // No mostrar toast aquí para no spamear; solo log
        const badge = document.getElementById('badge-carrito');
        if (badge) badge.textContent = '0';
    }
}

// ==========================================================
// === FUNCIONES GLOBALES (ACCIONES) ===
// ==========================================================

function verProducto(idProducto) {
    const url = `${apiUrl}/productos/verProducto`;

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id_producto: idProducto })
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al cargar el producto: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                // Guardar los datos del producto en sessionStorage
                sessionStorage.setItem('productoDetalle', JSON.stringify(data.data));
                // Redirigir a la página de detalle
                window.location.href = 'detalle-producto.php';
            } else {
                alert('Error: ' + data.mensaje);
            }
        })
        .catch(error => {
            console.error('Error al obtener el producto:', error);
            alert('No se pudo cargar el producto. Inténtalo de nuevo.');
        });
}

async function agregarAlCarrito(idProducto, cantidad = 1) {
    // Validar que la cantidad sea un número válido
    if (!Number.isInteger(cantidad) || cantidad < 1) {
        console.error('Cantidad inválida');
        mostrarToast('Error: Cantidad inválida', 'error');
        return;
    }

    // Preparar el JSON para enviar
    const datos = {
        id_producto: idProducto,
        cantidad: cantidad
    };

    try {
        // Enviar POST al endpoint
        const respuesta = await fetch(`${apiUrl}/carrito/agregar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(datos)
        });

        // Verificar si la respuesta es exitosa
        if (!respuesta.ok) {
            throw new Error(`Error del servidor: ${respuesta.status}`);
        }

        // Leer la respuesta del servidor (asumiendo que devuelve JSON, ej: { "success": true, "mensaje": "Producto agregado" })
        const resultado = await respuesta.json();
        console.log(resultado);
        if (resultado.status == "success") {  // Ajusta según la estructura de respuesta de tu PHP
            console.log('Producto agregado al carrito', idProducto);
            mostrarToast('¡Producto agregado al carrito!', 'success');
            await actualizarBadgeCarrito();
        } else {
            throw new Error(resultado.mensaje || 'Error al agregar al carrito');
        }
    } catch (error) {
        console.error('Error en agregarAlCarrito:', error);
        mostrarToast(`Error: ${error.message}`, 'error');
    }
}
function mostrarToast(mensaje, tipo = 'info') {
    const container = document.getElementById('toast-container');
    console.log(container);
    if (!container) return;

    // Colores según tipo (usa Tailwind)
    const clasesBg = {
        success: 'bg-shipping-light text-white',
        error: 'bg-offer-light text-white',
        info: 'bg-fondo-azul text-white'
    };
    const bgClass = clasesBg[tipo] || clasesBg.info;

    // Crear el toast HTML
    const toast = document.createElement('div');
    toast.className = `p-4 rounded-lg shadow-lg ${bgClass} transform translate-x-full transition-transform duration-300 ease-in-out mb-2 max-w-sm`;
    toast.innerHTML = `
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined">${tipo === 'success' ? 'check_circle' : tipo === 'error' ? 'error' : 'info'}</span>
            <span>${mensaje}</span>
        </div>
    `;
    console.log("mostrando el toast");
    // Agregar al container
    container.appendChild(toast);
    container.classList.remove('hidden');

    // Animación: slide in
    setTimeout(() => toast.classList.remove('translate-x-full'), 100);

    // Auto-desaparecer después de 3 segundos y slide out
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            container.removeChild(toast);
            if (container.children.length === 0) {
                container.classList.add('hidden');
            }
        }, 300);
    }, 3000);
}

// Ejecutar el renderizado del navbar al cargar el DOM, 
// Inicializar badge al cargar DOM (en todas las páginas)
document.addEventListener('DOMContentLoaded', () => {
    console.log('Debug: DOM cargado, actualizando badge carrito...');
    renderAuthContainer();
    actualizarBadgeCarrito();
});