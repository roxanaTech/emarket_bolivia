<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>e-market Bolivia - Carrito de Compras</title>
    <link rel="icon" type="image/png" href="./img/icon.png">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#03177c",
                        "background-light": "#f5f6f8",
                        "background-dark": "#0f1223",
                    },
                    fontFamily: {
                        display: ["Work Sans"],
                    },
                    borderRadius: {
                        DEFAULT: "0.25rem",
                        lg: "0.5rem",
                        xl: "0.75rem",
                        full: "9999px"
                    },
                },
            },
        };
    </script>
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-gray-800 dark:text-gray-200">
    <?php
    include 'navbar.php';
    ?>
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 hidden"></div>
    <div class="flex flex-col min-h-screen">

        <main class="container mx-auto px-4 sm:px-6 lg:px-8 py-12 flex-grow">
            <h1 class="text-3xl sm:text-4xl font-bold text-primary mb-8">Carrito de Compras</h1>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12">
                <div class="lg:col-span-2 space-y-6">
                    <!-- Contenedor dinámico para items -->
                    <div id="contenedor-items-carrito">
                        <!-- Los items se generarán aquí con JS -->
                    </div>

                </div>
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-background-dark/50 p-6 rounded-lg shadow-sm space-y-6">
                        <h2 class="text-2xl font-bold text-primary">Resumen del Pedido</h2>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span>Subtotal</span>
                                <span id="subtotal-resumen" class="font-medium">Bs 0.00</span>
                            </div>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700"></div>
                        <div class="flex justify-between items-center font-bold text-xl">
                            <span>Total a Pagar</span>
                            <span id="total-pagar" class="text-primary">Bs 0.00</span>
                        </div>
                        <button class="w-full bg-primary text-white font-bold py-3 rounded-lg hover:bg-primary/90 transition-colors text-lg"
                            onclick="window.location.href='pago.php'" id="btn-pagar">
                            Proceder al Pago
                        </button>
                    </div>
                </div>
                <div class="pt-6">
                    <a href="principal.php" class="text-primary font-semibold hover:underline">
                        ← Continuar Comprando
                    </a>
                </div>
            </div>
        </main>
    </div>
    <?php
    // 2. Incluir el pie de página 
    include 'footer.php';
    ?>
    <script src="./js/global.js"></script>
    <script src="./js/carrito.js"></script>
</body>

</html>