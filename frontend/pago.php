<!DOCTYPE html>
<html class="light" lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>e-market Bolivia - Pago</title>
    <link rel="icon" type="image/png" href="./img/icon.png">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700;800;900&amp;display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#02187D",
                        "background-light": "#FFFFFF",
                        "background-dark": "#0f1323",
                        "success": "#1db954",
                        "error": "#F40009"
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
    <style>
        .material-symbols-outlined {
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24
        }
    </style>
</head>

<body class="min-h-screen bg-background-light dark:bg-background-dark font-display text-gray-800 dark:text-gray-200">
    <?php
    include 'navbar.php';
    ?>
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 hidden"></div>
    <div class="w-full max-w-full lg:max-w-[90vw] container mx-auto px-4 py-8">
        <div class="mb-12">
            <div class="flex items-center justify-center">
                <!-- Paso 1 -->
                <div class="flex items-center text-gray-500 relative" id="step1">
                    <div class="rounded-full transition duration-500 ease-in-out h-10 w-10 flex items-center justify-center bg-primary text-white">
                        1
                    </div>
                    <div class="absolute top-0 -ml-10 text-center mt-12 w-32 text-xs font-medium uppercase text-primary">
                        Datos Comunes
                    </div>
                </div>
                <div class="flex-auto border-t-2 transition duration-500 ease-in-out border-primary" id="line1-2"></div>

                <!-- Paso 2 -->
                <div class="flex items-center text-white relative" id="step2">
                    <div class="rounded-full transition duration-500 ease-in-out h-10 w-10 flex items-center justify-center bg-success">
                        2
                    </div>
                    <div class="absolute top-0 -ml-10 text-center mt-12 w-32 text-xs font-medium uppercase text-success">
                        Pago
                    </div>
                </div>
                <div class="flex-auto border-t-2 transition duration-500 ease-in-out border-gray-300" id="line2-3"></div>

                <!-- Paso 3 -->
                <div class="flex items-center text-gray-500 relative" id="step3">
                    <div class="rounded-full transition duration-500 ease-in-out h-10 w-10 flex items-center justify-center border-2 border-gray-300">
                        3
                    </div>
                    <div class="absolute top-0 -ml-10 text-center mt-12 w-32 text-xs font-medium uppercase text-gray-500">
                        Confirmación
                    </div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <div class="flex flex-col gap-8">
                <div>
                    <p class="text-primary text-2xl font-bold mb-6">Datos de Contacto y Envío</p>
                    <div class="space-y-6">
                        <label class="flex flex-col">
                            <p class="text-gray-700 dark:text-gray-300 text-base font-medium leading-normal pb-2">
                                Teléfono de Contacto</p>
                            <input id="telefono-contacto"
                                class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-gray-800 dark:text-gray-200 focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-gray-300 dark:border-gray-600 bg-background-light dark:bg-background-dark focus:border-primary h-14 placeholder:text-gray-400 dark:placeholder:text-gray-500 p-[15px] text-base font-normal leading-normal"
                                placeholder="+591 7XXXXXXXX" value="" required />
                        </label>
                        <div>
                            <p class="text-gray-700 dark:text-gray-300 text-base font-medium leading-normal pb-2">Tipo de Entrega</p>
                            <div class="flex h-12 flex-1 items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700/50 p-1">
                                <label id="radio-domicilio" class="flex cursor-pointer h-full grow items-center justify-center overflow-hidden rounded-lg px-2 has-[:checked]:bg-background-light has-[:checked]:dark:bg-background-dark has-[:checked]:shadow-md has-[:checked]:text-primary text-gray-600 dark:text-gray-400 text-sm font-medium leading-normal transition-all duration-300">
                                    <span class="truncate">A Domicilio</span>
                                    <input checked="" class="invisible w-0" name="deliveryType" type="radio" value="A Domicilio" />
                                </label>
                                <label id="radio-recogida" class="flex cursor-pointer h-full grow items-center justify-center overflow-hidden rounded-lg px-2 has-[:checked]:bg-background-light has-[:checked]:dark:bg-background-dark has-[:checked]:shadow-md has-[:checked]:text-primary text-gray-600 dark:text-gray-400 text-sm font-medium leading-normal transition-all duration-300">
                                    <span class="truncate">Recogido en Tienda/Local</span>
                                    <input class="invisible w-0" name="deliveryType" type="radio" value="Recogido en Tienda/Local" />
                                </label>
                            </div>

                            <!-- Info de recogida en tienda (inicialmente oculto) -->
                            <div id="pickupInfo" class="mt-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg hidden">
                                <p class="text-gray-700 dark:text-gray-300 text-sm">Puedes recoger tu pedido en nuestra sucursal principal en Av. Arce #2519, La Paz. Horario de atención: Lunes a Viernes de 9:00 a 18:00.</p>
                            </div>

                            <!-- Input de dirección -->
                            <label class="flex flex-col" id="addressLabel">
                                <p class="text-gray-700 dark:text-gray-300 text-base font-medium leading-normal pb-2">Dirección de Entrega Completa</p>
                                <textarea id="direccion-entrega" class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-gray-800 dark:text-gray-200 focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-gray-300 dark:border-gray-600 bg-background-light dark:bg-background-dark focus:border-primary min-h-36 placeholder:text-gray-400 dark:placeholder:text-gray-500 p-[15px] text-base font-normal leading-normal" placeholder="Escriba su dirección completa" required></textarea>
                            </label>
                        </div>

                        <div
                            class="bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300 p-4 rounded-lg text-sm">
                            <p>La dirección y teléfono proporcionados serán usados para todos los pedidos de esta
                                compra.</p>
                        </div>
                    </div>
                </div>
                <div>
                    <p class="text-primary text-2xl font-bold mb-6">Selecciona un Método de Pago</p>
                    <div class="space-y-6">
                        <div class="flex h-12 flex-1 items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700/50 p-1">
                            <label id="radio-tarjeta" class="flex cursor-pointer h-full grow items-center justify-center overflow-hidden rounded-lg px-2 has-[:checked]:bg-background-light has-[:checked]:dark:bg-background-dark has-[:checked]:shadow-md has-[:checked]:text-primary text-gray-600 dark:text-gray-400 text-sm font-medium leading-normal transition-all duration-300">
                                <span class="truncate">Tarjeta</span>
                                <input checked="" class="invisible w-0" name="paymentMethod" type="radio" value="Tarjeta" />
                            </label>
                            <label id="radio-efectivo" class="flex cursor-pointer h-full grow items-center justify-center overflow-hidden rounded-lg px-2 has-[:checked]:bg-background-light has-[:checked]:dark:bg-background-dark has-[:checked]:shadow-md has-[:checked]:text-primary text-gray-600 dark:text-gray-400 text-sm font-medium leading-normal transition-all duration-300">
                                <span class="truncate">Efectivo</span>
                                <input class="invisible w-0" name="paymentMethod" type="radio" value="Efectivo" />
                            </label>
                            <label id="radio-transferencia" class="flex cursor-pointer h-full grow items-center justify-center overflow-hidden rounded-lg px-2 has-[:checked]:bg-background-light has-[:checked]:dark:bg-background-dark has-[:checked]:shadow-md has-[:checked]:text-primary text-gray-600 dark:text-gray-400 text-sm font-medium leading-normal transition-all duration-300">
                                <span class="truncate">Transferencia</span>
                                <input class="invisible w-0" name="paymentMethod" type="radio" value="Transferencia" />
                            </label>
                        </div>
                        <!-- Info de Efectivo (inicialmente oculto) -->
                        <div id="cashInfo" class="space-y-4 p-6 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-background-dark shadow-md hidden">
                            <p class="text-gray-700 dark:text-gray-300 font-semibold mb-4">Instrucciones para Pago en Efectivo por Vendedor</p>

                            <!-- ElectroMax (placeholder; se actualizará dinámicamente con datos del vendedor del JSON) -->
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4" id="cash-electromax-placeholder">
                                <p class="font-bold text-gray-800 dark:text-gray-200 mb-2">Vendedor: ElectroMax</p>
                                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                    <p>El vendedor se pondrá en contacto contigo dentro de las próximas 24 horas para coordinar el pago en efectivo y la entrega.</p>
                                    <p><span class="font-medium">Contacto:</span> +591 71234567 (WhatsApp disponible)</p>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Asegúrate de tener el efectivo listo en el momento acordado.</p>
                            </div>

                            <!-- ModaExpress (placeholder) -->
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4" id="cash-modexpress-placeholder">
                                <p class="font-bold text-gray-800 dark:text-gray-200 mb-2">Vendedor: ModaExpress</p>
                                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                    <p>El vendedor se pondrá en contacto contigo dentro de las próximas 24 horas para coordinar el pago en efectivo y la entrega.</p>
                                    <p><span class="font-medium">Contacto:</span> +591 72345678 (WhatsApp disponible)</p>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Asegúrate de tener el efectivo listo en el momento acordado.</p>
                            </div>
                        </div>
                        <!-- Info de transferencia (inicialmente oculto) -->
                        <div id="transferInfo" class="space-y-4 p-6 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-background-dark shadow-md hidden">
                            <p class="text-gray-700 dark:text-gray-300 font-semibold mb-4">Detalles de Cuenta Bancaria por Vendedor</p>

                            <!-- ElectroMax (placeholder; se actualizará con JSON: cuenta_bancaria, banco) -->
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4" id="transfer-electromax-placeholder">
                                <p class="font-bold text-gray-800 dark:text-gray-200 mb-2">Vendedor: ElectroMax</p>
                                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                    <p><span class="font-medium">Banco:</span> Banco Unión</p>
                                    <p><span class="font-medium">Número de Cuenta:</span> 1234-5678-9012-3456</p>
                                    <p><span class="font-medium">Titular:</span> ElectroMax SRL</p>
                                    <p><span class="font-medium">Código SWIFT:</span> BUNIBZLA</p>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Realiza la transferencia y envía el comprobante al vendedor una vez confirmada la orden.</p>
                            </div>

                            <!-- ModaExpress (placeholder) -->
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4" id="transfer-modexpress-placeholder">
                                <p class="font-bold text-gray-800 dark:text-gray-200 mb-2">Vendedor: ModaExpress</p>
                                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                    <p><span class="font-medium">Banco:</span> Banco Mercantil Santa Cruz</p>
                                    <p><span class="font-medium">Número de Cuenta:</span> 9876-5432-1098-7654</p>
                                    <p><span class="font-medium">Titular:</span> ModaExpress Ltda.</p>
                                    <p><span class="font-medium">Código SWIFT:</span> BCMMBZLA</p>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Realiza la transferencia y envía el comprobante al vendedor una vez confirmada la orden.</p>
                            </div>
                        </div>

                        <!-- Formulario de tarjeta -->
                        <div id="cardForm" class="space-y-6 p-6 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-background-dark shadow-md">
                            <!-- Número de tarjeta -->
                            <label class="flex flex-col relative">
                                <p class="text-gray-700 dark:text-gray-300 text-base font-semibold pb-2 flex items-center gap-2">
                                    <i class="bi bi-credit-card text-lg text-primary"></i> Número de Tarjeta
                                </p>
                                <input id="numero-tarjeta"
                                    type="text"
                                    maxlength="19"
                                    oninput="this.value = this.value.replace(/\s/g, '').replace(/(\d{4})/g, '$1 ').trim()"
                                    class="pl-12 pr-4 h-14 rounded-lg border border-gray-300 dark:border-gray-600 bg-background-light dark:bg-background-dark text-gray-800 dark:text-gray-200 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary text-base transition-colors duration-200"
                                    placeholder="**** **** **** ****" required />

                                <span class="absolute left-4 top-9 h-14 flex items-center text-gray-400 dark:text-gray-500 pointer-events-none">
                                    <i class="bi bi-credit-card-2-front text-xl"></i>
                                </span>
                            </label>

                            <!-- Expiración y CVV -->
                            <div class="grid grid-cols-2 gap-6">
                                <!-- Expiración -->
                                <label class="flex flex-col relative">
                                    <p class="text-gray-700 dark:text-gray-300 text-base font-semibold pb-2 flex items-center gap-2">
                                        <i class="bi bi-calendar-event text-lg text-primary"></i> Expiración (MM/AA)
                                    </p>
                                    <input id="expiracion-tarjeta"
                                        type="text"
                                        maxlength="5"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^(\d{2})(\d)/g, '$1/$2').trim()"
                                        class="pl-12 pr-4 h-14 rounded-lg border border-gray-300 dark:border-gray-600 bg-background-light dark:bg-background-dark text-gray-800 dark:text-gray-200 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary text-base transition-colors duration-200"
                                        placeholder="MM/AA" required />

                                    <span class="absolute left-4 top-9 h-14 flex items-center text-gray-400 dark:text-gray-500 pointer-events-none">
                                        <i class="bi bi-calendar text-xl"></i>
                                    </span>
                                </label>

                                <!-- CVV -->
                                <label class="flex flex-col relative">
                                    <p class="text-gray-700 dark:text-gray-300 text-base font-semibold pb-2 flex items-center gap-2">
                                        <i class="bi bi-shield-lock text-lg text-primary"></i> CVV
                                    </p>
                                    <input id="cvv-tarjeta"
                                        type="text"
                                        maxlength="4"
                                        class="pl-12 pr-4 h-14 rounded-lg border border-gray-300 dark:border-gray-600 bg-background-light dark:bg-background-dark text-gray-800 dark:text-gray-200 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary text-base transition-colors duration-200"
                                        placeholder="***" required />

                                    <span class="absolute left-4 top-9 h-14 flex items-center text-gray-400 dark:text-gray-500 pointer-events-none">
                                        <i class="bi bi-lock-fill text-xl"></i>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-8 sticky top-8 self-start">
                <p class="text-primary text-2xl font-bold mb-6">Tu Orden Unificada</p>
                <div class="space-y-6">
                    <!-- Contenedor dinámico para grupos de vendedores (se llenará con JS) -->
                    <div id="contenedor-grupos-vendedores">
                        <!-- Los grupos se generarán aquí basados en el JSON -->
                    </div>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 mt-8 pt-6 space-y-4">
                    <div class="flex justify-between text-lg">
                        <span class="text-gray-600 dark:text-gray-300">Subtotal General</span>
                        <span id="subtotal-general" class="font-semibold text-gray-800 dark:text-gray-200">Bs. 0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-300 text-lg">Total a Pagar</span>
                        <span id="total-pagar" class="font-black text-primary text-2xl">Bs. 0.00</span>
                    </div>
                </div>
                <div
                    class="bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300 p-4 rounded-lg text-sm mt-8">
                    <p class="font-bold">Al confirmar, se generarán órdenes separadas (una por vendedor).</p>
                </div>
                <div class="mt-8 space-y-4">
                    <button id="btn-confirmar-compra"
                        class="w-full bg-primary text-white font-bold py-4 px-6 rounded-lg text-lg hover:bg-primary/90 transition-colors duration-300 disabled:bg-gray-400 disabled:cursor-not-allowed">
                        Confirmar Compra y Generar Órdenes
                    </button>
                    <button id="btn-volver-carrito"
                        class="w-full text-primary font-bold py-4 px-6 rounded-lg text-lg hover:bg-primary/10 transition-colors duration-300 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">
                            arrow_back
                        </span>
                        Volver al Carrito
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php
    // 2. Incluir el pie de página 
    include 'footer.php';
    ?>
    <script src="./js/global.js"></script>
    <script src="./js/pago.js"></script>
</body>

</html>