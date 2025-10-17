<?php
$pageTitle = "Política de Privacidad - Emarket Bolivia";
$currentPage = "politica-privacidad";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Estilos alineados con la Misión (Primario: #007bff, Secundario: #28a745) */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
            /* Fondo profesional */
            color: #343a40;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            /* Degradado corporativo: Azul */
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 60px 20px;
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .content-card {
            background: white;
            border-radius: 10px;
            padding: 40px;
            margin-bottom: 30px;
            /* Sombra sutil y elegante */
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
        }

        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            /* Color primario */
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .back-button:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }

        .section {
            margin-bottom: 35px;
        }

        .section h3 {
            color: #007bff;
            /* Título de sección en azul corporativo */
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .section ul {
            padding-left: 20px;
        }

        .section ul li {
            margin-bottom: 8px;
        }

        .privacy-highlight {
            /* Tono verde claro para destacar información de seguridad/confianza */
            background: #e9f7ef;
            border-left: 5px solid #28a745;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: left;
            vertical-align: top;
        }

        .data-table th {
            background: #f8f9fa;
            font-weight: 700;
            color: #343a40;
        }
    </style>
</head>

<body>
    <?php
    include 'navbar.php';
    ?>
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 hidden"></div>
    <div class="header">
        <div class="container">
            <h1><i class="bi bi-shield-lock-fill"></i> Política de Privacidad de Emarket Bolivia</h1>
            <p>Un compromiso transparente con la protección y el tratamiento responsable de sus datos personales.</p>
        </div>
    </div>

    <div class="container">
        <div class="content-card">

            <div class="privacy-highlight">
                <p><strong>Vigencia:</strong> Esta política es efectiva a partir de **Enero de 2025**.</p>
                <p>En **Emarket Bolivia**, la confianza de nuestros usuarios es primordial. Por ello, nos comprometemos a proteger su **privacidad** y la **integridad** de sus datos personales en estricto cumplimiento de la normativa boliviana aplicable.</p>
            </div>

            <div class="section">
                <h3>1. Responsable del Tratamiento de Datos</h3>
                <p>El responsable del tratamiento de los datos recabados a través de la plataforma Emarket Bolivia es:</p>
                <ul>
                    <li>**Razón Social:** Emarket Bolivia S.R.L. (Nombre de la entidad ficticia)</li>
                    <li>**Domicilio:** [Dirección legal de la empresa en Bolivia]</li>
                    <li>**Contacto:** <a href="mailto:privacidad@emarket-bolivia.com">privacidad@emarket-bolivia.com</a></li>
                </ul>
            </div>

            <div class="section">
                <h3>2. Datos Personales Objeto de Recolección</h3>
                <p>Recopilamos la información estrictamente necesaria para la prestación de nuestros servicios de plataforma de comercio electrónico. Los tipos de datos incluyen:</p>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Categoría de Dato</th>
                            <th>Descripción y Ejemplos</th>
                            <th>Base Legal / Finalidad Principal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Datos de Identificación y Contacto</strong></td>
                            <td>Nombre completo, número de cédula de identidad, dirección de correo electrónico, número de teléfono.</td>
                            <td>Gestión de la cuenta de usuario y notificaciones esenciales.</td>
                        </tr>
                        <tr>
                            <td><strong>Datos Transaccionales</strong></td>
                            <td>Detalles de pedidos, historial de compras, método de pago utilizado (sin almacenar datos sensibles de tarjetas).</td>
                            <td>Procesamiento y facturación de la compraventa de productos.</td>
                        </tr>
                        <tr>
                            <td><strong>Datos de Ubicación (para el Vendedor)</strong></td>
                            <td>La **dirección de entrega** proporcionada por el Comprador y compartida con el Vendedor para que este gestione el envío.</td>
                            <td>Facilitar la entrega del producto adquirido (cumplimiento del contrato de compraventa).</td>
                        </tr>
                        <tr>
                            <td><strong>Datos Técnicos y de Uso</strong></td>
                            <td>Dirección IP, tipo de dispositivo, datos de navegación, actividad en la plataforma (clics, tiempo de permanencia).</td>
                            <td>Análisis de rendimiento, seguridad del sistema y prevención de fraudes.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="section">
                <h3>3. Finalidad del Tratamiento de Datos</h3>
                <p>La información personal es utilizada exclusivamente para los siguientes fines:</p>
                <ul class="list-unstyled">
                    <li><i class="bi bi-cart-check-fill" style="color: #007bff;"></i> **Ejecución del Servicio:** Facilitar la interacción entre compradores y vendedores y procesar los pagos.</li>
                    <li><i class="bi bi-person-badge-fill" style="color: #007bff;"></i> **Gestión de la Cuenta:** Mantener el perfil de usuario, validar identidad y ofrecer soporte técnico.</li>
                    <li><i class="bi bi-graph-up-arrow" style="color: #007bff;"></i> **Mejora Continua:** Analizar tendencias de uso para mejorar la funcionalidad y el diseño de la plataforma.</li>
                    <li><i class="bi bi-envelope-fill" style="color: #007bff;"></i> **Comunicaciones:** Enviar información relevante sobre el estado de las transacciones y ofertas personalizadas (si se dio consentimiento).</li>
                    <li><i class="bi bi-virus" style="color: #007bff;"></i> **Cumplimiento Legal y Seguridad:** Prevenir actividades fraudulentas y cumplir con las obligaciones legales y regulatorias bolivianas.</li>
                </ul>
            </div>

            <div class="section">
                <h3>4. Comunicación de Datos a Terceros</h3>
                <p>Emarket Bolivia no comercializa su información personal. La comunicación de datos se realiza únicamente en los siguientes casos, esenciales para el funcionamiento de la plataforma:</p>
                <ul class="list-unstyled">
                    <li><i class="bi bi-shop" style="color: #28a745;"></i> **Vendedores de la Plataforma:** Se comparte la **dirección de entrega**, el **nombre** y el **teléfono** del comprador con el vendedor para que este pueda gestionar el despacho de la compra.</li>
                    <li><i class="bi bi-credit-card-2-front-fill" style="color: #28a745;"></i> **Proveedores de Pago:** A las entidades financieras y pasarelas de pago necesarias para procesar las transacciones.</li>
                    <li><i class="bi bi-person-lines-fill" style="color: #28a745;"></i> **Autoridades Legales:** Cuando sea requerido por mandato judicial u obligación legal.</li>
                </ul>
                <p class="privacy-highlight">⚠️ **Nota sobre Logística:** Emarket Bolivia es una plataforma de intermediación. Actualmente, **no realizamos la logística de envío de forma directa**. La información de envío es transferida al Vendedor para que este gestione su método de despacho habitual.</p>
            </div>

            <div class="section">
                <h3>5. Derechos del Usuario (Derechos ARCO)</h3>
                <p>Como usuario, usted tiene derecho a ejercer sus derechos sobre sus datos personales. Puede solicitar:</p>
                <ul class="list-unstyled">
                    <li><i class="bi bi-eye-fill" style="color: #007bff;"></i> **Acceso:** Obtener confirmación sobre si estamos tratando sus datos y solicitarnos una copia.</li>
                    <li><i class="bi bi-pencil-fill" style="color: #007bff;"></i> **Rectificación:** Corregir sus datos que sean inexactos o estén incompletos.</li>
                    <li><i class="bi bi-trash-fill" style="color: #007bff;"></i> **Cancelación/Eliminación:** Solicitar la supresión de sus datos cuando ya no sean necesarios.</li>
                    <li><i class="bi bi-exclamation-octagon-fill" style="color: #007bff;"></i> **Oposición:** Oponerse a que sus datos se sigan tratando con fines de marketing.</li>
                </ul>
                <p>Para ejercer estos derechos, debe enviar una solicitud al correo electrónico de contacto indicado en la Sección 1.</p>
            </div>

            <div class="section">
                <h3>6. Medidas de Seguridad</h3>
                <p>Hemos implementado medidas de seguridad técnicas y organizativas para proteger su información contra el acceso no autorizado, la pérdida o alteración. Esto incluye:</p>
                <ul>
                    <li>Cifrado de datos sensible mediante protocolos **SSL/TLS**.</li>
                    <li>Acceso restringido y autenticado a los sistemas de gestión.</li>
                    <li>Auditorías periódicas de seguridad de la infraestructura.</li>
                </ul>
            </div>

            <div class="section">
                <h3>7. Cambios a esta Política</h3>
                <p>Cualquier modificación a esta Política de Privacidad será comunicada con antelación a través de un aviso destacado en nuestra plataforma o, si es un cambio sustancial, mediante notificación directa a su correo electrónico.</p>
            </div>

            <div class="section">
                <h3>8. Contacto y Consultas</h3>
                <p>Si tiene preguntas o inquietudes sobre esta política, le rogamos contactar con nuestro **Oficial de Protección de Datos**:</p>
                <div class="privacy-highlight">
                    <p><strong>Oficial de Protección de Datos</strong></p>
                    <p><i class="bi bi-at" style="color: #28a745;"></i> **Email:** <a href="mailto:privacidad@emarket-bolivia.com">privacidad@emarket-bolivia.com</a></p>
                    <p><i class="bi bi-telephone-fill" style="color: #28a745;"></i> **Teléfono:** [Número de Teléfono Corporativo]</p>
                </div>
            </div>

        </div>
    </div>
    <?php
    include 'footer.php';
    ?>
    <script src="./js/global.js"></script>
</body>

</html>