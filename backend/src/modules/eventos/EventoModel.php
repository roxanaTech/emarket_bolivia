<?php

namespace App\Modules\Eventos;

use App\Utils\ResponseHelper;
use PDO;
use PDOException;

class EventoModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Inserta un nuevo evento en la base de datos.
     * @param array $datosEvento Datos del evento a crear.
     * @return int|false El ID del evento insertado o false si falla.
     */
    public function crearEvento($data, $idVendedor)
    {
        try {
            // Ejemplo de consulta de inserción, ajusta los campos según tu tabla
            $sql = "INSERT INTO evento (id_vendedor, nombre_evento, tipo_aplicacion, valor_descuento, condiciones, fecha_inicio, fecha_vencimiento, tipo_evento) 
                    VALUES (?, ?, ?, ?, ?, NOW(),?,? )";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $idVendedor,
                $data['nombre_evento'],
                $data['tipo_aplicacion'],
                $data['valor_descuento'],
                $data['condiciones'],
                $data['fecha_vencimiento'],
                $data['tipo_evento']
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            // Manejo de errores, por ejemplo, loguear el error
            ResponseHelper::error("Error al crear el evento. Intente de nuevo." . $e->getMessage(), 500);

            return false;
        }
    }

    /**
     * Obtiene los datos de un evento por su ID.
     * @param int $idEvento El ID del evento a buscar.
     * @return array|null Los datos del evento o null si no se encuentra.
     */
    public function obtenerEventoPorId(int $idEvento)
    {
        try {
            $sql = "SELECT * FROM evento WHERE id_evento = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idEvento]);
            $evento = $stmt->fetch(PDO::FETCH_ASSOC);
            return $evento ? $evento : null;
        } catch (PDOException $e) {
            error_log("Error al obtener evento por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualiza múltiples campos de un evento.
     * @param int $idEvento El ID del evento a actualizar.
     * @param array $datos Los datos a actualizar (clave-valor).
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     */
    public function actualizarEvento(int $idEvento, array $datos)
    {
        try {
            $setClauses = [];
            $params = ['id' => $idEvento];
            foreach ($datos as $key => $value) {
                // Previene la actualización del ID y sanitiza las claves
                if ($key !== 'id') {
                    $setClauses[] = "$key = :$key";
                    $params[$key] = $value;
                }
            }
            if (empty($setClauses)) {
                return false; // No hay campos para actualizar
            }
            $sql = "UPDATE evento SET " . implode(', ', $setClauses) . " WHERE id_evento = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error al actualizar evento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un evento de la base de datos.
     * @param int $idEvento El ID del evento a eliminar.
     * @return bool True si la eliminación fue exitosa, false en caso contrario.
     */
    public function eliminarEvento(int $idEvento)
    {
        try {
            $sql = "DELETE FROM evento WHERE id_evento = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$idEvento]);
        } catch (PDOException $e) {
            error_log("Error al eliminar evento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza el estado de un evento y la fecha_inicio si el evento se activa.
     * @param int $idEvento El ID del evento.
     * @param string $estado El nuevo estado.
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     */
    public function actualizarEstadoEvento(int $idEvento, string $estado)
    {
        if ($estado == "activo") {
            $sql = "UPDATE evento SET fecha_inicio = NOW() WHERE id_evento = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idEvento]);
        }
        try {
            $sql = "UPDATE evento SET estado = ? WHERE id_evento = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$estado, $idEvento]);
        } catch (PDOException $e) {
            error_log("Error al actualizar estado del evento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los eventos de un vendedor específico.
     * @param int $idVendedor El ID del vendedor.
     * @return array La lista de eventos.
     */
    public function listarEventosPorVendedor(int $idVendedor)
    {
        try {
            $sql = "SELECT * FROM evento WHERE id_vendedor = ? ORDER BY fecha_inicio DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idVendedor]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al listar eventos por vendedor: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Filtra eventos de un vendedor por subcategoría y/o rango de fechas.
     * @param int $idVendedor El ID del vendedor.
     * @param array $filtros Arreglo con filtros opcionales (subcategoria, fecha_inicio, fecha_vencimiento).
     * @return array La lista de eventos filtrados.
     */
    public function filtrarEventos(int $idVendedor, array $filtros)
    {
        try {
            $sql = "SELECT * FROM evento WHERE id_vendedor = :id_vendedor";
            $params = ['id_vendedor' => $idVendedor];

            if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_vencimiento'])) {
                $sql .= " AND fecha_inicio BETWEEN :fecha_inicio AND :fecha_vencimiento";
                $params['fecha_inicio'] = $filtros['fecha_inicio'];
                $params['fecha_vencimiento'] = $filtros['fecha_vencimiento'];
            } elseif (!empty($filtros['fecha_inicio'])) {
                $sql .= " AND fecha_inicio >= :fecha_inicio";
                $params['fecha_inicio'] = $filtros['fecha_inicio'];
            } elseif (!empty($filtros['fecha_vencimiento'])) {
                $sql .= " AND fecha_vencimiento <= :fecha_vencimiento";
                $params['fecha_vencimiento'] = $filtros['fecha_vencimiento'];
            }

            $sql .= " ORDER BY fecha_inicio DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al filtrar eventos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los productos vinculados a un evento específico.
     * @param int $idEvento El ID del evento.
     * @return array La lista de productos vinculados con sus datos.
     */
    public function obtenerProductosVinculados(int $idEvento)
    {
        try {
            $sql = "SELECT pe.id_producto_evento AS vinculo_id, pe.precio_promocional, p.id AS producto_id, p.precio AS precio_original
                    FROM producto_evento pe
                    JOIN producto p ON pe.id_producto = p.id
                    WHERE pe.id_evento = :id_evento 
                    AND estado_vinculacion='activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_evento' => $idEvento]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener productos vinculados: " . $e->getMessage());
            return [];
        }
    }
}
