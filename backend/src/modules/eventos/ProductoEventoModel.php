<?php

namespace App\Modules\Eventos;

use App\Utils\ResponseHelper;

use PDO;
use PDOException;


class ProductoEventoModel
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Inserta un nuevo registro de vinculación entre un producto y un evento.
     * @param array $datos Los datos de vinculación (id_producto, id_evento, precio_promocional, fecha_vinculacion).
     * @return bool True si la inserción fue exitosa, false en caso contrario.
     */
    public function vincularProductoAEvento(array $datos)
    {
        try {
            if ($this->productoYaVinculado($datos['id_producto'], $datos['id_evento'])) {
                return ['exito' => false, 'mensaje' => "El producto {$datos['id_producto']} ya está vinculado a otro evento."];
            }
            if ($this->productoVinculadoAlEvento($datos['id_producto'], $datos['id_evento'])) {
                return ['exito' => false, 'mensaje' => "El producto {$datos['id_producto']} ya está vinculado a este evento."];
            }

            $sql = "INSERT INTO producto_evento (id_producto, id_evento, precio_promocional, fecha_vinculacion) 
                VALUES (?,?,?,NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $datos['id_producto'],
                $datos['id_evento'],
                $datos['precio_promocional']
            ]);

            return ['exito' => true, 'mensaje' => "Producto {$datos['id_producto']} vinculado correctamente."];
        } catch (PDOException $e) {
            return ['exito' => false, 'mensaje' => "Error al vincular producto {$datos['id_producto']}: " . $e->getMessage()];
        }
    }


    /**
     * Obtiene los detalles de un vínculo específico por su ID (Leer).
     * @param int $id El ID del registro de la tabla producto_evento.
     * @return array|null Los datos del registro o null si no se encuentra.
     */
    public function obtenerVinculoPorId(int $id_pe)
    {
        try {
            $sql = "SELECT * FROM producto_evento WHERE id_producto_evento = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_pe]);
            $vinculo = $stmt->fetch(PDO::FETCH_ASSOC);
            return $vinculo ?: null;
        } catch (PDOException $e) {
            return ResponseHelper::error("Error al obtener vínculo por ID: " . $e->getMessage());
        }
    }

    /**
     * Obtiene todos los registros de vinculación para un evento dado (Leer).
     * @param int $idEvento El ID del evento.
     * @return array La lista de registros de vinculación.
     */
    public function obtenerVinculosPorEvento(int $idEvento)
    {
        try {
            $sql = "SELECT * FROM producto_evento WHERE id_evento = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idEvento]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ResponseHelper::error("Error al obtener vínculos por evento: " . $e->getMessage());
        }
    }

    /**
     * Actualiza el precio promocional de un vínculo existente (Actualizar).
     * @param int $idVinculo El ID del registro a actualizar.
     * @param float $nuevoPrecio El nuevo precio promocional.
     * @return bool True si la actualización fue exitosa.
     */
    public function actualizarPrecioPromocional(int $idVinculo, float $nuevoPrecio)
    {
        try {
            $sql = "UPDATE producto_evento SET precio_promocional = ? WHERE id_producto_evento = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$nuevoPrecio, $idVinculo]);
        } catch (PDOException $e) {
            return ResponseHelper::error("Error al actualizar precio promocional: " . $e->getMessage());
        }
    }

    /**
     * Elimina un registro de vinculación (Borrar).
     * @param int $id El ID del registro a eliminar.
     * @return bool True si la eliminación fue exitosa.
     */
    public function eliminarVinculo(int $id)
    {
        try {
            $sql = "DELETE FROM producto_evento WHERE id_producto_evento = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return ResponseHelper::error("Error al eliminar vínculo: " . $e->getMessage());
        }
    }

    /**
     * Elimina un registro de vinculación (Borrar).
     * @param int $idEvento El ID del registro a eliminar.
     * @return bool True si la eliminación fue exitosa.
     */
    public function eliminarVinculosEventos(int $idEvento)
    {
        try {
            $sql = "DELETE FROM producto_evento WHERE id_evento = ? AND estado_vinculacion='activo'";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$idEvento]);
        } catch (PDOException $e) {
            return ResponseHelper::error("Error al eliminar vínculo: " . $e->getMessage());
        }
    }

    /**
     * Elimina logicamente un registro de vinculación.
     * @param int $id El ID del registro a eliminar.
     * @return bool True si la eliminación fue exitosa.
     */
    public function cambiaEstadoVinculacion($id, $estado)
    {
        try {
            $sql = "UPDATE producto_evento SET estado_vinculacion = ? WHERE id_producto_evento = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$estado, $id]);
            return $this->obtenerVinculoPorId($id);
        } catch (PDOException $e) {
            return ResponseHelper::error("Error al eliminar vínculo: " . $e->getMessage());
        }
    }
    /**
     * valida si el producto ya esta vinculado.
     * @param int $id El ID del producto.
     * @return bool true si la cantidad de columnas afectadas es mayor a 0
     */
    public function productoYaVinculado($idProducto, $idEvento)
    {
        $sql = "SELECT COUNT(*) FROM producto_evento WHERE id_producto = ? AND id_evento != ? AND estado_vinculacion='activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idProducto, $idEvento]);
        return $stmt->fetchColumn() > 0;
    }
    /**
     * valida si el producto ya esta vinculado al evento.
     * @param int $id El ID del producto.
     * @return bool true si la cantidad de columnas afectadas es mayor a 0
     */
    public function productoVinculadoAlEvento($idProducto, $idEvento)
    {
        $sql = "SELECT COUNT(*) FROM producto_evento WHERE id_producto = ? AND id_evento = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idProducto, $idEvento]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * obtiene los datos de vinculacion a un evento del producto .
     * @param int $id El ID del producto.
     * @return array las columnas afectadas
     */
    public function obtenerVinculacionActivaPorProducto(int $idProducto): array|false
    {
        try {
            $sql = "SELECT pe.precio_promocional, pe.fecha_vinculacion, e.id_evento, e.nombre_evento, e.tipo_aplicacion, e.valor_descuento, e.condiciones, e.fecha_inicio, e.fecha_vencimiento
                FROM producto_evento pe
                JOIN evento e ON pe.id_evento = e.id_evento
                WHERE pe.id_producto = ? AND pe.estado_vinculacion = 'activo' AND e.estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idProducto]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener vinculación activa: " . $e->getMessage());
            return false;
        }
    }
}
