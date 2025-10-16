<head>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<style>
  #inicio,
  #perfil,
  #contacto {
    width: 90%;
    margin: auto;
  }

  #productos-muestra .row {
    max-width: 100%;
    overflow-x: hidden;
  }
</style>
<?php
// No queries PHP aquí; todo via JS/API
include "header_perfil.php";
?>

<!-- Contenedor de pestañas (dentro de <main> del header) -->
<!-- Sección Inicio (de inicio.php, adaptado a Bootstrap responsive: 1 col móvil, 2 tablet, 4 web) -->
<section id="inicio" class="pestana" style="display: none;"> <!-- Visible por defecto -->
  <h4 class="mb-3">Productos destacados</h4>
  <div id="productos-muestra" class="row g-3">
    <div class="col-12 col-md-6 col-lg-3">
      <p>Cargando productos...</p>
    </div>
  </div>
</section>

<!-- Sección Perfil (de perfil_vendedor.php) -->
<section id="perfil" class="pestana" style="display: block;">
  <div class="row">
    <div class="col-md-8">
      <div class="info-card" id="info-empresa">
        <h2>Sobre la Empresa</h5>
          <p><strong>Cargando...</strong> <!-- Se llenará con nombre, resumen, rubro --></p>
      </div>
      <div class="info-card" id="representante">
        <h5>Representante</h5>
        <div class="d-flex align-items-center">
          <div><strong>Cargando...</strong><br>Ventas Corporativas</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <?php if (isset($_SESSION['rol']) && strtolower($_SESSION['rol']) === 'vendedor'): ?>
        <div class="info-card">
          <a href="formularioVendedor.php"><button class="btn btn-warning w-100 mb-2">Editar informacion</button></a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Sección Contacto (de contacto_vendedor.php) -->
<section id="contacto" class="pestana" style="display: none;">
  <div class="row mt-4">
    <!-- Columna izquierda -->
    <div class="col-md-8">
      <div class="contact-card" id="info-contacto">
        <h5>Información de Contacto</h5>
        <div class="d-flex align-items-center">
          <img src="" class="rounded-circle me-3" width="60" height="60" alt="Contacto">
          <div>
            <strong>Cargando...</strong><br> <!-- Nombre representante -->
            Representante de Ventas
          </div>
        </div>
        <hr>
        <p><i class="bi bi-telephone"></i> Teléfono: <a href="#">Cargando...</a></p> <!-- tel -->
        <p><i class="bi bi-phone"></i> Móvil: <a href="#">Cargando...</a></p> <!-- movil -->
        <p><i class="bi bi-globe"></i> Sitio web: <a href="#">Cargando...</a></p> <!-- web -->
      </div>

      <div class="location-card" id="ubicacion">
        <h5>Ubicación de la Empresa</h5>
        <p><i class="bi bi-geo-alt"></i> Cargando...</p> <!-- direccion -->
        <small class="text-muted">Verificado por Cargando...</small> <!-- verificado -->
      </div>
    </div>

    <!-- Columna derecha -->
    <div class="col-md-4">
      <div class="sidebar-card">
        <h6>Póngase en contacto con el proveedor</h6>
        <p><strong id="nombre-empresa-contacto">Cargando...</strong></p> <!-- razon_social -->
        <button class="btn btn-primary-custom w-100 mb-2">Contactar Ahora</button>
        <button class="btn btn-success-custom w-100">Solicitar Cotización</button>
      </div>
    </div>
  </div>
</section>

<?php include "footer.php"; ?>
<script src="./js/global.js"></script>