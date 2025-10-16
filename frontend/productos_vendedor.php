<?php
session_start();
$usuario = $_SESSION['usuario'] ?? 'Invitado';
?>
<?php include "header_perfil.php"; ?>
<!-- Contenido -->
<div class="container mt-4">
    <div class="row">
        <!-- Productos -->
        <div class="col-md-8">
            <h4 class="mb-3">Productos del Vendedor</h4>
            <div class="row g-3" id="contenedorProductos"></div>

            <!-- Paginación -->
            <nav>
                <ul class="pagination justify-content-center mt-4" id="paginacion"></ul>
            </nav>
        </div>
        <!-- Sidebar -->
        <div class="col-md-4">
            <div class="p-3 bg-white rounded shadow-sm mb-3">
                <h6>Acciones Rápidas</h6>
                <a href="registrar_producto.php"><button class="btn btn-success w-100 mb-2"><i class="bi bi-plus-circle"></i> Agregar nuevo producto</button></a>
                <button class="btn btn-warning w-100"><i class="bi bi-pencil"></i> Ver historial de ventas</button>
            </div>
            <div class="p-3 bg-white rounded shadow-sm">
                <h6>Estadísticas</h6>
                <p>Productos publicados: <strong><?php ?></strong></p>
                <p>Ventas realizadas: <strong><?php ?></strong></p>
                <p>Reseñas positivas: <strong><?php ?></strong></p>
            </div>
        </div>
    </div>
</div>
<script>
    const idVendedor = <?php echo json_encode($idVendedor) ?>
</script>
<?php require_once "footer.php"; ?>
<script src="./js/global.js"></script>