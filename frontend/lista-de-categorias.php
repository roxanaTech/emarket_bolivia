<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>e-market Bolivia- Categorías</title>
    <link rel="icon" type="image/png" href="./img/icon.png">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;700;900&amp;display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#03177c",
                        "background-light": "#f5f6f8",
                        "background-dark": "#0f1223",
                    },
                    fontFamily: {
                        "display": ["Work Sans"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                },
            },
        };
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24;
        }
    </style>
</head>

<body class="bg-background-light font-display text-gray-800">
    <?php
    include 'navbar.php';  // Asume que aquí se carga categories-shared.js una sola vez
    ?>
    <div class="relative flex min-h-screen w-full flex-col overflow-x-hidden">

        <main class="flex-1">
            <div class="container mx-auto px-4 py-8 sm:px-6 lg:px-8">
                <div class="max-w-7xl mx-auto">
                    <h2 class="text-3xl font-bold text-primary mb-4">Explora Todas las Categorías</h2>

                    <section class="mb-12">
                        <h3 class="text-2xl font-bold text-gray-800 mb-6">Categorías Destacadas</h3>
                        <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4">
                            <a class="group block" href="categorias-productos.php?id_categoria=1"> <!-- Enlace a página de productos -->
                                <div class="aspect-square w-full overflow-hidden rounded-lg">
                                    <div class="h-full w-full bg-cover bg-center transition-transform duration-300 group-hover:scale-105"
                                        style='background-image: url("./img/electronicos-g.png");'>
                                    </div>
                                </div>
                                <p class="mt-3 text-base font-semibold text-primary">Electrónica</p>
                            </a>
                            <a class="group block" href="categorias-productos.php?id_categoria=3"> <!-- Ajusta IDs según tu mapa -->
                                <div class="aspect-square w-full overflow-hidden rounded-lg">
                                    <div class="h-full w-full bg-cover bg-center transition-transform duration-300 group-hover:scale-105"
                                        style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuDcQB5PxjFVCtrhRLMW3vMKiIxLu1oRgb_Y3gmd4h5t4SgIVWcuWht9xryVBD6VQPVe9lVN24KvbLFqn2t675B-4zBWG_ICqilI0WcXz-3c6jqbwuNImfHjL-lnUcDk9janMZY9AcQUVQ6txpwaCsEeqQJuM2F2KtJcSTsfOafNdR76NSuuhZs4n01Ojd1btb62ClDjdy7ugz6g0woO_QppUkjaiea0xj4M-4XxeInucoae2lr2_u_LX1JlcelSMNn3KetVWPhciR0");'>
                                    </div>
                                </div>
                                <p class="mt-3 text-base font-semibold text-primary">Moda y Accesorios</p>
                            </a>
                            <a class="group block" href="categorias-productos.php?id_categoria=2">
                                <div class="aspect-square w-full overflow-hidden rounded-lg">
                                    <div class="h-full w-full bg-cover bg-center transition-transform duration-300 group-hover:scale-105"
                                        style='background-image: url("./img/hogar-g.png");'>
                                    </div>
                                </div>
                                <p class="mt-3 text-base font-semibold text-primary">Hogar y Jardín</p>
                            </a>
                            <a class="group block" href="categorias-productos.php?id_categoria=4">
                                <div class="aspect-square w-full overflow-hidden rounded-lg">
                                    <div class="h-full w-full bg-cover bg-center transition-transform duration-300 group-hover:scale-105"
                                        style='background-image: url("./img/deporte-g.png");'>
                                    </div>
                                </div>
                                <p class="mt-3 text-base font-semibold text-primary">Deportes y Ocio</p>
                            </a>
                        </div>
                    </section>
                    <section>
                        <h3 class="text-2xl font-bold text-gray-800 mb-6">Todas las Categorías</h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            <a class="flex items-center gap-4 rounded-lg border border-gray-200 bg-white p-4 transition-all hover:border-primary hover:shadow-lg"
                                href="categorias-productos.php?id_categoria=1">
                                <span class="material-symbols-outlined text-primary text-3xl">tv</span>
                                <span class="font-semibold text-gray-700">Electrónica</span>
                            </a>
                            <a class="flex items-center gap-4 rounded-lg border border-gray-200 bg-white p-4 transition-all hover:border-primary hover:shadow-lg"
                                href="categorias-productos.php?id_categoria=3">
                                <span class="material-symbols-outlined text-red-500 text-3xl">checkroom</span>
                                <span class="font-semibold text-gray-700">Moda y Accesorios</span>
                            </a>
                            <a class="flex items-center gap-4 rounded-lg border border-gray-200 bg-white p-4 transition-all hover:border-primary hover:shadow-lg"
                                href="categorias-productos.php?id_categoria=2">
                                <span class="material-symbols-outlined text-yellow-500 text-3xl">deck</span>
                                <span class="font-semibold text-gray-700">Hogar y Jardín</span>
                            </a>
                            <a class="flex items-center gap-4 rounded-lg border border-gray-200 bg-white p-4 transition-all hover:border-primary hover:shadow-lg"
                                href="categorias-productos.php?id_categoria=4">
                                <span class="material-symbols-outlined text-green-500 text-3xl">sports_basketball</span>
                                <span class="font-semibold text-gray-700">Deportes y Ocio</span>
                            </a>
                            <a class="flex items-center gap-4 rounded-lg border border-gray-200 bg-white p-4 transition-all hover:border-primary hover:shadow-lg"
                                href="categorias-productos.php?id_categoria=7">
                                <span class="material-symbols-outlined text-yellow-500 text-3xl">extension</span>
                                <span class="font-semibold text-gray-700">Juguetes y Juegos</span>
                            </a>
                            <a class="flex items-center gap-4 rounded-lg border border-gray-200 bg-white p-4 transition-all hover:border-primary hover:shadow-lg"
                                href="categorias-productos.php?id_categoria=6">
                                <span class="material-symbols-outlined text-green-500 text-3xl">book_2</span>
                                <span class="font-semibold text-gray-700">Libros y Medios</span>
                            </a>
                            <a class="flex items-center gap-4 rounded-lg border border-gray-200 bg-white p-4 transition-all hover:border-primary hover:shadow-lg"
                                href="categorias-productos.php?id_categoria=5">
                                <span class="material-symbols-outlined text-primary text-3xl">directions_car</span>
                                <span class="font-semibold text-gray-700">Automotriz</span>
                            </a>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
    <?php
    include 'footer.php';
    ?>
    <!-- NO agregues el script aquí; ya está en navbar.php -->
</body>

</html>