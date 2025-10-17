// Instancias de charts globales para destrucción (evita loop)
let ventasChart = null;
let categoriasChart = null;
let estadosChart = null;

// Función para cargar y renderizar gráficos (actualizada)
async function cargarGraficos() {
    const token = localStorage.getItem('token');
    if (!token) return;

    try {
        const res = await fetch(`${apiUrl}/vendedor/graficos`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const data = await res.json();
        if (data.status === 'success') {
            const graficos = data.data;
            renderVentasChart(graficos.ventas_mensuales);
            renderCategoriasChart(graficos.ordenes_por_categoria);
            renderEstadosChart(graficos.distribucion_estados);
        } else {
            console.error('Error al cargar gráficos:', data.mensaje);
        }
    } catch (error) {
        console.error('Error en fetch gráficos:', error);
    }
}

// Gráfico de líneas: Ventas Mensuales (con destrucción)
function renderVentasChart(ventasData) {
    const canvas = document.getElementById('ventas-chart');
    if (!canvas || !ventasData) return;
    const ctx = canvas.getContext('2d');

    // 👈 Destruye chart anterior si existe
    if (ventasChart) {
        ventasChart.destroy();
    }

    ventasChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ventasData.labels,
            datasets: [{
                label: 'Ingresos (Bs.)',
                data: ventasData.data,
                borderColor: '#02187D',
                backgroundColor: 'rgba(2, 24, 125, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { display: false } }
        }
    });
    canvas.style.height = '250px';
}

// Gráfico de barras: Órdenes por Categoría (con destrucción)
function renderCategoriasChart(categoriasData) {
    const canvas = document.getElementById('categorias-chart');
    if (!canvas || !categoriasData) return;
    const ctx = canvas.getContext('2d');

    if (categoriasChart) {
        categoriasChart.destroy();
    }

    categoriasChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: categoriasData.labels,
            datasets: [{
                label: 'Órdenes',
                data: categoriasData.data,
                backgroundColor: '#02187D',
                borderColor: '#02187D',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { display: false } }
        }
    });
    canvas.style.height = '250px';
}

// Gráfico Doughnut: Distribución de Estados (con destrucción)
function renderEstadosChart(estadosData) {
    const canvas = document.getElementById('estados-chart');
    if (!canvas || !estadosData) return;
    const ctx = canvas.getContext('2d');

    if (estadosChart) {
        estadosChart.destroy();
    }

    const colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];

    estadosChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: estadosData.labels,
            datasets: [{
                data: estadosData.data,
                backgroundColor: colors.slice(0, estadosData.labels.length),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });
    canvas.style.height = '250px';
}