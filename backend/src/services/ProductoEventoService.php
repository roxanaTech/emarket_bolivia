<?php

namespace App\Services;

use App\Modules\Eventos\ProductoEventoModel;
use App\Modules\Eventos\EventoModel;
use App\Modules\Productos\ProductoModel;
use App\Utils\ResponseHelper;
use DateTime;
use Exception;
use OpenApi\Annotations\Response;
use PhpParser\Node\Stmt\Return_;

class ProductoEventoService
{
    private $productoEventoModel;
    private $eventoModel;
    private $productoModel;

    /**
     * Constructor que inicializa las dependencias de los modelos.
     */
    public function __construct($pdo)
    {
        $this->productoEventoModel = new ProductoEventoModel($pdo);
        $this->eventoModel = new EventoModel($pdo);
        $this->productoModel = new ProductoModel($pdo);
    }

    /**
     * Vincula un array de productos a un evento, aplicando la lógica de negocio.
     * @param int $idEvento El ID del evento.
     * @param array $idProductos Un array de IDs de productos.
     * @return bool True si la vinculación fue exitosa, false en caso de error.
     */
    public function vincularProductos(int $idEvento, array $idProductos)
    {
        $resultados = [
            'vinculados' => [],
            'errores' => []
        ];

        try {
            $evento = $this->eventoModel->obtenerEventoPorId($idEvento);
            if (!$evento) {
                return ResponseHelper::error("El evento no existe.", 400);
            }

            $fechaActual = new DateTime();
            $fechaInicio = new DateTime($evento['fecha_inicio']);
            $fechaVencimiento = new DateTime($evento['fecha_vencimiento']);

            if ($evento['estado'] !== 'activo' || $fechaActual < $fechaInicio || $fechaActual > $fechaVencimiento) {
                return ResponseHelper::error("El evento no está activo o fuera de su rango de fechas.", 400);
            }

            foreach ($idProductos as $idProducto) {
                $producto = $this->productoModel->recuperarProducto($idProducto);
                if (!$producto) {
                    $resultados['errores'][] = "El producto $idProducto no existe.";
                    continue;
                }

                $precioOriginal = $producto['precio'];
                $precioPromocional = $this->calcularPrecioPromocional(
                    $evento['valor_descuento'],
                    $evento['tipo_aplicacion'],
                    $precioOriginal
                );

                $datosVinculacion = [
                    'id_producto' => $idProducto,
                    'id_evento' => $idEvento,
                    'precio_promocional' => $precioPromocional
                ];

                $resultado = $this->productoEventoModel->vincularProductoAEvento($datosVinculacion);

                if ($resultado['exito']) {
                    $resultados['vinculados'][] = $idProducto;
                } else {
                    $resultados['errores'][] = $resultado['mensaje'];
                }
            }

            return $resultados;
        } catch (Exception $e) {
            return ResponseHelper::error("Error en el servicio de vinculación: " . $e->getMessage());
        }
    }


    /**
     * Obtiene los detalles de un vínculo específico (Leer).
     * @param int $id El ID del vínculo.
     * @return array|null Los datos del vínculo.
     */
    public function obtenerVinculo(int $id)
    {
        return $this->productoEventoModel->obtenerVinculoPorId($id);
    }

    /**
     * Obtiene todos los vínculos de un evento (Leer).
     * @param int $idEvento El ID del evento.
     * @return array La lista de vínculos.
     */
    public function obtenerVinculosPorEvento(int $idEvento)
    {
        return $this->productoEventoModel->obtenerVinculosPorEvento($idEvento);
    }

    /**
     * Actualiza el precio promocional de un vínculo (Actualizar).
     * @param int $idEvento El ID del vínculo.
     * @param float $nuevoIndicador El nuevo precio.
     * @return bool True si la actualización fue exitosa.
     */
    public function actualizarPrecio(int $idEvento, float $nuevoIndicador)
    {
        if ($nuevoIndicador < 0) return false;
        return $this->productoEventoModel->actualizarPrecioPromocional($idEvento, $nuevoIndicador);
    }

    /**
     * Elimina un vínculo de producto-evento (Borrar).
     * @param int $id El ID del vínculo.
     * @return bool True si la eliminación fue exitosa.
     */
    public function eliminarVinculo(int $id)
    {
        return $this->productoEventoModel->eliminarVinculo($id);
    }
    /**
     * cambiar estado de un vinculo.
     * @param int $id El ID del vínculo.
     * @return bool True si la eliminación fue exitosa.
     */
    public function cambiarEstadoVinculo(int $id, string $estado)
    {
        return $this->productoEventoModel->cambiaEstadoVinculacion($id,$estado);
    }
    /**
     * Elimina un vínculo de producto-evento (Borrar).
     * @param int $id El ID del vínculo.
     * @return bool True si la eliminación fue exitosa.
     */
    public function eliminarVinculosEventos(int $idEvento)
    {
        return $this->productoEventoModel->eliminarVinculo($idEvento);
    }
    /**
     * Calcular precio promocional segun tipo de aplicacion
     * @param  $valor_descuento
     * @param $tipo_aplicacion
     * @param $precioOriginal
     * @return float $precio_promocional.
     */
    public function calcularPrecioPromocional($valor_descuento, $tipo_aplicacion, $precioOriginal)
    {
        switch ($tipo_aplicacion) {
            case 'porcentaje':
                return $precioOriginal * (1 - ($valor_descuento / 100));
                break;
            case 'monto_fijo':
                return max(0, $precioOriginal - $valor_descuento);
                break;
            case 'condicional':
                // Lógica de descuento condicional más compleja, por ejemplo, si el precio > X
                if ($precioOriginal > 100) {
                    return max(0, $precioOriginal - $valor_descuento);
                }
                break;
            default:
                return false;
                break;
        }
    }
}
