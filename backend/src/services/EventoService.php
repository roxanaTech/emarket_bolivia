<?php

namespace App\Services;

use App\Modules\Vendedores\VendedorModel;
use App\Modules\Eventos\EventoModel;
use App\Utils\ResponseHelper;
use App\Modules\Eventos\ProductoEventoModel;
use App\Services\ProductoEventoService;


class EventoService
{
    private $eventoModel;
    private $vendedorModel;
    private $productoEventoModel;
    private $productoEventoService;

    /**
     * Constructor que inyecta la dependencia del modelo.
     */
    public function __construct($db)
    {
        $this->eventoModel = new EventoModel($db);
        $this->vendedorModel = new VendedorModel($db);
        $this->productoEventoModel = new ProductoEventoModel($db);
        $this->productoEventoService = new ProductoEventoService($db);
    }

    /**
     * Valida los datos y crea un nuevo evento.
     * @param array $datosEvento Datos del evento.
     * @return int|false El ID del nuevo evento o false si falla.
     */
    public function crearEvento($datosEvento, $idUsuario)
    {

        $idVendedor = $this->vendedorModel->recuperarIdVendedorPorIdUsuario($idUsuario);
        if (!$idVendedor) {
            ResponseHelper::error("Error al obtener el id del vendedor.", 500);
        }

        return $this->eventoModel->crearEvento($datosEvento, $idVendedor);
    }

    /**
     * Obtiene un evento por su ID.
     * @param int $idEvento El ID del evento.
     * @return array|null Los datos del evento o null.
     */
    public function obtenerEvento(int $idEvento)
    {
        return $this->eventoModel->obtenerEventoPorId($idEvento);
    }


    /**
     * Actualiza el estado de un evento.
     * @param int $idEvento El ID del evento.
     * @param string $estado El nuevo estado.
     * @return bool True si la actualización fue exitosa.
     */
    public function actualizarEstado(int $idEvento, string $estado)
    {
        $estadosValidos = ['activo', 'inactivo', 'finalizado', 'cancelado'];
        if (!in_array($estado, $estadosValidos)) {
            return false;
        }

        return $this->eventoModel->actualizarEstadoEvento($idEvento, $estado);
    }

    /**
     * Obtiene una lista de eventos con filtros.
     * @param int $idVendedor El ID del vendedor.
     * @param array $filtros Filtros de búsqueda opcionales.
     * @return array La lista de eventos.
     */
    public function obtenerEventosConFiltros(int $idUsuario, array $filtros)
    {
        $idVendedor = $this->vendedorModel->recuperarIdVendedorPorIdUsuario($idUsuario);
        if (!empty($filtros)) {
            return $this->eventoModel->filtrarEventos($idVendedor, $filtros);
        } else {
            return $this->eventoModel->listarEventosPorVendedor($idVendedor);
        }
    }

    /**
     * Elimina un evento.
     * @param int $idEvento El ID del evento a eliminar.
     * @return bool True si la eliminación fue exitosa.
     */
    public function eliminarEvento(int $idEvento)
    {
        return $this->eventoModel->eliminarEvento($idEvento);
    }

    /**
     * Actualiza múltiples campos de un evento, recalculando precios promocionales si es necesario.
     * @param int $idEvento El ID del evento a actualizar.
     * @param array $datos Los datos a actualizar (clave-valor).
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     */
    public function actualizarEvento(int $idEvento, array $datos)
    {
        // 1. Obtener el evento actual para comparar
        $eventoAnterior = $this->eventoModel->obtenerEventoPorId($idEvento);
        if (!$eventoAnterior) {
            return false;
        }

        // 2. Realizar la actualización del evento
        $actualizado = $this->eventoModel->actualizarEvento($idEvento, $datos);

        if ($actualizado) {
            // 3. Verificar si el tipo de aplicación o valor de descuento ha cambiado
            $tipoCambio = isset($datos['tipo_aplicacion']) && $datos['tipo_aplicacion'] !== $eventoAnterior['tipo_aplicacion'];
            $valorCambio = isset($datos['valor_descuento']) && $datos['valor_descuento'] !== $eventoAnterior['valor_descuento'];

            if ($tipoCambio || $valorCambio) {
                // 4. Si hay cambios, obtener todos los productos vinculados y recalcular el precio
                $productosVinculados = $this->eventoModel->obtenerProductosVinculados($idEvento);

                $eventoActualizado = $this->eventoModel->obtenerEventoPorId($idEvento);

                foreach ($productosVinculados as $vinculo) {
                    $precioOriginal = $vinculo['precio_original'];
                    $precioPromocional = $this->productoEventoService->calcularPrecioPromocional(
                        $eventoActualizado['valor_descuento'],
                        $eventoActualizado['tipo_aplicacion'],
                        $precioOriginal
                    );

                    // 5. Actualizar el precio promocional en la tabla producto_evento
                    $this->productoEventoModel->actualizarPrecioPromocional($vinculo['vinculo_id'], $precioPromocional);
                }
            }
        }

        return $actualizado;
    }
}
