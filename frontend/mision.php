<?php
$pageTitle = "Nuestra Misión - Emarket Bolivia";
$currentPage = "mision";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Paleta de Colores Sugerida para Emarket-Bolivia (Ejemplo Profesional):
        - Primario: #007bff (Azul Corporativo)
        - Secundario: #28a745 (Verde Éxito/Confianza)
        - Fondo: #f8f9fa (Gris Claro Suave)
        - Acento: #ffc107 (Amarillo para destacar)
        */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
            /* Fondo más claro y profesional */
            color: #343a40;
            /* Color de texto oscuro para alta legibilidad */
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            /* Degradado más corporativo y menos saturado */
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

        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .content-card {
            background: white;
            border-radius: 10px;
            padding: 40px;
            margin-bottom: 30px;
            /* Sombra más sutil y elegante */
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
            line-height: 1.7;
        }

        .content-card h2 {
            color: #007bff;
            margin-top: 0;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin: 40px 0;
        }

        .feature-item {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .feature-item:hover {
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.1);
            transform: translateY(-5px);
        }

        .feature-item .icon {
            font-size: 3rem;
            display: block;
            margin-bottom: 15px;
            color: #28a745;
            /* Color secundario para iconos */
        }

        .feature-item h3 {
            color: #343a40;
            margin-bottom: 10px;
        }

        .highlight-box {
            background: #e9f7ef;
            /* Tono verde claro para destacar objetivos/valores */
            padding: 30px;
            border-radius: 8px;
            border-left: 5px solid #28a745;
            margin: 30px 0;
        }

        .highlight-box h3 {
            color: #28a745;
            margin-top: 0;
        }

        .highlight-box ul {
            list-style-type: none;
            padding-left: 0;
        }

        .highlight-box ul li {
            margin-bottom: 10px;
            position: relative;
            padding-left: 25px;
        }

        .highlight-box ul li strong {
            color: #007bff;
            /* Color primario para resaltar la palabra clave */
        }

        .highlight-box ul li::before {
            content: "\F382";
            /* Icono de check (bi-check-circle-fill) de Bootstrap Icons */
            font-family: 'bootstrap icons';
            color: #007bff;
            position: absolute;
            left: 0;
        }
    </style>
</head>

<body>
    <?php
    // Incluir navbar (se asume que existe)
    include 'navbar.php';
    ?>
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 hidden"></div>
    <div class="header">
        <div class="container">
            <h1>Nuestra Misión: Impulsando el E-commerce en Bolivia 🇧🇴</h1>
            <p>Un compromiso firme con la innovación, el crecimiento y la comunidad boliviana.</p>
        </div>
    </div>

    <div class="container">

        <div class="content-card">
            <div class="mission-content">
                <h2>Declaración de Misión Corporativa</h2>
                <p>La misión de **Emarket Bolivia** es **transformar el ecosistema digital y comercial del país**, proporcionando una plataforma de comercio electrónico **robusta, intuitiva y segura**. Estamos dedicados a ser el puente que conecta a los **productores y emprendedores bolivianos** con una base de consumidores amplia y diversa, fomentando la inclusión digital y el desarrollo económico local.</p>

                <p>Buscamos empoderar a nuestros vendedores con herramientas tecnológicas de vanguardia para que puedan escalar sus negocios, al mismo tiempo que garantizamos a nuestros clientes una experiencia de compra transparente, conveniente y de total confianza.</p>

                <div class="features-grid">

                    <div class="feature-item">
                        <i class="bi bi-lightbulb-fill icon"></i>
                        <h3>Innovación Tecnológica</h3>
                        <p>Aplicamos constantemente soluciones tecnológicas avanzadas para optimizar cada aspecto de la experiencia de usuario y la gestión comercial.</p>
                    </div>

                    <div class="feature-item">
                        <i class="bi bi-people-fill icon"></i>
                        <h3>Fomento Comunitario</h3>
                        <p>Somos un motor de apoyo para el talento y el producto nacional, dedicados a impulsar el crecimiento y la visibilidad de los negocios bolivianos.</p>
                    </div>

                    <div class="feature-item">
                        <i class="bi bi-shield-lock-fill icon"></i>
                        <h3>Seguridad y Confianza</h3>
                        <p>Nuestra prioridad es la protección de datos y transacciones, construyendo relaciones duraderas basadas en la transparencia absoluta.</p>
                    </div>
                </div>

                <div class="highlight-box">
                    <h3>✅ Objetivos Estratégicos Fundamentales</h3>
                    <ul style="line-height: 2;">
                        <li><strong>Digitalización Sostenible:</strong> Liderar la migración de las micro, pequeñas y medianas empresas (MiPyMES) bolivianas al entorno digital.</li>
                        <li><strong>Inclusión Financiera:</strong> Ofrecer diversas soluciones de pago accesibles, promoviendo la bancarización y el comercio formal.</li>
                        <li><strong>Experiencia Superior:</strong> Asegurar una navegación sencilla y un proceso de compra y venta eficaz para todos los usuarios.</li>
                        <li><strong>Expansión Nacional:</strong> Conectar a vendedores y compradores en los nueve departamentos de Bolivia, eliminando barreras geográficas.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php
    // Incluir footer (se asume que existe)
    include 'footer.php';
    ?>
    <script src="./js/global.js"></script>
</body>

</html>