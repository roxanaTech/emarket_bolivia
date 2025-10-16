<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>eMarket Bolivia - Resultados de Busqueda</title>
    <link rel="icon" type="image/png" href="./img/icon.png">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script defer="" src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 hidden"></div>
    <div class="flex flex-col min-h-screen">
        <main class="container mx-auto px-4 lg:px-8 py-8 flex-grow">
            <div class="flex flex-col lg:flex-row gap-8">
                <div class="lg:hidden mb-4">
                    <button @click="filtersOpen = true"
                        class="w-full flex items-center justify-center gap-2 py-3 px-4 bg-primary text-white rounded-lg font-bold">
                        <span class="material-symbols-outlined">filter_list</span>
                        Filtros y Categorías
                    </button>
                </div>
                <div @click="filtersOpen = false" class="fixed inset-0 bg-black/50 z-40 lg:hidden" x-show="filtersOpen"
                    x-transition:enter="transition-opacity ease-out duration-300" x-transition:enter-end="opacity-100"
                    x-transition:enter-start="opacity-0" x-transition:leave="transition-opacity ease-in duration-200"
                    x-transition:leave-end="opacity-0" x-transition:leave-start="opacity-100"></div>
                <aside
                    x-show="filtersOpen"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="transform -translate-x-full"
                    x-transition:enter-end="transform translate-x-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="transform translate-x-0"
                    x-transition:leave-end="transform -translate-x-full"
                    class="fixed inset-y-0 left-0 z-50 w-80 bg-gray-50 p-6 shadow-xl overflow-y-auto lg:static lg:inset-auto lg:left-auto lg:z-auto lg:w-1/4 xl:w-1/5 lg:shadow-none lg:overflow-visible lg:!block lg:transform-none">
                    <div class="flex justify-between items-center mb-6">
                        <button @click="filtersOpen = false" class="lg:hidden absolute top-4 right-4 text-gray-500 hover:text-gray-700">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                        <h2 class="titulo-aside text-xl font-bold text-primary">Filtros</h2>
                        <button id="clear-filters" class="text-sm text-subtle-light  hover:text-primary">Limpiar Filtros</button>
                    </div>
                    <div class="space-y-6">
                        <div x-data="{ open: true }">
                            <button @click="open = !open" class="flex justify-between items-center w-full font-bold mb-3">
                                <span>Categoría</span>
                                <span :class="{ 'rotate-180': open }" class="material-symbols-outlined transition-transform">expand_more</span>
                            </button>
                            <div class="space-y-2" x-show="open">
                                <label class="flex items-center gap-2 text-sm">
                                    <input class="form-radio text-primary focus:ring-primary/50" name="categoria-filter" type="radio" value="1" data-filter-type="categoria" />
                                    Electrónicos
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input class="form-radio text-primary focus:ring-primary/50" name="categoria-filter" type="radio" value="2" data-filter-type="categoria" />
                                    Hogar y Jardín
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input class="form-radio text-primary focus:ring-primary/50" name="categoria-filter" type="radio" value="3" data-filter-type="categoria" />
                                    Moda y Accesorios
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input class="form-radio text-primary focus:ring-primary/50" name="categoria-filter" type="radio" value="4" data-filter-type="categoria" />
                                    Deportes y Ocio
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input class="form-radio text-primary focus:ring-primary/50" name="categoria-filter" type="radio" value="5" data-filter-type="categoria" />
                                    Automotriz
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input class="form-radio text-primary focus:ring-primary/50" name="categoria-filter" type="radio" value="6" data-filter-type="categoria" />
                                    Libros y Medios
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input class="form-radio text-primary focus:ring-primary/50" name="categoria-filter" type="radio" value="7" data-filter-type="categoria" />
                                    Juguetes y Juegos
                                </label>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-bold mb-3">Marca</h3>
                            <div class="space-y-2" id="brand-filters-container">
                                <!-- Contenido dinámico cargado por JS -->
                            </div>
                        </div>
                        <div>
                            <h4 class="font-semibold mb-2 text-primary">Precio (Bs)</h4>
                            <div class="flex items-center gap-3">
                                <input id="price-min" type="number" placeholder="Min" min="0" step="0.01"
                                    class="w-1/2 p-2 border border-gray-300 rounded-md text-sm focus:ring-primary focus:border-primary">
                                <span class="text-gray-500">-</span>
                                <input id="price-max" type="number" placeholder="Max" min="0" step="0.01"
                                    class="w-1/2 p-2 border border-gray-300 rounded-md text-sm focus:ring-primary focus:border-primary">
                            </div>
                        </div>
                        <div>
                            <h4 class="font-semibold mb-2 text-primary">Valoración Mínima</h4>
                            <div class="flex flex-wrap gap-2">
                                <label class="flex items-center gap-1 cursor-pointer rounded border border-gray-300 dark:border-gray-600 px-3 py-1 text-sm has-[:checked]:bg-primary has-[:checked]:text-white has-[:checked]:border-primary">
                                    <input class="sr-only" name="rating-filter" type="radio" value="4" data-filter-type="rating" />
                                    4+ <span class="text-yellow-400">★</span>
                                </label>
                                <label class="flex items-center gap-1 cursor-pointer rounded border border-gray-300 dark:border-gray-600 px-3 py-1 text-sm has-[:checked]:bg-primary has-[:checked]:text-white has-[:checked]:border-primary">
                                    <input class="sr-only" name="rating-filter" type="radio" value="3" data-filter-type="rating" />
                                    3+ <span class="text-yellow-400">★</span>
                                </label>
                                <label class="flex items-center gap-1 cursor-pointer rounded border border-gray-300 dark:border-gray-600 px-3 py-1 text-sm has-[:checked]:bg-primary has-[:checked]:text-white has-[:checked]:border-primary">
                                    <input class="sr-only" name="rating-filter" type="radio" value="2" data-filter-type="rating" />
                                    2+ <span class="text-yellow-400">★</span>
                                </label>
                                <label class="flex items-center gap-1 cursor-pointer rounded border border-gray-300 dark:border-gray-600 px-3 py-1 text-sm has-[:checked]:bg-primary has-[:checked]:text-white has-[:checked]:border-primary">
                                    <input class="sr-only" name="rating-filter" type="radio" value="1" data-filter-type="rating" />
                                    1+ <span class="text-yellow-400">★</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-bold mb-3">Condición</h3>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 text-sm">
                                    <input class="form-radio text-primary focus:ring-primary/50" name="condition-filter" type="radio" value="nuevo" data-filter-type="condition" />
                                    Nuevo
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input class="form-radio text-primary focus:ring-primary/50" name="condition-filter" type="radio" value="usado" data-filter-type="condition" />
                                    Usado
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input class="form-radio text-primary focus:ring-primary/50" name="condition-filter" type="radio" value="reacondicionado" data-filter-type="condition" />
                                    Reacondicionado
                                </label>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-bold mb-3">Disponibilidad</h3>
                            <label class="flex items-center gap-2 text-sm">
                                <input id="availability-filter" class="form-checkbox rounded text-primary focus:ring-primary/50" type="checkbox" value="en_stock" data-filter-type="availability" />
                                En Stock
                            </label>
                        </div>
                    </div>
                    <div class="mt-8">
                        <button id="apply-filters"
                            @click="filtersOpen = false"
                            class="w-full bg-primary text-white font-bold py-3 rounded-lg hover:bg-primary/90 transition-colors">
                            Aplicar Filtros
                        </button>
                </aside>
                <div class="w-full lg:w-3/4 xl:w-4/5" id="productos-container">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                        <div id="header-container">
                            <h1 class="text-3xl font-bold text-primary" id="products-title">Resultados para "Electronics"</h1>

                        </div>
                    </div>
                    <div class="grid grid-cols-1 space-y-6 mt-6 " id="product-cards-container">

                    </div>
                    <div class="flex justify-center items-center gap-2 mt-8" id="pagination-container">

                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php
    // 2. Incluir el pie de página 
    include 'footer.php';
    ?>
    <script src="./js/global.js"></script>
    <script src="./js/productos-resultados.js"></script>
</body>

</html>