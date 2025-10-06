<?php

namespace App\Modules\Carrito;

use PDO;

class CarritoModel
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Carrito
    public function crearCarrito(int $idUsuario): ?int
    {
        $sql = "INSERT INTO carrito (id_usuario, fecha_creacion, estado) VALUES (:id_usuario, CURDATE(), 'activo')";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return (int)$this->db->lastInsertId();
        }
        return null;
    }

    public function obtenerCarritoActivoPorUsuario(int $idUsuario): ?array
    {
        $sql = "SELECT id_carrito, id_usuario, fecha_creacion, estado FROM carrito WHERE id_usuario = :id_usuario AND estado = 'activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function actualizarEstadoCarrito(int $idCarrito, string $estado): bool
    {
        $sql = "UPDATE carrito SET estado = :estado WHERE id_carrito = :id_carrito";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
        $stmt->bindParam(':id_carrito', $idCarrito, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Carrito_producto
    public function obtenerItemPorCarritoYProducto(int $idCarrito, int $idProducto): ?array
    {
        $sql = "SELECT id_item, cantidad, precio_unitario, subtotal FROM carrito_producto WHERE id_carrito = :id_carrito AND id_producto = :id_producto";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_carrito', $idCarrito, PDO::PARAM_INT);
        $stmt->bindParam(':id_producto', $idProducto, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function agregarItem(int $idCarrito, int $idProducto, int $cantidad, float $precioUnitario, float $subtotal): ?int
    {
        $sql = "INSERT INTO carrito_producto (id_carrito, id_producto, cantidad, precio_unitario, subtotal) VALUES (:id_carrito, :id_producto, :cantidad, :precio_unitario, :subtotal)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_carrito', $idCarrito, PDO::PARAM_INT);
        $stmt->bindParam(':id_producto', $idProducto, PDO::PARAM_INT);
        $stmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
        $stmt->bindParam(':precio_unitario', $precioUnitario, PDO::PARAM_STR);
        $stmt->bindParam(':subtotal', $subtotal, PDO::PARAM_STR);
        if ($stmt->execute()) {
            return (int)$this->db->lastInsertId();
        }
        return null;
    }

    public function actualizarItem(int $idItem, int $cantidad, float $subtotal): bool
    {
        $sql = "UPDATE carrito_producto SET cantidad = :cantidad, subtotal = :subtotal WHERE id_item = :id_item";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
        $stmt->bindParam(':subtotal', $subtotal, PDO::PARAM_STR);
        $stmt->bindParam(':id_item', $idItem, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function eliminarItem(int $idItem): bool
    {
        $sql = "DELETE FROM carrito_producto WHERE id_item = :id_item";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_item', $idItem, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function vaciarCarrito(int $idCarrito): bool
    {
        $sql = "DELETE FROM carrito_producto WHERE id_carrito = :id_carrito";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_carrito', $idCarrito, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Productos
    public function obtenerProductoConImagen(int $idProducto): ?array
    {
        $sql = "
            SELECT 
                p.id_producto, p.nombre, p.precio, p.stock,
                i.ruta AS imagen_principal
            FROM producto p
            LEFT JOIN imagen i ON p.id_imagen_principal = i.id_imagen
            WHERE p.id_producto = :id_producto AND p.estado = 'activo'
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_producto', $idProducto, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function obtenerCarritoCompleto(int $idCarrito): array
    {
        $sql = "
            SELECT 
                cp.id_item,
                cp.id_producto,
                cp.cantidad,
                cp.precio_unitario,
                cp.subtotal,
                p.nombre,
                p.stock,
                i.ruta AS imagen_principal
            FROM carrito_producto cp
            INNER JOIN producto p ON cp.id_producto = p.id_producto
            LEFT JOIN imagen i ON p.id_imagen_principal = i.id_imagen
            WHERE cp.id_carrito = :id_carrito AND p.estado = 'activo'
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_carrito', $idCarrito, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTotalCarrito(int $idCarrito): float
    {
        $sql = "SELECT COALESCE(SUM(subtotal), 0) AS total FROM carrito_producto WHERE id_carrito = :id_carrito";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_carrito', $idCarrito, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)$result['total'];
    }
}
