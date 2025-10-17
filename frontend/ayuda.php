<?php
$pageTitle = "Centro de Ayuda - Emarket Bolivia";
$currentPage = "ayuda";
// Define la ruta base para los enlaces (ajusta si es necesario)
$base_path = '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="font-sans antialiased bg-gray-50 text-gray-800">
    <?php
    // Incluir navbar (se asume que existe)
    include 'navbar.php';
    ?>
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 hidden"></div>

    <div class="bg-blue-600 text-white py-16 mb-8 shadow-md">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl font-extrabold mb-2"><i class="bi bi-question-circle-fill mr-3"></i> Centro de Ayuda</h1>
            <p class="text-xl opacity-90">Encuentra respuestas rápidas o contacta a nuestro equipo de soporte.</p>
        </div>
    </div>

    <div class="container mx-auto px-4 pb-12">


        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

            <div class="lg:col-span-2">
                <div class="bg-white p-6 md:p-8 rounded-xl shadow-lg">
                    <h2 class="text-3xl font-bold text-blue-700 mb-6 border-b border-gray-200 pb-3">Preguntas Frecuentes (FAQ)</h2>

                    <div class="mb-8">
                        <h3 class="text-2xl font-semibold text-blue-600 mb-4 flex items-center"><i class="bi bi-cart-fill mr-3"></i> Compras y Pedidos</h3>

                        <div class="faq-item border-b border-gray-100 py-3">
                            <details class="group cursor-pointer">
                                <summary class="flex justify-between items-center text-lg font-medium text-gray-900 list-none hover:text-blue-500 transition duration-150">
                                    <span>¿Cómo realizo una compra?</span>
                                    <i class="bi bi-chevron-down group-open:rotate-180 transition duration-300"></i>
                                </summary>
                                <div class="mt-3 text-gray-600 pl-4 border-l-2 border-green-500">
                                    <p>Para realizar una compra en Emarket Bolivia, sigue estos sencillos pasos:</p>
                                    <ol class="list-decimal list-inside ml-2 mt-2 space-y-1">
                                        <li>Regístrate o inicia sesión en tu cuenta.</li>
                                        <li>Busca los productos y haz clic en "Agregar al Carrito".</li>
                                        <li>Procede al Checkout (Caja) desde tu carrito.</li>
                                        <li>Ingresa tu dirección de envío y selecciona tu método de pago.</li>
                                        <li>Confirma tu pedido. Recibirás una notificación por correo electrónico.</li>
                                    </ol>
                                </div>
                            </details>
                        </div>

                        <div class="faq-item border-b border-gray-100 py-3">
                            <details class="group cursor-pointer">
                                <summary class="flex justify-between items-center text-lg font-medium text-gray-900 list-none hover:text-blue-500 transition duration-150">
                                    <span>¿Qué métodos de pago son aceptados?</span>
                                    <i class="bi bi-chevron-down group-open:rotate-180 transition duration-300"></i>
                                </summary>
                                <div class="mt-3 text-gray-600 pl-4 border-l-2 border-green-500">
                                    <p>Aceptamos diversas opciones de pago seguras para facilitar tu compra:</p>
                                    <ul class="list-disc list-inside ml-2 mt-2 space-y-1">
                                        <li>Tarjetas de crédito/débito (Visa, MasterCard).</li>
                                        <li>Transferencias bancarias a través de [Nombre de bancos locales].</li>
                                        <li>Billeteras y pasarelas digitales locales (Ej: Tigo Money, etc.).</li>
                                        <li>Pago contra entrega (disponibilidad limitada según el vendedor/zona).</li>
                                    </ul>
                                </div>
                            </details>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h3 class="text-2xl font-semibold text-blue-600 mb-4 flex items-center"><i class="bi bi-truck mr-3"></i> Envíos y Tiempos de Entrega</h3>

                        <div class="faq-item border-b border-gray-100 py-3">
                            <details class="group cursor-pointer">
                                <summary class="flex justify-between items-center text-lg font-medium text-gray-900 list-none hover:text-blue-500 transition duration-150">
                                    <span>¿Quién se encarga del envío de mi pedido?</span>
                                    <i class="bi bi-chevron-down group-open:rotate-180 transition duration-300"></i>
                                </summary>
                                <div class="mt-3 text-gray-600 pl-4 border-l-2 border-green-500">
                                    <p>Emarket Bolivia es una plataforma de intermediación (marketplace). El **Vendedor es el responsable directo de la gestión y contratación de la logística de envío**.</p>
                                    <p>El Vendedor utilizará sus proveedores logísticos de confianza para asegurar la entrega del producto en la dirección que proporcionaste.</p>
                                </div>
                            </details>
                        </div>

                        <div class="faq-item border-b border-gray-100 py-3">
                            <details class="group cursor-pointer">
                                <summary class="flex justify-between items-center text-lg font-medium text-gray-900 list-none hover:text-blue-500 transition duration-150">
                                    <span>¿Puedo rastrear mi pedido?</span>
                                    <i class="bi bi-chevron-down group-open:rotate-180 transition duration-300"></i>
                                </summary>
                                <div class="mt-3 text-gray-600 pl-4 border-l-2 border-green-500">
                                    <p>Sí, una vez que el vendedor despache tu paquete, recibirás un correo electrónico con el número de seguimiento y el enlace al proveedor de logística utilizado, siempre que el vendedor lo proporcione.</p>
                                </div>
                            </details>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white p-6 md:p-8 rounded-xl shadow-2xl sticky top-8">
                    <h3 class="text-2xl font-bold text-green-600 mb-4 flex items-center"><i class="bi bi-headset mr-3"></i> Contacta a Soporte</h3>
                    <p class="text-gray-600 mb-4">Si las preguntas frecuentes no resolvieron tu duda, envíanos un mensaje directo.</p>

                    <form action="<?php echo $base_path; ?>procesar_ayuda.php" method="POST" class="space-y-4">
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                            <input type="text" id="nombre" name="nombre" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Juan Pérez">
                        </div>
                        <div>
                            <label for="email_contacto" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                            <input type="email" id="email_contacto" name="email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="tu.correo@ejemplo.com">
                        </div>
                        <div>
                            <label for="asunto" class="block text-sm font-medium text-gray-700">Asunto</label>
                            <select id="asunto" name="asunto" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecciona una opción</option>
                                <option value="problema_pedido">Problema con mi Pedido</option>
                                <option value="pago">Consulta de Pago</option>
                                <option value="devolucion">Devolución / Garantía</option>
                                <option value="tecnico">Problema Técnico de la Web</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div>
                            <label for="mensaje_contacto" class="block text-sm font-medium text-gray-700">Mensaje Detallado</label>
                            <textarea id="mensaje_contacto" name="mensaje" rows="4" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Describe tu problema o consulta con detalle."></textarea>
                        </div>

                        <button type="submit" class="w-full bg-green-500 text-white font-bold py-3 rounded-lg hover:bg-green-600 transition duration-300 shadow-md">
                            Enviar Solicitud
                        </button>
                    </form>

                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <h4 class="text-lg font-semibold text-gray-800 mb-2">Contacto Directo</h4>
                        <p class="flex items-center text-sm mb-1"><i class="bi bi-telephone-fill mr-2 text-blue-500"></i> **Teléfono:** +591 2 1234567</p>
                        <p class="flex items-center text-sm mb-1"><i class="bi bi-envelope-fill mr-2 text-blue-500"></i> **Email:** <a href="mailto:soporte@emarket-bolivia.com" class="text-blue-600 hover:underline">soporte@emarket-bolivia.com</a></p>
                        <p class="text-xs text-gray-500 mt-2">Horario: Lunes a Viernes 8:00 - 18:00 (Hora Bolivia)</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <?php
    // Incluir footer (se asume que existe y usa Tailwind)
    include 'footer.php';
    ?>
    <script src="./js/global.js"></script>

    <script>
        // Aquí podrías agregar un script para manejar el colapso/expansión de las <details>
    </script>
</body>

</html>