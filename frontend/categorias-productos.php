<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>e-market Bolivia - Productos por Categorías</title>
    <link rel="icon" type="image/png" href="./img/icon.png">
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link rel="stylesheet" href="./css/variables.css">
    <style>
        .range-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 16px;
            height: 16px;
            background: #3874f6ff;
            border-radius: 9999px;
            cursor: pointer;
        }

        .range-slider::-moz-range-thumb {
            width: 16px;
            height: 16px;
            background: #03177c;
            border-radius: 9999px;
            cursor: pointer;
        }

        aside a:hover {
            color: var(--color-azul);
        }

        aside a span {
            color: var(--color-azul);
        }

        aside {
            border-right: 1px solid #e2e2e2ff;
        }
    </style>
</head>

<body class="bg-gray-30 dark:bg-background-dark font-display text-gray-800 dark:text-gray-200">
    <?php
    include 'navbar.php';
    ?>
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 hidden"></div>
    <div class="flex min-h-screen flex-col">
        <header></header>
        <main class="mx-auto w-full max-w-7xl flex-grow p-4 sm:p-6 lg:p-8">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-4">

                <aside class="hidden md:block md:col-span-1">
                    <div class="sticky top-24 space-y-6 rounded-lg bg-gray-40 p-4">
                        <div>
                            <h3 class="text-lg font-bold text-primary mb-3">Categorías</h3>
                            <a class="flex justify-between items-center cursor-pointer hover:bg-primary/10" data-id-categoria="1">
                                <span class="font-semibold px-1 py-2">Electrónica</span>
                            </a>
                            <ul class="space-y-1">
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                        href="#"
                                        data-id-subcategoria="1"
                                        data-categoria-padre="1">Teléfonos Móviles</a></li>
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                        href="#"
                                        data-id-subcategoria="2"
                                        data-categoria-padre="1">Computadoras</a></li>
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                        href="#"
                                        data-id-subcategoria="3"
                                        data-categoria-padre="1">Audio y Video</a></li>
                            </ul>

                            <!-- Hogar y Jardín -->
                            <a class="flex justify-between items-center cursor-pointer hover:bg-primary/10" data-id-categoria="2">
                                <span class="font-semibold px-1 py-2">Hogar y Jardín</span>
                            </a>
                            <ul class="space-y-1">
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                        href="#"
                                        data-id-subcategoria="5"
                                        data-categoria-padre="2">Muebles</a></li>
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10" href="#"
                                        data-id-subcategoria="6"
                                        data-categoria-padre="2">Decoración</a></li>
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10" href="#"
                                        data-id-subcategoria="7"
                                        data-categoria-padre="2">Herramientas</a></li>
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10" href="#"
                                        data-id-subcategoria="8"
                                        data-categoria-padre="2">Electrodomésticos</a></li>
                            </ul>
                            <a class="flex justify-between items-center cursor-pointer hover:bg-primary/10"
                                data-id-categoria="3">
                                <span class="font-semibold px-1 py-2">Moda y Accesorios</span>
                            </a>
                            <ul class="space-y-1">
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                        href="#"
                                        data-id-subcategoria="9"
                                        data-categoria-padre="3">Ropa de Mujer</a></li>
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                        href="#"
                                        data-id-subcategoria="10"
                                        data-categoria-padre="3">Ropa de Hombre</a></li>
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                        href="#"
                                        data-id-subcategoria="11"
                                        data-categoria-padre="3">Calzado</a></li>
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                        href="#"
                                        data-id-subcategoria="12"
                                        data-categoria-padre="3">Joyas y Relojes</a></li>
                            </ul>
                            <a class="flex justify-between items-center cursor-pointer hover:bg-primary/10"
                                data-id-categoria="4">
                                <span class="font-semibold px-1 py-2">Deportes y Ocio</span>
                            </a>
                            <ul class="space-y-1">
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                        href="#"
                                        data-id-subcategoria="13"
                                        data-categoria-padre="4">Artículos Deportivos</a></li>
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                        href="#"
                                        data-id-subcategoria="14"
                                        data-categoria-padre="4">Camping y Senderismo</a></li>
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                        href="#"
                                        data-id-subcategoria="15"
                                        data-categoria-padre="4">Bicicletas</a></li>
                            </ul>
                            <a class="flex justify-between items-center cursor-pointer hover:bg-primary/10"
                                data-id-categoria="5">
                                <span class="font-semibold px-1 py-2">Automotriz</span>
                            </a>
                            <ul class="space-y-1">
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                        href="#"
                                        data-id-subcategoria="16"
                                        data-categoria-padre="5">Vehículos</a></li>
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                        href="#"
                                        data-id-subcategoria="17"
                                        data-categoria-padre="5">Piezas de Repuesto</a></li>
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                        href="#"
                                        data-id-subcategoria="18"
                                        data-categoria-padre="5">Accesorios</a></li>
                            </ul>
                            <a class="flex justify-between items-center cursor-pointer hover:bg-primary/10"
                                data-id-categoria="6">
                                <span class="font-semibold px-1 py-2">Libros y Medios</span>
                            </a>
                            <ul class="space-y-1">
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                        href="#"
                                        data-id-subcategoria="19"
                                        data-categoria-padre="6">Novelas</a></li>
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                        href="#"
                                        data-id-subcategoria="20"
                                        data-categoria-padre="6">Libros de Texto</a></li>
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                        href="#"
                                        data-id-subcategoria="21"
                                        data-categoria-padre="6">Películas</a></li>
                            </ul>
                            <a class="flex justify-between items-center cursor-pointer hover:bg-primary/10"
                                data-id-categoria="7">
                                <span class="font-semibold px-1 py-2">Juguetes y Juegos</span>
                            </a>
                            <ul class="space-y-1">
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                        href="#"
                                        data-id-subcategoria="22"
                                        data-categoria-padre="7">Juguetes</a></li>
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                        href="#"
                                        data-id-subcategoria="23"
                                        data-categoria-padre="7">Juegos de Mesa</a>
                                </li>
                                <li><a class="block rounded px-3 py-2 text-sm font-medium hover:bg-primary/10"
                                        href="#"
                                        data-id-subcategoria="24"
                                        data-categoria-padre="7">Videojuegos</a></li>

                            </ul>
                            <div class="border-t border-gray-300 dark:border-gray-600 pt-6">
                                <h3 class="text-lg font-bold text-primary mb-3">Filtrar por</h3>
                                <div class="space-y-4">

                                    <div>
                                        <h4 class="font-semibold mb-2 text-primary">Marca</h4>
                                        <div class="space-y-2 text-sm" id="brand-filters-container">

                                        </div>
                                    </div>

                                    <div>
                                        <h4 class="font-semibold mb-2 text-primary">Precio (Bs)</h4>
                                        <div class="flex items-center gap-3">
                                            <input id="price-min" type="number" placeholder="Min" min="0"
                                                class="w-1/2 p-2 border border-gray-300 rounded-md text-sm focus:ring-primary focus:border-primary">
                                            <span class="text-gray-500">-</span>
                                            <input id="price-max" type="number" placeholder="Max" min="0"
                                                class="w-1/2 p-2 border border-gray-300 rounded-md text-sm focus:ring-primary focus:border-primary">
                                        </div>
                                    </div>

                                    <div>
                                        <h4 class="font-semibold mb-2 text-primary dark:text-white">Valoración Mínima</h4>
                                        <div class="flex flex-wrap gap-2">
                                            <label
                                                class="flex items-center gap-1 cursor-pointer rounded border border-gray-300 dark:border-gray-600 px-3 py-1 text-sm has-[:checked]:bg-primary has-[:checked]:text-white has-[:checked]:border-primary">
                                                <input class="sr-only" name="rating-filter" type="radio" value="4" data-filter-type="rating" />
                                                4+ <span class="text-yellow-400">★</span>
                                            </label>
                                            <label
                                                class="flex items-center gap-1 cursor-pointer rounded border border-gray-300 dark:border-gray-600 px-3 py-1 text-sm has-[:checked]:bg-primary has-[:checked]:text-white has-[:checked]:border-primary">
                                                <input class="sr-only" name="rating-filter" type="radio" value="3" data-filter-type="rating" />
                                                3+ <span class="text-yellow-400">★</span>
                                            </label>
                                            <label
                                                class="flex items-center gap-1 cursor-pointer rounded border border-gray-300 dark:border-gray-600 px-3 py-1 text-sm has-[:checked]:bg-primary has-[:checked]:text-white has-[:checked]:border-primary">
                                                <input class="sr-only" name="rating-filter" type="radio" value="2" data-filter-type="rating" />
                                                2+ <span class="text-yellow-400">★</span>
                                            </label>
                                            <label
                                                class="flex items-center gap-1 cursor-pointer rounded border border-gray-300 dark:border-gray-600 px-3 py-1 text-sm has-[:checked]:bg-primary has-[:checked]:text-white has-[:checked]:border-primary">
                                                <input class="sr-only" name="rating-filter" type="radio" value="1" data-filter-type="rating" />
                                                1+ <span class="text-yellow-400">★</span>
                                            </label>
                                        </div>
                                    </div>
                                    <!-- NUEVO: Botón Aplicar -->
                                    <div class="pt-4">
                                        <button id="apply-filters" class="w-full bg-primary text-white py-2 px-4 rounded-lg hover:bg-primary/90 transition-colors">
                                            Aplicar Filtros
                                        </button>
                                        <button id="clear-filters" class="w-full mt-2 text-gray-500 py-2 px-4 rounded-lg border border-gray-300 hover:bg-gray-100 transition-colors">
                                            Limpiar Filtros
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
                <!--DESDE AQUI SE DEBE GENERAR LOS PRODUCTOS-->
                <section class="productos col-span-full md:col-span-3" id="productos-container">
                    <div class="mb-2" id="header-container">
                        <h2 id="products-title" class="text-lg sm:text-2xl md:text-4xl font-bold mb-2 text-primary">Todos los Productos</h2>
                    </div>

                    <div class="space-y-6 mt-6" id="product-cards-container">
                    </div>

                    <div class="mt-8 flex justify-center items-center space-x-1" id="pagination-container">
                    </div>
                </section>
            </div>
        </main>
    </div>
    <?php
    // 2. Incluir el pie de página 
    include 'footer.php';
    ?>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    boxShadow: {
                        right: '4px -1px 6px -2px rgba(0, 0, 0, 0.1)'
                    },
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
    <script src="./js/global.js"></script>
    <script src="./js/producto-categoria.js"></script>
</body>

</html>