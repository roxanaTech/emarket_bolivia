<?php

namespace App\Services;

use App\Utils\ResponseHelper;


class VentaService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Valida el número de tarjeta con el algoritmo de Luhn.
     */
    public function validarTarjetaLuhn($numero)
    {
        $numero = preg_replace('/\D/', '', $numero);
        if (strlen($numero) < 13 || strlen($numero) > 19) {
            return false;
        }

        $suma = 0;
        $par = false;
        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $digito = (int)$numero[$i];
            if ($par) {
                $digito *= 2;
                if ($digito > 9) $digito -= 9;
            }
            $suma += $digito;
            $par = !$par;
        }
        return ($suma % 10 === 0);
    }

    /**
     * Valida fecha de expiración (formato MM/AA).
     */
    public function validarFechaExpiracion($fecha)
    {
        if (!preg_match('/^\d{2}\/\d{2}$/', $fecha)) {
            return false;
        }
        [$mes, $anio] = explode('/', $fecha);
        $mes = (int)$mes;
        $anio = (int)$anio;
        if ($mes < 1 || $mes > 12) return false;
        $anioCompleto = $anio < 50 ? 2000 + $anio : 1900 + $anio;
        $fechaActual = new \DateTime();
        $fechaExpiracion = new \DateTime("$anioCompleto-$mes-01");
        return $fechaExpiracion >= $fechaActual;
    }

    /**
     * Valida CVV (3 o 4 dígitos).
     */
    public function validarCVV($cvv)
    {
        return preg_match('/^\d{3,4}$/', $cvv) === 1;
    }

    /**
     * Valida stock de productos antes de crear venta.
     */
    public function validarStockProductos($items)
    {
        foreach ($items as $item) {
            $sql = "SELECT stock FROM producto WHERE id_producto = :id_producto AND estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_producto', $item['id_producto']);
            $stmt->execute();
            $producto = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$producto || $producto['stock'] < $item['cantidad']) {
                return false;
            }
        }
        return true;
    }

    /**
     * Obtiene el ID del vendedor a partir de un producto (asume que todos los items son del mismo vendedor).
     */
    public function obtenerIdVendedorDeItems($items)
    {
        if (empty($items)) return null;
        $idProducto = $items[0]['id_producto'];
        $sql = "SELECT id_vendedor FROM producto WHERE id_producto = :id_producto";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_producto', $idProducto);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? $row['id_vendedor'] : null;
    }

    /**
     * Verifica que todos los productos pertenezcan al mismo vendedor.
     */
    public function todosMismoVendedor($items)
    {
        if (empty($items)) return true;
        $idVendedor = $this->obtenerIdVendedorDeItems([$items[0]]);
        foreach ($items as $item) {
            $sql = "SELECT id_vendedor FROM producto WHERE id_producto = :id_producto";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_producto', $item['id_producto']);
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row || $row['id_vendedor'] != $idVendedor) {
                return false;
            }
        }
        return true;
    }
    /**
     * Reduce el stock de los productos asociados a una venta.
     */
    public function reducirStockPorVenta($idVenta)
    {
        // Iniciar transacción
        $this->db->beginTransaction();

        try {
            // Obtener los detalles de la venta
            $sqlDetalles = "SELECT id_producto, cantidad FROM detalle_venta WHERE id_venta = :id_venta";
            $stmt = $this->db->prepare($sqlDetalles);
            $stmt->bindParam(':id_venta', $idVenta);
            $stmt->execute();
            $detalles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($detalles as $detalle) {
                $sqlUpdate = "UPDATE producto 
                          SET stock = stock - :cantidad 
                          WHERE id_producto = :id_producto AND stock >= :cantidad";
                $stmtUpdate = $this->db->prepare($sqlUpdate);
                $stmtUpdate->bindParam(':cantidad', $detalle['cantidad']);
                $stmtUpdate->bindParam(':id_producto', $detalle['id_producto']);

                if (!$stmtUpdate->execute() || $stmtUpdate->rowCount() === 0) {
                    throw new \Exception("Stock insuficiente para el producto {$detalle['id_producto']}");
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("Error al reducir stock para venta $idVenta: " . $e->getMessage());
            return false;
        }
    }
}
