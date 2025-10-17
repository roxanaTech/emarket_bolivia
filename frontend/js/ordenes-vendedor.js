
currentFilters = { search: '', estado: '' };
totalOrdenes = 0;

// Función para cargar órdenes
async function cargarOrdenes(page = 1, filters = {}) {
    const token = localStorage.getItem('token');
    if (!token) return;

    const params = new URLSearchParams({ pagina: page, por_pagina: 10, ...filters });
    try {
        const res = await fetch(`${apiUrl}/ventas/vendedor?${params}`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const data = await res.json();
        if (data.status === 'success') {
            totalOrdenes = data.data.total || 0;  // Asume estructura con total
            renderOrdenes(data.data.ventas || data.data);  // Si es array directo, usa data.data
            renderPaginacion(data.data.pagina || page, data.data.total_paginas || 1);
            updatePaginationInfo(10 * (page - 1) + 1, Math.min(10 * page, totalOrdenes), totalOrdenes);
        } else {
            console.error('Error al cargar órdenes:', data.mensaje);
        }
    } catch (error) {
        console.error('Error en fetch órdenes:', error);
    }
}

// Renderiza tabla de órdenes
function renderOrdenes(ordenes) {
    const tbody = document.getElementById('ordenes-tbody');
    if (!tbody) return;

    tbody.innerHTML = ordenes.length > 0
        ? ordenes.map(orden => {
            const estadoClass = {
                'pendiente': 'bg-yellow-100 text-yellow-800',
                'enviada': 'bg-blue-100 text-blue-800',
                'entregada': 'bg-green-100 text-green-800',
                'cancelada': 'bg-red-100 text-red-800'
            }[orden.estado] || 'bg-gray-100 text-gray-800';

            return `
        <tr class="bg-white dark:bg-gray-900 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
          <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">${orden.id_venta}</td>
          <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
            ${new Date(orden.fecha).toLocaleDateString('es-BO', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            })}
          </td>
          <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">Bs. ${parseFloat(orden.total_venta).toFixed(2)}</td>
          <td class="px-6 py-4">
            <span class="px-2 py-1 ${estadoClass} text-xs rounded-full">
              ${orden.estado.charAt(0).toUpperCase() + orden.estado.slice(1)}
            </span>
          </td>
          <td class="px-6 py-4 text-gray-600 dark:text-gray-400">${orden.tipo_pago.charAt(0).toUpperCase() + orden.tipo_pago.slice(1)}</td>
          <td class="px-6 py-4 text-gray-600 dark:text-gray-400">${orden.tipo_entrega}</td>
          <td class="px-6 py-4 text-gray-600 truncate max-w-[200px]">${orden.nombre_comprador}</td>
          <td class="px-6 py-4 text-gray-600 truncate max-w-[200px]">${orden.email_comprador}</td>
          <td class="px-6 py-4 text-gray-500 dark:text-gray-400 text-sm">N/A</td>
        </tr>
      `;
        }).join('')
        : `
    <tr>
      <td colspan="9" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
        No hay órdenes.
      </td>
    </tr>
  `;

}
// Renderiza paginación (igual que productos)
function renderPaginacion(current, totalPages) {
    const nav = document.getElementById('pagination-nav');
    if (!nav) return;

    let html = '';
    if (current > 1) {
        html += `< li > <a class="flex items-center justify-center px-3 h-8 ml-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 hover:text-gray-700" href="#" onclick="cambiarPaginaOrdenes(${current - 1}); return false;">Anterior</a></li > `;
    }

    const startPage = Math.max(1, current - 2);
    const endPage = Math.min(totalPages, current + 2);

    if (startPage > 1) html += `< li > <a class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700" href="#" onclick="cambiarPaginaOrdenes(1); return false;">1</a></li > `;
    if (startPage > 2) html += `< li > <span class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300">...</span></li > `;

    for (let i = startPage; i <= endPage; i++) {
        html += `< li > <a class="flex items-center justify-center px-3 h-8 ${i === current ? 'text-white border border-primary bg-primary' : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700'} leading-tight" href="#" onclick="cambiarPaginaOrdenes(${i}); return false;">${i}</a></li > `;
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += `< li > <span class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300">...</span></li > `;
        html += `< li > <a class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700" href="#" onclick="cambiarPaginaOrdenes(${totalPages}); return false;">${totalPages}</a></li > `;
    }

    if (current < totalPages) {
        html += `< li > <a class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 hover:text-gray-700" href="#" onclick="cambiarPaginaOrdenes(${current + 1}); return false;">Siguiente</a></li > `;
    }
    nav.innerHTML = html;
}

function updatePaginationInfo(start, end, total) {
    document.getElementById('pagination-info').textContent = `Mostrando ${start} -${end} de ${total} órdenes`;
}

function cambiarPaginaOrdenes(page) {
    currentPage = page;
    cargarOrdenes(page, currentFilters);
}

// Listeners para filtros
document.addEventListener('DOMContentLoaded', () => {
    // Search
    document.getElementById('search-ordenes').addEventListener('input', (e) => {
        currentFilters.search = e.target.value;
        cargarOrdenes(1, currentFilters);
    });

    // Estado filter
    document.getElementById('estado-filter').addEventListener('change', (e) => {
        currentFilters.estado = e.target.value;
        cargarOrdenes(1, currentFilters);
    });

    // Limpiar filtros
    document.getElementById('clear-filters').addEventListener('click', () => {
        currentFilters = { search: '', estado: '' };
        document.getElementById('search-ordenes').value = '';
        document.getElementById('estado-filter').value = '';
        cargarOrdenes(1, currentFilters);
    });

    // Carga inicial
    cargarOrdenes(1, currentFilters);
});