<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E-Market Bolivia - Inicio</title>
    <link rel="icon" type="image/png" href="./img/icon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./css/variables.css">
    <link rel="stylesheet" href="./css/estilos.css">
    <link rel="stylesheet" href="./css/custom.css">
    <link rel="stylesheet" href="estilos_perfil.css">

</head>

<body>
    <!-- Barra superior -->
    <?php
    include 'navbar.php';
    ?>

    <div class="bg-bolivia"></div>

    <!-- Banner Usuario-->
    <!-- Banner Empresarial Estático -->
    <div class="header-banner d-flex flex-column align-items-center justify-content-center text-center text-light position-relative"
        id="headerBanner"
        style="
         width: 100%;
         height: 320px;
         background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)),
                     url('https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=1200&q=80') 
                     center/cover no-repeat;
     ">
        <div class="position-relative" style="z-index: 1;">
            <h2 class="fw-bold mb-2" id="nombre-empresa">Mi Empresa, Mi Identidad</h2> <!-- Se llenará con razon_social via JS -->
            <p class="lead mb-0">Impulsando mi negocio con e-market Bolivia</p>
        </div>
    </div>

    <!-- Menú -->
    <div class="barra">
        <div class="container">
            <ul class="nav nav-tabs border-0">
                <li class="nav-item"><a class="nav-link active" href="#" data-pestana="inicio">Inicio</a></li>
                <!--el boton debe estar oculto si el usuario no tiene rol de vendedor-->
                <?php if (isset($_SESSION['rol']) && strtolower($_SESSION['rol']) === 'vendedor'): ?>
                    <li class="nav-item"><a class="nav-link" href="#" data-pestana="productos">Productos</a></li> <!-- Para después -->
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="#" data-pestana="perfil">Perfil de la Empresa</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-pestana="contacto">Contactos</a></li>
            </ul>
        </div>
    </div>

    <!-- Contenedor principal de pestañas (agregado para dinámicas) -->
    <main class="container mt-5">
        <!-- Secciones ocultas por defecto; JS las maneja -->
    </main>

    <!-- Script JS único al final -->
    <script src="js/vendedor.js"></script>
    <script src="./js/global.js"></script>
</body>