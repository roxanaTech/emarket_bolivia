<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>eMarket Bolivia - Ofertas</title>
    <link rel="icon" type="image/png" href="./img/icon.png">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script defer="" src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="./css/custom.css">
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#02187D",
                        "background-light": "#FFFFFF",
                        "background-dark": "#0D1117",
                        "sidebar-light": "#f2f1f0",
                        "sidebar-dark": "#161B22",
                        "text-light": "#0D1117",
                        "text-dark": "#E6EDF3",
                        "subtle-light": "#6b7280",
                        "subtle-dark": "#8B949E",
                        "accent-red": "#F40009",
                        "accent-green": "#1db954",
                        "accent-yellow": "#FEBD69"
                    },
                    fontFamily: {
                        "display": ["Work Sans", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.5rem",
                        "lg": "0.75rem",
                        "xl": "1rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24
        }

        aside {
            border-right: 1px solid #cacacaff;
        }

        .container-text {
            text-shadow: 1px 1px 15px rgba(0, 0, 0, 0.5);
        }

        @media (max-width: 600px) {
            .titulo-aside {
                margin-top: 14px;
            }
        }
    </style>
</head>

<body :class="{ 'overflow-hidden': filtersOpen }"
    class="bg-gray-50 font-display text-text-light"
    x-data="{ filtersOpen: false }">
    <?php
    include 'navbar.php';
    ?>
    <div class="flex flex-col min-h-screen">
        <header class="relative bg-cover bg-center bg-no-repeat min-h-[55vh] sm:min-h-[60vh] md:min-h-[75vh] flex items-center justify-end pl-8 md:pl-16 pr-4"
            style="background-image: url('./img/Banner-oferta.png');">

            <!-- Overlay para transparencia -->
            <div class="absolute inset-0 bg-gradient-to-r from-transparent to-red-500"></div>

            <div class="container-banner mx-auto relative z-10 text-white max-w-4xl w-full">
                <!-- Texto alineado a la derecha -->
                <div class="container-text max-w-md ml-auto text-right md:text-lg lg:text-xl">
                    <h1 class="text-3xl md:text-4xl font-bold mb-4">Festival de Tecnología Bolivia 2025</h1>
                    <p class="mb-6 leading-relaxed">¡Descuentos en smartphones, accesorios y gadgets hasta el 20 de octubre! Compra hoy y aprovecha los precios más bajos del año.</p>
                </div>

                <!-- Temporizador -->
                <div id="countdown" class="max-w-md ml-auto text-center bg-black/60 rounded-lg px-6 py-4 md:px-8">
                    <div class="flex justify-between items-center mb-2 animate-pulse">
                        <div class="text-center min-w-[3rem]">
                            <span id="days" class="block text-2xl md:text-3xl font-mono">08</span>
                            <span class="text-xs md:text-sm block">Días</span>
                        </div>
                        <span class="text-2xl md:text-3xl font-mono">:</span>
                        <div class="text-center min-w-[3rem]">
                            <span id="hours" class="block text-2xl md:text-3xl font-mono">08</span>
                            <span class="text-xs md:text-sm block">Horas</span>
                        </div>
                        <span class="text-2xl md:text-3xl font-mono">:</span>
                        <div class="text-center min-w-[3rem]">
                            <span id="minutes" class="block text-2xl md:text-3xl font-mono">50</span>
                            <span class="text-xs md:text-sm block">Minutos</span>
                        </div>
                        <span class="text-2xl md:text-3xl font-mono">:</span>
                        <div class="text-center min-w-[3rem]">
                            <span id="seconds" class="block text-2xl md:text-3xl font-mono">56</span>
                            <span class="text-xs md:text-sm block">Segundos</span>
                        </div>
                    </div>
                </div>

                <div class="max-w-md ml-auto text-right mt-6">
                    <a href="#ofertas" class="bg-white hover:bg-orange-600 hover:text-white text-accent-red px-6 py-3 rounded-full font-semibold transition">¡Compra Ahora!</a>
                </div>
            </div>
        </header>
        <main class="container mx-auto px-4 lg:px-8 py-8 flex-grow">
            <div class="flex flex-col">
                <!-- Header con título y contador -->
                <div id="offer-header-container" class="">
                    <h1 id="offer-title" class="text-2xl font-bold text-primary mb-2">Productos en Oferta</h1>
                    <!-- Contador se inyecta aquí por JS" -->
                </div>

                <!-- Filtros horizontales -->
                <div id="offer-filters-container" class="bg-gray/30 py-4 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="font-bold text-secondary dark:text-background-light text-sm" for="category">Categoría</label>
                            <select
                                class="mt-1 block w-full rounded-md border-secondary/20 bg-background-light dark:bg-background-dark/30 dark:border-background-light/20 py-2 pl-3 pr-10 text-secondary dark:text-background-light focus:border-primary focus:outline-none focus:ring-primary/50 text-sm"
                                id="category"
                                aria-label="Seleccionar categoría">
                                <option value="">Todo</option>
                                <option value="1">Electrónicos</option>
                                <option value="2">Hogar y Jardín</option>
                                <option value="3">Moda y Accesorios</option>
                                <option value="4">Deportes y Ocio</option>
                                <option value="5">Automotriz</option>
                                <option value="6">Libros y Medios</option>
                                <option value="7">Juguetes y Juegos</option>
                            </select>
                        </div>
                        <div>
                            <label class="font-bold text-secondary dark:text-background-light text-sm" for="price">Rango de Precios</label>
                            <select
                                class="mt-1 block w-full rounded-md border-secondary/20 bg-background-light dark:bg-background-dark/30 dark:border-background-light/20 py-2 pl-3 pr-10 text-secondary dark:text-background-light focus:border-primary focus:outline-none focus:ring-primary/50 text-sm"
                                id="price"
                                aria-label="Seleccionar rango de precios">
                                <option value="">Todo</option>
                                <option value="1">Bs 0 - Bs 100</option>
                                <option value="2">Bs 100 - Bs 500</option>
                                <option value="3">Bs 500 - Bs 1000</option>
                                <option value="4">Bs 1000+</option>
                            </select>
                        </div>
                        <div>
                            <label class="font-bold text-secondary dark:text-background-light text-sm" for="rating">Clasificación</label>
                            <select
                                class="mt-1 block w-full rounded-md border-secondary/20 bg-background-light dark:bg-background-dark/30 dark:border-background-light/20 py-2 pl-3 pr-10 text-secondary dark:text-background-light focus:border-primary focus:outline-none focus:ring-primary/50 text-sm"
                                id="rating"
                                aria-label="Seleccionar calificación mínima">
                                <option value="">Todos</option>
                                <option value="4">4 estrellas y más</option>
                                <option value="3">3 estrellas y más</option>
                                <option value="2">2 estrellas y más</option>
                            </select>
                        </div>
                    </div>
                    <!-- Botones para aplicar/limpiar -->
                    <div class="flex justify-end gap-2 mt-4">
                        <button
                            id="clear-filters"
                            class="px-4 py-2 text-sm text-secondary border border-secondary/20 rounded-md hover:bg-secondary/10 dark:hover:bg-background-light/10 transition-colors"
                            aria-label="Limpiar todos los filtros">
                            Limpiar
                        </button>
                        <button
                            id="apply-filters"
                            class="px-4 py-2 text-sm bg-primary text-background-light rounded-md hover:bg-primary/90 transition-colors"
                            aria-label="Aplicar filtros seleccionados">
                            Aplicar Filtros
                        </button>
                    </div>
                </div>

                <!-- Contenedor de tarjetas -->
                <div class="flex-1">
                    <div id="offer-cards-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Tarjetas generadas por JS -->
                    </div>
                    <div class="flex justify-center items-center gap-2 mt-8" id="pagination-container">
                        <!-- Paginación generada por JS -->
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php
    // 2. Incluir el pie de página 
    include 'footer.php';
    ?>
    <script>
        // Fecha final: 20 de octubre 2025, fin del día
        const endTime = new Date('2025-10-20T23:59:59');

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = endTime.getTime() - now;

            if (distance < 0) {
                document.getElementById('countdown').innerHTML = '<div class="text-2xl md:text-3xl font-mono text-red-300">¡Festival Terminado!</div><p class="text-sm mt-2">Vuelve en 2026</p>';
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Actualiza con ceros a la izquierda
            document.getElementById('days').textContent = days.toString().padStart(2, '0');
            document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
            document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
            document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
        }

        // Inicia: actualiza cada segundo
        setInterval(updateCountdown, 1000);
        updateCountdown(); // llamar por primera vez
    </script>
    <script src="./js/producto-ofertas.js"></script>
    <script src="./js/global.js"></script>
</body>

</html>