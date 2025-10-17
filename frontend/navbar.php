<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>emarket Bolivia</title>
    <link rel="icon" type="image/png" href="./img/icon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/variables.css">
    <link rel="stylesheet" href="./css/estilos.css">
    <link rel="stylesheet" href="./css/custom.css">
    <!-- Alpine.js para el dropdown -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Google Material Icons para los spans -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<body>
    <!-- Toast container-->
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 hidden"></div>
    <!-- Top Nav (azul oscuro) -->
    <nav class="bg-blue-900 text-white py-3 sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center px-4">
            <!-- Sección izquierda del nav (agregamos ID para manipular con JS) -->
            <div id="nav-left" class="flex items-center gap-6">
                <button id="sidebar-toggle" class=" text-white text-2xl">
                    <i class="bi bi-list"></i>
                </button>
                <a href="principal.php" class="font-medium text-sm md:text-base hover:text-blue-300">Inicio</a>
                <a href="productos-ofertas.php" class="font-medium text-sm md:text-base hover:text-blue-300">Ofertas</a>
                <a href="lista-de-categorias.php" class="hidden md:inline font-medium text-sm md:text-base hover:text-blue-300">Categorías</a>
                <!-- Link "Empieza a vender" (se condicionarará con JS) 
                <a id="start-selling-link" href="#" class="font-bold text-sm md:text-base hover:text-blue-300">Empieza a vender</a>-->
                <a href="ayuda.php" class="md:block font-medium text-sm md:text-base hover:text-blue-300">Ayuda</a>
            </div>
            <!-- Contenedor de autenticación (se modifica dinámicamente con JS) -->
            <div id="auth-container">
                <!-- Placeholder inicial: se reemplaza con renderAuthContainer() -->
                <a href="login.php"
                    class="inline-block px-5 py-2 rounded-full bg-primary text-white text-sm font-semibold shadow-md hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all duration-200">
                    <i class="bi bi-person-circle mr-2 text-base"></i> Iniciar Sesión
                </a>

            </div>
        </div>
    </nav>
    <!-- Header principal (sticky) -->
    <header class="sticky top-12 z-40 bg-white shadow-sm">
        <div class="container mx-auto py-3 px-4">
            <div class="flex flex-row items-center gap-2 md:flex-row md:gap-3">

                <div class="shrink-0">
                    <img src="./img/logoh.png" alt="Logo emarket-bolivia" class="hidden md:block h-10 object-contain">
                    <img src="./img/icon.png" alt="Logo emarket-bolivia" class="md:hidden h-6 w-6 object-contain">
                </div>

                <div class="flex-grow flex-shrink">
                    <div class="flex">
                        <input type="text" id="search-input" placeholder="¿Qué estás buscando?" maxlength="100"
                            class="flex-grow px-3 py-1 text-sm border border-gray-300 rounded-l-md outline-none focus:outline-none focus:border-gray-300 focus:ring-2 focus:ring-yellow-500 text-black">

                        <button type="button" id="search-btn"
                            class="bg-yellow-500 hover:bg-yellow-600 text-black/70 px-3 py-1.5 rounded-r-md font-medium flex items-center gap-1 md:gap-2 whitespace-nowrap">
                            <i class="bi bi-search text-sm md:text-base"></i>
                            <span class="hidden md:inline">Buscar</span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap text-sm md:gap-3 md:flex-nowrap">
                    <div class="flex items-center gap-1 text-black">
                        <span class="hidden sm:inline md:inline">Entregas en:</span>
                        <img src="https://s.alicdn.com/@icon/flag/assets/bo.png" alt="BO" width="20" class="hidden sm:inline md:inline">
                        <span class="hidden sm:inline md:inline">BO</span>
                    </div>
                    <div class="hidden sm:block md:block text-black">Español - BOB</div>

                    <div class="relative">
                        <i class="bi bi-bell text-base text-black cursor-pointer md:text-lg"></i>
                        <span class="absolute top-0 right-0 translate-x-1/2 -translate-y-1/2 bg-red-600 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">0</span>
                    </div>

                    <div class="relative"> <!-- Carrito -->
                        <a href="carrito.php"><i class="bi bi-cart3 text-base text-black cursor-pointer md:text-lg"></i></a>
                        <span id="badge-carrito" class="absolute top-0 right-0 translate-x-1/2 -translate-y-1/2 bg-red-600 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">0</span>
                    </div>
                </div>

            </div>
        </div>
    </header>
    <main>
        <div id="sidebar-overlay"
            class="fixed inset-0 bg-black bg-opacity-50 z-50  hidden transition-opacity duration-300"></div>
        <aside id="sidebar-categorias"
            class="fixed top-0 left-0 w-72 bg-white h-screen overflow-y-auto shadow-lg z-50 transform -translate-x-full transition-transform duration-300">

            <div class="space-y-2 rounded-lg bg-gray-50">
                <div class="sticky top-0 z-50 flex items-center justify-between py-2 px-4 bg-blue-900 text-white"> <!-- Ajusté clase para consistencia -->
                    <h3 class="text-lg font-bold">Categorías</h3>
                    <button id="sidebar-close" class="text-white text-xl hover:text-red-300">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="px-4 pb-4">
                    <a class="flex justify-between items-center cursor-pointer hover:bg-primary/10" data-id-categoria="1">
                        <span class="font-semibold px-1 py-2">Electrónica</span>
                    </a>
                    <ul class="space-y-1">
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                href="javascript:void(0);"
                                data-id-subcategoria="1"
                                data-categoria-padre="1">Teléfonos Móviles</a></li>
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                href="javascript:void(0);"
                                data-id-subcategoria="2"
                                data-categoria-padre="1">Computadoras</a></li>
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                href="javascript:void(0);"
                                data-id-subcategoria="3"
                                data-categoria-padre="1">Audio y Video</a></li>
                    </ul>

                    <!-- Hogar y Jardín -->
                    <a class="flex justify-between items-center cursor-pointer hover:bg-primary/10" data-id-categoria="2">
                        <span class="font-semibold px-1 py-2">Hogar y Jardín</span>
                    </a>
                    <ul class="space-y-1">
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                href="javascript:void(0);"
                                data-id-subcategoria="5"
                                data-categoria-padre="2">Muebles</a></li>
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10" href="javascript:void(0);"
                                data-id-subcategoria="6"
                                data-categoria-padre="2">Decoración</a></li>
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10" href="javascript:void(0);"
                                data-id-subcategoria="7"
                                data-categoria-padre="2">Herramientas</a></li>
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10" href="javascript:void(0);"
                                data-id-subcategoria="8"
                                data-categoria-padre="2">Electrodomésticos</a></li>
                    </ul>
                    <a class="flex justify-between items-center cursor-pointer hover:bg-primary/10"
                        data-id-categoria="3">
                        <span class="font-semibold px-1 py-2">Moda y Accesorios</span>
                    </a>
                    <ul class="space-y-1">
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                href="javascript:void(0);"
                                data-id-subcategoria="9"
                                data-categoria-padre="3">Ropa de Mujer</a></li>
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                href="javascript:void(0);"
                                data-id-subcategoria="10"
                                data-categoria-padre="3">Ropa de Hombre</a></li>
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                href="javascript:void(0);"
                                data-id-subcategoria="11"
                                data-categoria-padre="3">Calzado</a></li>
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                href="javascript:void(0);"
                                data-id-subcategoria="12"
                                data-categoria-padre="3">Joyas y Relojes</a></li>
                    </ul>
                    <a class="flex justify-between items-center cursor-pointer hover:bg-primary/10"
                        data-id-categoria="4">
                        <span class="font-semibold px-1 py-2">Deportes y Ocio</span>
                    </a>
                    <ul class="space-y-1">
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                href="javascript:void(0);"
                                data-id-subcategoria="13"
                                data-categoria-padre="4">Artículos Deportivos</a></li>
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                href="javascript:void(0);"
                                data-id-subcategoria="14"
                                data-categoria-padre="4">Camping y Senderismo</a></li>
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                href="javascript:void(0);"
                                data-id-subcategoria="15"
                                data-categoria-padre="4">Bicicletas</a></li>
                    </ul>
                    <a class="flex justify-between items-center cursor-pointer hover:bg-primary/10"
                        data-id-categoria="5">
                        <span class="font-semibold px-1 py-2">Automotriz</span>
                    </a>
                    <ul class="space-y-1">
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                href="javascript:void(0);"
                                data-id-subcategoria="16"
                                data-categoria-padre="5">Vehículos</a></li>
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                href="javascript:void(0);"
                                data-id-subcategoria="17"
                                data-categoria-padre="5">Piezas de Repuesto</a></li>
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                href="javascript:void(0);"
                                data-id-subcategoria="18"
                                data-categoria-padre="5">Accesorios</a></li>
                    </ul>
                    <a class="flex justify-between items-center cursor-pointer hover:bg-primary/10"
                        data-id-categoria="6">
                        <span class="font-semibold px-1 py-2">Libros y Medios</span>
                    </a>
                    <ul class="space-y-1">
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                href="javascript:void(0);"
                                data-id-subcategoria="19"
                                data-categoria-padre="6">Novelas</a></li>
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                href="javascript:void(0);"
                                data-id-subcategoria="20"
                                data-categoria-padre="6">Libros de Texto</a></li>
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                href="javascript:void(0);"
                                data-id-subcategoria="21"
                                data-categoria-padre="6">Películas</a></li>
                    </ul>
                    <a class="flex justify-between items-center cursor-pointer hover:bg-primary/10"
                        data-id-categoria="7">
                        <span class="font-semibold px-1 py-2">Juguetes y Juegos</span>
                    </a>
                    <ul class="space-y-1">
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                href="javascript:void(0);"
                                data-id-subcategoria="22"
                                data-categoria-padre="7">Juguetes</a></li>
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                href="javascript:void(0);"
                                data-id-subcategoria="23"
                                data-categoria-padre="7">Juegos de Mesa</a>
                        </li>
                        <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                href="javascript:void(0);"
                                data-id-subcategoria="24"
                                data-categoria-padre="7">Videojuegos</a></li>

                    </ul>
                </div>
        </aside>
        </div>
    </main>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {

                        "background-light": "#f5f6f8",
                        "background-dark": "#0f1223",
                        "primary": "#02187D",
                        "surface-light": "#ffffff",
                        "surface-dark": "#1a202c",
                        "text-light": "#02187D",
                        "text-dark": "#ffffff",
                        "subtle-light": "#6b7280",
                        "subtle-dark": "#9ca3af",
                        "offer-light": "#F40009",
                        "offer-dark": "#F40009",
                        "shipping-light": "#1db954",
                        "shipping-dark": "#1db954",
                        "rating-light": "#FEBD69",
                        "rating-dark": "#FEBD69",
                    },
                    fontFamily: {
                        "display": ["Work Sans", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <!-- Scripts inline para toggle (igual que en tu código) -->
    <script>
        const toggleBtn = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar-categorias');
        const overlay = document.getElementById('sidebar-overlay');
        const closeBtn = document.getElementById('sidebar-close');

        if (toggleBtn && sidebar && overlay && closeBtn) { // Verificación para evitar errores si no existe
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            });

            overlay.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            });

            closeBtn.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            });
        }
    </script>

    <script>
        const nav = document.querySelector('nav');
        const header = document.querySelector('header');
        const observer = new IntersectionObserver(
            ([entry]) => {
                if (!entry.isIntersecting) {
                    nav.classList.add('py-3');
                } else {
                    nav.classList.remove('py-2');
                }
            }, {
                threshold: 0
            }
        );

        observer.observe(header);
    </script>
    <script src="./js/categories-shared.js"></script>
    <script src="./js/navbar.js"></script>
    <script src="./js/global.js"></script>
</body>

</html>