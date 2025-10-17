let currentPage = 1;
let currentFilters = { search: '', estado: '', stock: '', subcategoria: '' };
let totalProductos = 0;

// Función para cargar productos (endpoint ajustado)
async function cargarProductos(page = 1, filters = {}) {
    const token = localStorage.getItem('token');
    if (!token) return;

    const params = new URLSearchParams({ pagina: page, por_pagina: 10, ...filters });
    try {
        const res = await fetch(`${apiUrl}/productos/listarMisProductos?${params}`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const data = await res.json();
        if (data.status === 'success') {
            totalProductos = data.data.total;
            renderProductos(data.data.productos);
            renderPaginacion(data.data.pagina, data.data.total_paginas);
            updatePaginationInfo(10 * (page - 1) + 1, Math.min(10 * page, totalProductos), totalProductos);
        } else {
            console.error('Error al cargar productos:', data.mensaje);
        }
    } catch (error) {
        console.error('Error en fetch productos:', error);
    }
}
// Funciones para acciones
function editProduct(id) {
    if (window.productoModalInstance) {
        window.productoModalInstance.loadForEdit(id);
    } else {
        console.error('Modal instance no disponible. Asegúrate de cargar agregar-producto.js primero.');
        // Fallback: redirect si no
        window.location.href = `edit-producto.php?id=${id}`;
    }
}

function deleteProduct(id) {
    if (confirm(`¿Eliminar producto ID ${id}? Esta acción no se puede deshacer.`)) {
        const token = localStorage.getItem('token');
        fetch(`${apiUrl}/productos/eliminar`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id_producto: id })
        })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Producto eliminado exitosamente.');
                    cargarProductos(currentPage, currentFilters);  // Recarga la tabla
                } else {
                    alert('Error al eliminar: ' + (data.mensaje || 'Inténtalo de nuevo.'));
                }
            })
            .catch(err => {
                console.error('Error delete:', err);
                alert('Error de conexión al eliminar producto.');
            });
    }
}

// Listeners para filters (DOMContentLoaded ya maneja, pero agrega para subcategoria)
document.addEventListener('DOMContentLoaded', () => {
    // ... código anterior de listeners ...

    // Para subcategoria-filter (ejemplo; agrega opciones dinámicas si quieres fetch)
    const subFilter = document.getElementById('subcategoria-filter');
    subFilter.addEventListener('change', (e) => {
        currentFilters.subcategoria = e.target.value;
        cargarProductos(1, currentFilters);
    });

    // Bulk actions (ej. delete selected)
    document.getElementById('productos-tbody').addEventListener('change', (e) => {
        if (e.target.type === 'checkbox') {
            const checked = document.querySelectorAll('#productos-tbody input[type="checkbox"]:checked');
            // Ej. Si >0, habilita botón bulk delete (agrega en HTML si quieres)
        }
    });
});

// Renderiza tabla (usa campos del JSON: nombre, sku, precio, stock, estado, nombre_subcategoria, imagen_principal_ruta, promedio_calificacion)
function renderProductos(productos) {
    const tbody = document.getElementById('productos-tbody');
    if (!tbody) return;

    tbody.innerHTML = productos.map(producto => {
        let imgUrl = producto.imagen_principal_ruta || 'http://localhost/emarket_bolivia/frontend/img/default-avatar.png';
        console.log("imgh: ", imgUrl);
        if (!imgUrl.startsWith('http')) {
            imgUrl = apiUrl + '/' + imgUrl;
            console.log("img: ", imgUrl);
        }
        //<td class="px-6 py-4"><input type="checkbox" value="${producto.id_producto}" /></td>
        return `
        <tr class="bg-white border-b hover:bg-gray-50">
            
            <td class="px-6 py-4">
                <img class="h-12 w-12 object-cover rounded-md" src="${imgUrl}" alt="${producto.nombre}" onerror="this.src='./img/default-product.jpg'" />
            </td>
            <td class="px-6 py-4 font-medium text-gray-900">${producto.nombre}</td>
            <td class="px-6 py-4 text-gray-600">${producto.sku}</td>
            <td class="px-6 py-4">Bs. ${parseFloat(producto.precio).toFixed(2)}</td>
            <td class="px-6 py-4 ${producto.stock < 10 ? 'text-danger font-bold' : 'text-success font-bold'}">${producto.stock}</td>
            <td class="px-6 py-4">
                <span class="px-2 py-1 ${producto.estado === 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'} text-xs rounded-full">${producto.estado}</span>
            </td>
            <td class="px-6 py-4 text-gray-600">${producto.nombre_subcategoria}</td>
            <td class="px-6 py-4 flex items-center gap-3">
                <button class="p-1 text-primary hover:bg-primary/10 rounded-full" onclick="editProduct(${producto.id_producto})">
                    <span class="material-symbols-outlined">edit</span>
                </button>
                <button class="p-1 text-danger hover:bg-danger/10 rounded-full" onclick="deleteProduct(${producto.id_producto})">
                    <span class="material-symbols-outlined">delete</span>
                </button>
            </td>
        </tr>
    `;
    }).join('') || '<tr><td colspan="10" class="px-6 py-4 text-center text-gray-500">No hay productos.</td></tr>';


}

// Renderiza paginación (basada en total_paginas)
function renderPaginacion(current, totalPages) {
    const nav = document.getElementById('pagination-nav');
    if (!nav) return;

    let html = '';
    if (current > 1) {
        html += `<li><a class="flex items-center justify-center px-3 h-8 ml-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700" href="#" onclick="cambiarPagina(${current - 1}); return false;">Previous</a></li>`;
    }

    const startPage = Math.max(1, current - 2);
    const endPage = Math.min(totalPages, current + 2);

    if (startPage > 1) html += `<li><a class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700" href="#" onclick="cambiarPagina(1); return false;">1</a></li>`;
    if (startPage > 2) html += `<li><span class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300">...</span></li>`;

    for (let i = startPage; i <= endPage; i++) {
        html += `<li><a class="flex items-center justify-center px-3 h-8 ${i === current ? 'text-white border border-primary bg-primary' : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700'} leading-tight" href="#" onclick="cambiarPagina(${i}); return false;">${i}</a></li>`;
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += `<li><span class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300">...</span></li>`;
        html += `<li><a class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700" href="#" onclick="cambiarPagina(${totalPages}); return false;">${totalPages}</a></li>`;
    }

    if (current < totalPages) {
        html += `<li><a class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700" href="#" onclick="cambiarPagina(${current + 1}); return false;">Next</a></li>`;
    }
    nav.innerHTML = html;
}

function updatePaginationInfo(start, end, total) {
    document.getElementById('pagination-info').textContent = `Mostrando ${start}-${end} de ${total}`;
}

function cambiarPagina(page) {
    currentPage = page;
    cargarProductos(page, currentFilters);
}

