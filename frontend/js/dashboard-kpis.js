// Función para cargar y renderizar KPIs
async function cargarKPIs() {
    const token = localStorage.getItem('token');
    if (!token) return;

    try {
        const res = await fetch(`${apiUrl}/vendedor/kpis`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const data = await res.json();
        if (data.status === 'success') {
            const kpis = data.data;
            renderKPICards(kpis);
        } else {
            console.error('Error al cargar KPIs:', data.mensaje);
        }
    } catch (error) {
        console.error('Error en fetch KPIs:', error);
    }
}

// Renderiza cards de KPIs (actualiza el contenedor #kpi-cards)
function renderKPICards(kpis) {
    const container = document.getElementById('kpi-cards');
    if (!container) return;

    const kpiTemplates = [
        { key: 'total_productos', label: 'Total Productos', icon: 'sell', color: 'text-primary' },
        { key: 'ordenes_pendientes', label: 'Órdenes Pendientes', icon: 'inventory_2', color: 'text-success' },
        { key: 'ingresos_mes', label: 'Ingresos del Mes', icon: 'trending_up', color: 'text-success', prefix: 'Bs. ' },
        { key: 'productos_bajo_stock', label: 'Productos Bajo Stock', icon: 'warning', color: 'text-danger' },
        { key: 'tasa_conversion', label: 'Tasa de Conversión', icon: 'analytics', color: 'text-primary', suffix: '%' },
        { key: 'ordenes_entregadas_semana', label: 'Órdenes Entregadas (Semana)', icon: 'check_circle', color: 'text-success' }
    ];

    container.innerHTML = kpiTemplates.map(kpi => `
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="material-symbols-outlined ${kpi.color} text-xl">${kpi.icon}</span>
                <span class="text-xs text-gray-500">${kpi.label}</span>
            </div>
            <p class="text-3xl font-black ${kpi.color}">
                ${kpi.prefix || ''}${kpis[kpi.key] || 0}${kpi.suffix || ''}
            </p>
        </div>
    `).join('');
}