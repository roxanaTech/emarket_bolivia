<?php
require_once '../config/database.php';
require_once '../App/Services/ProductoService.php';

use App\Services\ProductoService;

// Suponiendo que $db es tu conexión PDO o MySQLi
$productoService = new ProductoService($db);

$idVendedor = $_GET['id_vendedor'] ?? null; // o $_SESSION['id_vendedor'] si está logueado

if ($idVendedor) {
    $respuesta = $productoService->obtenerProductosPorVendedor((int)$idVendedor);
    echo json_encode($respuesta);
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID de vendedor no proporcionado']);
}
