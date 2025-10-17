<?php

namespace App\Modules\Ventas;

class VentaModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Crea un nuevo registro de venta.
     */
    public function crearVenta($idVendedor, $idComprador, $tipoPago, $totalVenta, $tipoEntrega, $comprobantePago = null, $direccionEntrega = null, $telefonoContacto = null)
    {
        $sql = "INSERT INTO venta (id_vendedor, id_comprador, tipo_pago, total_venta, estado, tipo_entrega, comprobante_pago, direccion_entrega, telefono_contacto) 
                VALUES (:id_vendedor, :id_comprador, :tipo_pago, :total_venta, 'pendiente', :tipo_entrega, :comprobante_pago, :direccion_entrega, :telefono_contacto)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_vendedor', $idVendedor);
        $stmt->bindParam(':id_comprador', $idComprador);
        $stmt->bindParam(':tipo_pago', $tipoPago);
        $stmt->bindParam(':total_venta', $totalVenta);
        $stmt->bindParam(':tipo_entrega', $tipoEntrega);
        $stmt->bindValue(':comprobante_pago', $comprobantePago ?: null);
        $stmt->bindValue(':direccion_entrega', $direccionEntrega ?: null);
        $stmt->bindValue(':telefono_contacto', $telefonoContacto ?: null);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Crea un detalle de venta.
     */
    public function crearDetalleVenta($idVenta, $idProducto, $cantidad, $precioUnit, $subtotal)
    {
        $sql = "INSERT INTO detalle_venta (id_producto, id_venta, cantidad, precio_unit, subtotal) 
                VALUES (:id_producto, :id_venta, :cantidad, :precio_unit, :subtotal)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_producto', $idProducto);
        $stmt->bindParam(':id_venta', $idVenta);
        $stmt->bindParam(':cantidad', $cantidad);
        $stmt->bindParam(':precio_unit', $precioUnit);
        $stmt->bindParam(':subtotal', $subtotal);
        return $stmt->execute();
    }

    /**
     * Obtiene una venta por ID, incluyendo datos del comprador y vendedor.
     */
    public function obtenerVentaPorId($idVenta)
    {
        $sql = "SELECT v.*, 
                       u.nombres AS nombre_comprador, u.email AS email_comprador,
                       ven.razon_social AS nombre_vendedor
                FROM venta v
                JOIN usuario u ON v.id_comprador = u.id_usuario
                JOIN vendedor ven ON v.id_vendedor = ven.id_vendedor
                WHERE v.id_venta = :id_venta";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_venta', $idVenta);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los detalles de una venta.
     */
    public function obtenerDetallesVenta($idVenta)
    {
        $sql = "SELECT dv.*, p.nombre AS nombre_producto, p.id_imagen_principal
                FROM detalle_venta dv
                JOIN producto p ON dv.id_producto = p.id_producto
                WHERE dv.id_venta = :id_venta";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_venta', $idVenta);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Lista ventas por comprador (usuario).
     */
    public function listarVentasPorComprador($idComprador)
    {
        $sql = "SELECT v.id_venta, v.fecha, v.total_venta, v.estado, v.tipo_pago, v.tipo_entrega,
                       ven.razon_social AS nombre_vendedor
                FROM venta v
                LEFT JOIN vendedor ven ON v.id_vendedor = ven.id_vendedor
                WHERE v.id_comprador = :id_comprador
                ORDER BY v.fecha DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_comprador', $idComprador);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Lista ventas por vendedor.
     */
    public function listarVentasPorVendedor($idVendedor)
    {
        $sql = "SELECT v.id_venta, v.fecha, v.total_venta, v.estado, v.tipo_pago, v.tipo_entrega,
                       u.nombres AS nombre_comprador, u.email AS email_comprador
                FROM venta v
                JOIN usuario u ON v.id_comprador = u.id_usuario
                WHERE v.id_vendedor = :id_vendedor
                ORDER BY v.fecha DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_vendedor', $idVendedor);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza el estado de una venta.
     */
    public function actualizarEstadoVenta($idVenta, $estado)
    {
        $sql = "UPDATE venta SET estado = :estado WHERE id_venta = :id_venta";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id_venta', $idVenta);
        return $stmt->execute();
    }

    /**
     * Actualiza el comprobante de pago.
     */
    public function actualizarComprobantePago($idVenta, $comprobante)
    {
        $sql = "UPDATE venta SET comprobante_pago = :comprobante WHERE id_venta = :id_venta";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':comprobante', $comprobante);
        $stmt->bindParam(':id_venta', $idVenta);
        return $stmt->execute();
    }

    /**
     * Verifica si una venta pertenece a un comprador.
     */
    public function ventaPerteneceAComprador($idVenta, $idComprador)
    {
        $sql = "SELECT 1 FROM venta WHERE id_venta = :id_venta AND id_comprador = :id_comprador";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_venta', $idVenta);
        $stmt->bindParam(':id_comprador', $idComprador);
        $stmt->execute();
        return $stmt->fetch() !== false;
    }

    /**
     * Verifica si una venta pertenece a un vendedor.
     */
    public function ventaPerteneceAVendedor($idVenta, $idVendedor)
    {
        $sql = "SELECT 1 FROM venta WHERE id_venta = :id_venta AND id_vendedor = :id_vendedor";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_venta', $idVenta);
        $stmt->bindParam(':id_vendedor', $idVendedor);
        $stmt->execute();
        return $stmt->fetch() !== false;
    }
}
