<?php

namespace App\Services;

use App\Modules\Carrito\CarritoModel;
use App\Utils\ResponseHelper;
use PDO;

class CarritoService
{
    private $carritoModel;
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->carritoModel = new CarritoModel($db);
    }

    public function agregarProducto(int $idUsuario, int $idProducto, int $cantidad): array
    {
        $this->db->beginTransaction();

        try {
            // 1. Obtener carrito activo o crear uno nuevo
            $carrito = $this->carritoModel->obtenerCarritoActivoPorUsuario($idUsuario);
            if (!$carrito) {
                $idCarrito = $this->carritoModel->crearCarrito($idUsuario);
                if (!$idCarrito) {
                    throw new \Exception('Error al crear carrito.');
                }
            } else {
                $idCarrito = (int)$carrito['id_carrito'];
            }

            // 2. Validar producto
            $producto = $this->carritoModel->obtenerProductoConImagen($idProducto);
            if (!$producto) {
                return ResponseHelper::error('Producto no encontrado o inactivo.', 404);
            }

            if ($producto['stock'] < $cantidad) {
                return ResponseHelper::error('Stock insuficiente para el producto.', 400);
            }

            // 3. Verificar si ya existe en el carrito
            $itemExistente = $this->carritoModel->obtenerItemPorCarritoYProducto($idCarrito, $idProducto);
            $precioUnitario = (float)$producto['precio'];
            $subtotal = $precioUnitario * $cantidad;

            if ($itemExistente) {
                $nuevaCantidad = $itemExistente['cantidad'] + $cantidad;
                if ($producto['stock'] < $nuevaCantidad) {
                    return ResponseHelper::error('Stock insuficiente para la cantidad solicitada.', 400);
                }
                $nuevoSubtotal = $precioUnitario * $nuevaCantidad;
                $actualizado = $this->carritoModel->actualizarItem((int)$itemExistente['id_item'], $nuevaCantidad, $nuevoSubtotal);
                if (!$actualizado) {
                    throw new \Exception('Error al actualizar ítem.');
                }
            } else {
                $insertado = $this->carritoModel->agregarItem($idCarrito, $idProducto, $cantidad, $precioUnitario, $subtotal);
                if (!$insertado) {
                    throw new \Exception('Error al agregar ítem.');
                }
            }

            $this->db->commit();
            return ResponseHelper::success('Producto agregado al carrito.', [], 201);
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error en CarritoService::agregarProducto: " . $e->getMessage());
            return ResponseHelper::databaseError();
        }
    }

    public function listarCarrito(int $idUsuario): array
    {
        $carrito = $this->carritoModel->obtenerCarritoActivoPorUsuario($idUsuario);
        if (!$carrito) {
            return ResponseHelper::success('Carrito vacío.', ['carrito' => null, 'items' => [], 'total_general' => 0.0]);
        }

        $items = $this->carritoModel->obtenerCarritoCompleto((int)$carrito['id_carrito']);
        $total = $this->carritoModel->obtenerTotalCarrito((int)$carrito['id_carrito']);

        return ResponseHelper::success('Carrito recuperado.', [
            'carrito' => $carrito,
            'items' => $items,
            'total_general' => $total
        ]);
    }

    public function actualizarCantidad(int $idUsuario, int $idItem, int $cantidad): array
    {
        // Validar carrito activo del usuario
        $carrito = $this->carritoModel->obtenerCarritoActivoPorUsuario($idUsuario);
        if (!$carrito) {
            return ResponseHelper::error('No existe un carrito activo.', 404);
        }

        // Obtener el ítem con precio_unitario
        $sql = "SELECT cp.id_producto, cp.cantidad, cp.precio_unitario, p.stock 
            FROM carrito_producto cp 
            JOIN producto p ON cp.id_producto = p.id_producto 
            WHERE cp.id_item = :id_item AND cp.id_carrito = :id_carrito";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_item', $idItem, PDO::PARAM_INT);
        $stmt->bindParam(':id_carrito', $carrito['id_carrito'], PDO::PARAM_INT);
        $stmt->execute();
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            return ResponseHelper::error('Ítem no encontrado en el carrito.', 404);
        }

        if ($cantidad <= 0) {
            $eliminado = $this->carritoModel->eliminarItem($idItem);
            if (!$eliminado) {
                return ResponseHelper::databaseError();
            }
            return ResponseHelper::success('Ítem eliminado del carrito.');
        }

        if ($item['stock'] < $cantidad) {
            return ResponseHelper::error('Stock insuficiente para la nueva cantidad.', 400);
        }

        $precioUnitario = (float)$item['precio_unitario'];
        $subtotal = $precioUnitario * $cantidad;

        $actualizado = $this->carritoModel->actualizarItem($idItem, $cantidad, $subtotal);
        if (!$actualizado) {
            return ResponseHelper::databaseError();
        }

        return ResponseHelper::success('Cantidad actualizada.');
    }

    public function eliminarItem(int $idUsuario, int $idItem): array
    {
        $carrito = $this->carritoModel->obtenerCarritoActivoPorUsuario($idUsuario);
        if (!$carrito) {
            return ResponseHelper::error('No existe un carrito activo.', 404);
        }

        $sql = "SELECT id_item FROM carrito_producto WHERE id_item = :id_item AND id_carrito = :id_carrito";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_item', $idItem, PDO::PARAM_INT);
        $stmt->bindParam(':id_carrito', $carrito['id_carrito'], PDO::PARAM_INT);
        $stmt->execute();
        if (!$stmt->fetch()) {
            return ResponseHelper::error('Ítem no encontrado en el carrito.', 404);
        }

        $eliminado = $this->carritoModel->eliminarItem($idItem);
        if (!$eliminado) {
            return ResponseHelper::databaseError();
        }

        return ResponseHelper::success('Ítem eliminado.');
    }

    public function vaciarCarrito(int $idUsuario): array
    {
        $carrito = $this->carritoModel->obtenerCarritoActivoPorUsuario($idUsuario);
        if (!$carrito) {
            return ResponseHelper::success('Carrito ya vacío.');
        }

        $vaciar = $this->carritoModel->vaciarCarrito((int)$carrito['id_carrito']);
        if (!$vaciar) {
            return ResponseHelper::databaseError();
        }

        return ResponseHelper::success('Carrito vaciado.');
    }

    public function marcarComoConvertido(int $idUsuario): array
    {
        $carrito = $this->carritoModel->obtenerCarritoActivoPorUsuario($idUsuario);
        if (!$carrito) {
            return ResponseHelper::error('No existe un carrito activo para convertir.', 404);
        }

        $actualizado = $this->carritoModel->actualizarEstadoCarrito((int)$carrito['id_carrito'], 'convertido');
        if (!$actualizado) {
            return ResponseHelper::databaseError();
        }

        return ResponseHelper::success('Carrito marcado como convertido.');
    }
}
