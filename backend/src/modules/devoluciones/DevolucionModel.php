<?php

namespace App\Modules\Devoluciones;

class DevolucionModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Crea una nueva solicitud de devolución.
     */
    public function crearDevolucion($idDetalleVenta, $idComprador, $cantidad, $motivo, $comprobanteImagen = null)
    {
        $sql = "INSERT INTO devolucion (id_detalle_venta, id_comprador, cantidad, motivo, comprobante_imagen, estado) 
                VALUES (:id_detalle_venta, :id_comprador, :cantidad, :motivo, :comprobante_imagen, 'solicitada')";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_detalle_venta', $idDetalleVenta);
        $stmt->bindParam(':id_comprador', $idComprador);
        $stmt->bindParam(':cantidad', $cantidad);
        $stmt->bindParam(':motivo', $motivo);
        $stmt->bindValue(':comprobante_imagen', $comprobanteImagen ?: null);
        return $stmt->execute() ? $this->db->lastInsertId() : false;
    }

    /**
     * Obtiene una devolución por ID.
     */
    public function obtenerDevolucionPorId($idDevolucion)
    {
        $sql = "SELECT d.*, 
                       dv.id_venta,
                       v.id_vendedor,
                       ven.razon_social AS nombre_vendedor,
                       u.nombres AS nombre_comprador
                FROM devolucion d
                JOIN detalle_venta dv ON d.id_detalle_venta = dv.id_detalle
                JOIN venta v ON dv.id_venta = v.id_venta
                JOIN vendedor ven ON v.id_vendedor = ven.id_vendedor
                JOIN usuario u ON d.id_comprador = u.id_usuario
                WHERE d.id_devolucion = :id_devolucion";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_devolucion', $idDevolucion);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Lista devoluciones del comprador.
     */
    public function listarDevolucionesComprador($idComprador)
    {
        $sql = "SELECT d.id_devolucion, d.estado, d.fecha_solicitud, d.cantidad, d.motivo,
                       p.nombre AS nombre_producto,
                       v.id_venta
                FROM devolucion d
                JOIN detalle_venta dv ON d.id_detalle_venta = dv.id_detalle
                JOIN producto p ON dv.id_producto = p.id_producto
                JOIN venta v ON dv.id_venta = v.id_venta
                WHERE d.id_comprador = :id_comprador
                ORDER BY d.fecha_solicitud DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_comprador', $idComprador);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Lista devoluciones del vendedor (de sus ventas).
     */
    public function listarDevolucionesVendedor($idVendedor)
    {
        $sql = "SELECT d.id_devolucion, d.estado, d.fecha_solicitud, d.cantidad, d.motivo,
                       p.nombre AS nombre_producto,
                       u.nombres AS nombre_comprador,
                       v.id_venta
                FROM devolucion d
                JOIN detalle_venta dv ON d.id_detalle_venta = dv.id_detalle
                JOIN venta v ON dv.id_venta = v.id_venta
                JOIN producto p ON dv.id_producto = p.id_producto
                JOIN usuario u ON d.id_comprador = u.id_usuario
                WHERE v.id_vendedor = :id_vendedor
                ORDER BY d.fecha_solicitud DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_vendedor', $idVendedor);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza el estado de una devolución.
     */
    public function actualizarEstado($idDevolucion, $estado, $comentariosRechazo = null, $devolucionStock = 0, $reembolsoMetodo = 'ninguno')
    {
        $sql = "UPDATE devolucion 
                SET estado = :estado, 
                    comentarios_rechazo = :comentarios_rechazo,
                    devolucion_stock = :devolucion_stock,
                    reembolso_metodo = :reembolso_metodo
                WHERE id_devolucion = :id_devolucion";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindValue(':comentarios_rechazo', $comentariosRechazo ?: null);
        $stmt->bindParam(':devolucion_stock', $devolucionStock, \PDO::PARAM_INT);
        $stmt->bindParam(':reembolso_metodo', $reembolsoMetodo);
        $stmt->bindParam(':id_devolucion', $idDevolucion);
        return $stmt->execute();
    }

    /**
     * Verifica si una devolución pertenece a un comprador.
     */
    public function devolucionPerteneceAComprador($idDevolucion, $idComprador)
    {
        $sql = "SELECT 1 FROM devolucion WHERE id_devolucion = :id_devolucion AND id_comprador = :id_comprador";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_devolucion', $idDevolucion);
        $stmt->bindParam(':id_comprador', $idComprador);
        $stmt->execute();
        return $stmt->fetch() !== false;
    }

    /**
     * Verifica si una devolución pertenece a un vendedor (a través de la venta).
     */
    public function devolucionPerteneceAVendedor($idDevolucion, $idVendedor)
    {
        $sql = "SELECT 1 
                FROM devolucion d
                JOIN detalle_venta dv ON d.id_detalle_venta = dv.id_detalle
                JOIN venta v ON dv.id_venta = v.id_venta
                WHERE d.id_devolucion = :id_devolucion AND v.id_vendedor = :id_vendedor";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_devolucion', $idDevolucion);
        $stmt->bindParam(':id_vendedor', $idVendedor);
        $stmt->execute();
        return $stmt->fetch() !== false;
    }

    /**
     * Obtiene el detalle de venta para validar stock y cantidad.
     */
    public function obtenerDetalleVenta($idDetalleVenta)
    {
        $sql = "SELECT dv.cantidad AS cantidad_vendida, p.stock, p.id_producto
                FROM detalle_venta dv
                JOIN producto p ON dv.id_producto = p.id_producto
                WHERE dv.id_detalle = :id_detalle";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_detalle', $idDetalleVenta);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve stock al producto (usado al procesar devolución).
     */
    public function devolverStock($idProducto, $cantidad)
    {
        $sql = "UPDATE producto SET stock = stock + :cantidad WHERE id_producto = :id_producto";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cantidad', $cantidad);
        $stmt->bindParam(':id_producto', $idProducto);
        return $stmt->execute();
    }
}
