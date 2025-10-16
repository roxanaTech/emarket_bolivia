<?php
include "header_perfil.php";

require_once "/backend/src/config/database.php";

//Obtenemos el ID del vendedor desde la URL
$idVendedor = isset($_GET['vendedor']) ? (int)$_GET['vendedor'] : 1;

try {
  $sql = "SELECT p.id_producto, p.nombre, p.precio, i.ruta AS imagen
            FROM producto p
            LEFT JOIN imagen i ON p.id_imagen_principal = i.id_imagen
            WHERE p.id_vendedor = :id_vendedor
            ORDER BY p.id_producto DESC";

  $stmt = $pdo->prepare($sql);
  $stmt->execute(['id_vendedor' => $idVendedor]);
  $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  echo "<div class='alert alert-danger'>Error al cargar productos: {$e->getMessage()}</div>";
}
?>

<!-- Productos destacados -->
<div class="container mt-5">
  <h4 class="mb-3">Productos destacados</h4>
  <div class="row g-3">
    <?php if (!empty($productos)): ?>
      <?php foreach ($productos as $p): ?>
        <div class="col-md-3">
          <div class="product-card shadow-sm">
            <img class="colorbox" src="<?= htmlspecialchars($p['imagen'] ?: 'img/no-image.png') ?>"
              alt="<?= htmlspecialchars($p['nombre']) ?>"
              style="width:100%;height:200px;object-fit:cover;border-radius:8px;">
            <div class="p-2 text-center">
              <h6 class="text-dark"><?= htmlspecialchars($p['nombre']) ?></h6>
              <p class="fw-bold text-success">Bs. <?= htmlspecialchars($p['precio']) ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No hay productos publicados por este vendedor.</p>
    <?php endif; ?>
  </div>
</div>

<?php include "footer.php" ?>
<script src="./js/global.js"></script>