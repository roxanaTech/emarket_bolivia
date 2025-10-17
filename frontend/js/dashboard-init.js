// Función para toggle menú móvil
function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileSidebar = document.getElementById('mobile-sidebar');
    mobileMenu.classList.toggle('hidden');
    if (!mobileMenu.classList.contains('hidden')) {
        mobileSidebar.classList.remove('-translate-x-full');
    }
}

// Función para cerrar menú móvil
function closeMobileMenu(event) {
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileSidebar = document.getElementById('mobile-sidebar');
    if (event && event.target === mobileMenu) {
        mobileSidebar.classList.add('-translate-x-full');
        mobileMenu.classList.add('hidden');
    }
}

// Función para cargar sección en main
function loadSection(section) {
    const sections = document.querySelectorAll('.section-content');
    sections.forEach(s => s.classList.add('hidden'));

    const targetSection = document.getElementById(section + '-section');
    if (targetSection) {
        targetSection.classList.remove('hidden');
    }

    const links = document.querySelectorAll('nav a');
    links.forEach(link => link.classList.remove('bg-primary', 'text-white'));
    event?.target.closest('a').classList.add('bg-primary', 'text-white');

    if (window.innerWidth < 1024) {
        closeMobileMenu();
    }

    // Si es dashboard, recarga KPIs y gráficos
    if (section === 'dashboard') {
        cargarKPIs();
        cargarGraficos();
    }
    if (section === 'productos') {
        cargarProductos(1); 
         // Carga inicial
    }
}

// Carga inicial: Verifica rol, carga perfil y dashboard
async function initDashboard() {
    const user = await cargarPerfil();
    window.currentUser = user;
    if (!user) {
        window.location.href = 'login.php';
        return;
    }

    if (user.rol !== 'vendedor') {
        alert('Acceso denegado: Este panel es solo para vendedores.');
        window.location.href = 'principal.php';
        return;
    }

    document.getElementById('user-name').textContent = user.nombres?.split(' ')[0] || 'Usuario';
    const imgUrl = user.imagen || 'https://lh3.googleusercontent.com/aida-public/...';
    document.getElementById('profile-img').style.backgroundImage = `url(${imgUrl})`;

    loadSection('dashboard'); // Carga dashboard por defecto (llama a KPIs y gráficos)
    

}
window.addEventListener('resize', () => { if (ventasChart) ventasChart.resize(); });
// Inicializa al cargar
document.addEventListener('DOMContentLoaded', initDashboard);
