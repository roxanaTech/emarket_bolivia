<?php
$pageTitle = "Términos y Condiciones - Emarket Bolivia";
$currentPage = "terminos-condiciones";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Estilos alineados con el diseño corporativo (Azul Primario: #007bff) */
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

        .section h4 {
            color: #343a40;
            margin-top: 20px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .section ul {
            padding-left: 20px;
        }

        .section ul li {
            margin-bottom: 8px;
        }

        .highlight-box {
            /* Tono gris claro para destacar fechas y avisos legales */
            background: #f1f5f9;
            border-left: 5px solid #007bff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }

        .warning-box {
            background: #fff3cd;
            /* Amarillo claro para advertencias */
            border-left: 5px solid #ffc107;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
            color: #856404;
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
            <h1><i class="bi bi-file-earmark-text-fill"></i> Términos y Condiciones de Uso</h1>
            <p>Documento legal que rige la relación entre Emarket Bolivia y sus usuarios.</p>
        </div>
    </div>

    <div class="container">
        <div class="content-card">

            <div class="highlight-box">
                <p><strong>Fecha de Vigencia:</strong> **Enero de 2025**.</p>
                <p>El uso de la plataforma **Emarket Bolivia** implica la aceptación plena de los siguientes términos, condiciones y políticas de servicio. Por favor, léalos detenidamente.</p>
            </div>

            <div class="section">
                <h3>1. Naturaleza del Servicio y Objeto</h3>
                <p>Emarket Bolivia S.R.L. (en adelante, "La Plataforma") proporciona un servicio de **intermediación digital (marketplace)** que permite a Vendedores independientes publicar, ofrecer y vender bienes y servicios a Compradores a través de internet.</p>
                <p>La Plataforma no es la vendedora de los productos, sino un espacio de encuentro. Por lo tanto, el contrato de compraventa se celebra **directamente entre el Vendedor y el Comprador**.</p>
            </div>

            <div class="section">
                <h3>2. Capacidad y Registro de Cuenta</h3>
                <p>El acceso al servicio está permitido a personas que tengan la capacidad legal para contratar y cumplir con la legislación boliviana (mayoría de edad). El usuario se compromete a:</p>
                <ul>
                    <li>Proporcionar información de registro exacta, veraz y completa.</li>
                    <li>Mantener la confidencialidad de su contraseña y clave de acceso.</li>
                    <li>Ser el único responsable de toda la actividad que ocurra bajo su cuenta.</li>
                </ul>
            </div>

            <div class="section">
                <h3>3. Obligaciones del Usuario (Comprador y Vendedor)</h3>
                <p>El Usuario se obliga a utilizar la Plataforma de manera lícita, sin incurrir en:</p>
                <ul>
                    <li>Publicación de contenido difamatorio, ilegal, obsceno o que viole derechos de terceros.</li>
                    <li>Actividades de fraude, suplantación de identidad o competencia desleal.</li>
                    <li>Interferencia o ataque a la seguridad y estabilidad técnica de la Plataforma.</li>
                    <li>Violación de cualquier derecho de propiedad intelectual o industrial.</li>
                </ul>
            </div>

            <div class="section">
                <h3>4. Condiciones de Compraventa y Pago</h3>

                <h4>4.1 Proceso de Transacción</h4>
                <p>El Comprador, al confirmar un pedido, acepta la obligación de pagar el precio publicado. La Plataforma gestiona el pago de forma segura a través de pasarelas de pago externas, y retiene una comisión por el servicio de intermediación.</p>

                <h4>4.2 Precios</h4>
                <p>Todos los precios se expresan en Bolivianos (BOB) y son establecidos por los Vendedores. Se presume que incluyen los impuestos legalmente aplicables en Bolivia (IVA, IT, etc.).</p>

                <h4>4.3 Reembolsos y Devoluciones</h4>
                <p>Las políticas de devolución, garantía y reembolso son responsabilidad principal del Vendedor, siempre bajo los lineamientos mínimos establecidos por Emarket Bolivia y la normativa de defensa del consumidor boliviano.</p>
            </div>

            <div class="section">
                <h3>5. Responsabilidad sobre la Logística y Envíos</h3>

                <div class="warning-box">
                    <p><i class="bi bi-truck-flatbed"></i> **ADVERTENCIA IMPORTANTE:**</p>
                    <p>Emarket Bolivia **NO** asume la responsabilidad por la logística de envío, transporte, manipulación o entrega de los productos.</p>
                </div>

                <p>La **gestión de la entrega** del producto es **responsabilidad exclusiva del Vendedor**, quien deberá coordinar y contratar los servicios de transporte o mensajería necesarios para que el producto llegue al Comprador en la dirección indicada y en los plazos ofrecidos.</p>
                <ul>
                    <li>**El Vendedor** es responsable de los daños, pérdidas o demoras que ocurran durante el proceso de envío.</li>
                    <li>**Emarket Bolivia** no será responsable por fallas o incumplimientos en los servicios de terceros contratados por el Vendedor para la logística.</li>
                </ul>
            </div>

            <div class="section">
                <h3>6. Propiedad Intelectual</h3>
                <p>Todos los derechos de propiedad intelectual e industrial sobre el software, diseño, logotipos, código fuente y contenido de la Plataforma (excluyendo el contenido cargado por Vendedores) son propiedad de Emarket Bolivia S.R.L. o están licenciados a su favor. Se prohíbe su copia, reproducción o distribución sin autorización expresa.</p>
            </div>

            <div class="section">
                <h3>7. Limitación y Exclusión de Responsabilidad</h3>
                <p>La Plataforma no garantiza la calidad, seguridad, legalidad o veracidad de los productos y servicios ofrecidos por los Vendedores. La responsabilidad de Emarket Bolivia se limita exclusivamente a la prestación de los servicios de intermediación técnica.</p>
                <p>En ningún caso la Plataforma será responsable por daños directos o indirectos, incidentales o consecuenciales derivados de:</p>
                <ul>
                    <li>Disputas entre Compradores y Vendedores.</li>
                    <li>Incumplimiento de garantías de productos por parte de Vendedores.</li>
                    <li>Interrupción, suspensión o fallo del servicio debido a causas externas o de fuerza mayor.</li>
                </ul>
            </div>

            <div class="section">
                <h3>8. Ley Aplicable y Jurisdicción</h3>
                <p>Estos Términos y Condiciones se rigen e interpretan de acuerdo con las **leyes vigentes de la República de Bolivia**.</p>
                <p>Cualquier controversia derivada de estos términos será sometida a la jurisdicción de los tribunales competentes de la ciudad de **La Paz, Bolivia**, renunciando las partes a cualquier otro fuero que pudiera corresponderles.</p>
            </div>

            <div class="section">
                <h3>9. Contacto y Modificaciones</h3>
                <p>Emarket Bolivia se reserva el derecho de modificar estos Términos en cualquier momento. Los cambios se harán efectivos al ser publicados en la Plataforma. El uso continuado del servicio después de la publicación constituye la aceptación de los nuevos términos.</p>
                <div class="highlight-box">
                    <p><i class="bi bi-question-circle-fill"></i> Para cualquier consulta sobre estos Términos:</p>
                    <ul>
                        <li>**Email Legal:** <a href="mailto:legal@emarket-bolivia.com">legal@emarket-bolivia.com</a></li>
                        <li>**Dirección:** [Dirección legal de la empresa en La Paz, Bolivia]</li>
                    </ul>
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