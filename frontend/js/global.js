// === js/global.js ===

// --- Configuración Global ---
const host = window.location.hostname;
const apiUrl = `http://${host}/emarket_bolivia/backend/public`;

// ==========================================================
// === LÓGICA DE AUTENTICACIÓN Y RENDERIZADO DEL NAVBAR ===
// ==========================================================

async function cargarPerfil() {
    const token = localStorage.getItem('token');
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
        const avatarUrl = user.avatar_url || `https://i.pravatar.cc/30?u=${user.email}`;
        const userName = user.nombres ? user.nombres.split(' ')[0] : 'Mi Cuenta';

        authContainer.innerHTML = `
            <a href="perfilUsuario.php" class="user-profile-nav" title="Ir a Mi Cuenta">
                <img src="${avatarUrl}" alt="Avatar de ${userName}" class="user-avatar-nav">
                <p class="user-name-nav d-none d-sm-block">${userName}</p>
            </a>
        `;
    } else {
        authContainer.innerHTML = `
            <a href="login.html" class="btn btn-login">Iniciar Sesión</a>
        `;
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

function agregarAlCarrito(idProducto) {
    // Aquí iría la lógica real para agregar el producto al carrito
    alert('Producto agregado al carrito: ' + idProducto);
}

// Ejecutar el renderizado del navbar al cargar el DOM, 
// ya que es común a todas las páginas que incluyan este script.
document.addEventListener('DOMContentLoaded', renderAuthContainer);