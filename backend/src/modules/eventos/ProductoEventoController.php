<?php

namespace App\Modules\Eventos;

use App\Services\ProductoEventoService;
use App\Utils\ResponseHelper;
use App\Utils\Validator;

use Exception;


class ProductoEventoController
{
    private $productoEventoService;

    public function __construct($pdo)
    {
        $this->productoEventoService = new ProductoEventoService($pdo);
    }

    /**
     * Maneja la petición POST para vincular productos a un evento.
     * Espera un JSON en el cuerpo de la petición.
     */
    public function vincularProductosAEvento($data)
    {
        try {
            if (!is_array($data['id_productos'])) {
                return ResponseHelper::error("Datos de petición inválidos. Se requiere un array de id_productos.", 400);
            }

            $reglasVendedor = ['id_evento' => ['requerido']];
            $errores = Validator::validarCampos($data, $reglasVendedor);
            if (!empty($errores)) {
                return ResponseHelper::validationError($errores);
            }

            $idEvento = (int)$data['id_evento'];
            $idProductos = $data['id_productos'];

            $resultado = $this->productoEventoService->vincularProductos($idEvento, $idProductos);

            if (isset($resultado['vinculados']) || isset($resultado['errores'])) {
                return ResponseHelper::success([
                    'mensaje' => 'Proceso de vinculación completado.',
                    'vinculados' => $resultado['vinculados'],
                    'errores' => $resultado['errores'],
                    'evento' => $this->productoEventoService->obtenerVinculosPorEvento($idEvento)
                ], 200);
            }

            return ResponseHelper::error("No se pudo vincular los productos al evento.", 400);
        } catch (Exception $e) {
            return ResponseHelper::error("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene un vínculo por su ID.
     */
    public function obtener($id)
    {
        try {
            $reglasVendedor = [
                'id_evento' => ['requerido']
            ];

            $errores = Validator::validarCampos($id, $reglasVendedor);
            if (!empty($errores)) {
                return ResponseHelper::validationError($errores);
            }

            $vinculo = $this->productoEventoService->obtenerVinculo((int)$id);
            if ($vinculo) {
                return ResponseHelper::success("Vínculo encontrado.", $vinculo);
            } else {
                return ResponseHelper::error("Vínculo no encontrado.", 404);
            }
        } catch (Exception $e) {
            return ResponseHelper::error("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Lista todos los vínculos de un evento.
     */
    public function listarPorEvento($idEvento)
    {
        try {
            $reglasVendedor = [
                'id_evento' => ['requerido']
            ];
            $errores = Validator::validarCampos($idEvento, $reglasVendedor);
            if (!empty($errores)) {
                return ResponseHelper::validationError($errores);
            }
            $vinculos = $this->productoEventoService->obtenerVinculosPorEvento((int)$idEvento);
            return ResponseHelper::success("Vínculos listados exitosamente.", $vinculos);
        } catch (Exception $e) {
            return ResponseHelper::error("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Actualiza el precio de un vínculo (PUT).
     */
    public function actualizar($idEvento, $data)
    {
        try {
            if (!isset($idEvento) || !isset($data)) {
                return ResponseHelper::error("ID y nuevo indicador requeridos.", 400);
            }
            $exito = $this->productoEventoService->actualizarPrecio($idEvento, $data);
            if ($exito) {
                return ResponseHelper::success("Precio actualizado exitosamente.");
            } else {
                return ResponseHelper::error("No se pudo actualizar el precio.", 400);
            }
        } catch (Exception $e) {
            return ResponseHelper::error("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Elimina un vínculo (DELETE).
     */
    public function eliminar($data)
    {
        try {
            $id = $data['id_vinculo'] ?? null;
            if (!$id) {
                return ResponseHelper::error("ID de vínculo no proporcionado.", 400);
            }
            $exito = $this->productoEventoService->eliminarVinculo((int)$id);
            if ($exito) {
                return ResponseHelper::success("Vínculo eliminado exitosamente.");
            } else {
                return ResponseHelper::error("Vínculo no encontrado o no se pudo eliminar.", 404);
            }
        } catch (Exception $e) {
            return ResponseHelper::error("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Cambiar estado de un vínculo.
     */
    public function cambiarEstadoVinculo($data)
    {
        try {
            $id = $data['id_vinculo'] ?? null;
            if (!$id) {
                return ResponseHelper::error("ID de vínculo no proporcionado.", 400);
            }
            return $this->productoEventoService->cambiarEstadoVinculo((int)$id, $data['estado']);
        } catch (Exception $e) {
            return ResponseHelper::error("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }
}
