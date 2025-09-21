<?php

namespace App\Modules\Eventos;

use App\Services\EventoService;
use App\Modules\Eventos\ProductoEventoController;
use App\Utils\ResponseHelper;
use App\Utils\Validator;

use Exception;

class EventoController
{
    private $eventoService;
    private $productoEventoController;

    public function __construct($db)
    {
        $this->eventoService = new EventoService($db);
        $this->productoEventoController = new ProductoEventoController($db);
    }

    /**
     * Maneja la creación de un nuevo evento (POST).
     */
    public function registrar($payload, $data)
    {
        $idUsuario = $payload->sub;
        try {
            $idEvento = $this->eventoService->crearEvento($data, $idUsuario);

            if ($idEvento) {
                return ResponseHelper::success("Evento creado exitosamente.", 201);
            } else {
                return ResponseHelper::error("Error al crear el evento. Intente de nuevo.", 500);
            }
        } catch (Exception $e) {
            return ResponseHelper::error("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }


    /**
     * Obtiene los detalles de un evento por su ID (GET).
     * Endpoint: /eventos/obtenerPorId?id=123
     */
    public function obtenerPorId($data)
    {
        try {
            if (!$data['id_evento']) {
                ResponseHelper::error("ID de evento no proporcionado.", 400);
                return;
            }
            return $this->eventoService->obtenerEvento((int)$data['id_evento']);
        } catch (Exception $e) {
            return ResponseHelper::error("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Actualiza los datos de un evento existente (PUT).
     * Endpoint: /eventos/actualizar
     */
    public function actualizar($data)
    {
        try {

            if (!isset($data['id_evento']) || empty($data)) {
                ResponseHelper::error("Datos de actualización o ID de evento no proporcionado.", 400);
                return;
            }

            $idEvento = (int)$data['id_evento'];
            unset($data['id_evento']); // Evitar actualizar el ID

            $actualizado = $this->eventoService->actualizarEvento($idEvento, $data);
            if ($actualizado) {
                return ResponseHelper::success('Evento actualizado exitosamente.', ['id_evento' => $idEvento]);
            } else {
                return ResponseHelper::error("Error al actualizar el evento o no hay cambios.", 500);
            }
        } catch (Exception $e) {
            return ResponseHelper::error("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Elimina un evento (DELETE).
     * Endpoint: /eventos/eliminar?id=123
     */
    public function eliminar($data)
    {
        try {
            $id = $data['id_evento'] ?? null;

            if (!$id) {
                return ResponseHelper::error("ID de evento no proporcionado.", 400);
            }

            $eliminado = $this->eventoService->eliminarEvento((int)$id);

            if ($eliminado) {
                return ResponseHelper::success("Evento eliminado exitosamente.");
            } else {
                ResponseHelper::error("No se pudo eliminar el evento. Verifique el ID.", 404);
            }
        } catch (Exception $e) {
            return ResponseHelper::error("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Lista los eventos de un vendedor con filtros opcionales (GET).
     * Endpoint: /eventos/listarMisEventos?id_vendedor=1&subcategoria=musica
     */
    public function listarMisEventos($payload, $filtros)
    {
        try {
            $idUsuario = $payload->sub;

            return $this->eventoService->obtenerEventosConFiltros($idUsuario, $filtros);
        } catch (Exception $e) {
            return ResponseHelper::error("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Cambia el estado de un evento (PUT/POST).
     * Endpoint: /eventos/cambiarEstado
     */
    public function cambiarEstado($data)
    {
        try {

            if (!isset($data['id_evento']) || !isset($data['estado'])) {
                return ResponseHelper::error("ID y estado de evento requeridos.", 400);
            }

            $id = (int)$data['id_evento'];
            $estado = $data['estado'];

            $actualizado = $this->eventoService->actualizarEstado($id, $estado);

            if ($actualizado) {
                return ResponseHelper::success(
                    "Estado del evento actualizado exitosamente.",
                    $estado
                );
            } else {
                return ResponseHelper::error("No se pudo actualizar el estado del evento. Verifique los datos.", 500);
            }
        } catch (Exception $e) {
            return ResponseHelper::error("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }
}
