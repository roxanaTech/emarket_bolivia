<?php

// clase de conexión a la base de datos
require_once __DIR__ . '/../config/database.php';


// 1. Consulta para actualizar el estado del evento a 'finalizado'
$sqlEventosVencidos = "UPDATE evento SET estado = 'finalizado' 
                       WHERE fecha_vencimiento < CURDATE() AND estado = 'activo'";
try {
    $stmt = $pdo->prepare($sqlEventosVencidos);
    $stmt->execute();
    $filasAfectadas = $stmt->rowCount();
    error_log("Se actualizaron $filasAfectadas eventos a 'finalizado'.");

    // 2. Actualiza el estado_vinculacion de los productos de esos eventos
    $sqlProductosExpirados = "UPDATE producto_evento SET estado_vinculacion = 'expirado'
                             WHERE id_evento IN (SELECT id_evento FROM evento WHERE estado = 'finalizado')";

    $stmtProductos = $pdo->prepare($sqlProductosExpirados);
    $stmtProductos->execute();
    $filasProductos = $stmtProductos->rowCount();
    error_log("Se actualizaron $filasProductos vínculos de productos a 'expirado'.");
} catch (PDOException $e) {
    error_log("Error en la actualización de estados: " . $e->getMessage());
}
file_put_contents(__DIR__ . '/../logs/actualizacion.log', date('Y-m-d H:i:s') . " - Eventos: $filasAfectadas, Productos: $filasProductos\n", FILE_APPEND);
echo "Tarea de actualización de estados finalizada.";
