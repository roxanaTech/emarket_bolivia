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
    /**
     * Obtiene el carrito del usuario agrupado por vendedor, listo para finalizar compra.
     */
    public function obtenerCarritoAgrupadoPorVendedor($idUsuario)
    {
        // Obtener carrito activo
        $sqlCarrito = "SELECT id_carrito FROM carrito WHERE id_usuario = :id_usuario AND estado = 'activo'";
        $stmt = $this->db->prepare($sqlCarrito);
        $stmt->bindParam(':id_usuario', $idUsuario);
        $stmt->execute();
        $carrito = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$carrito) {
            return [
                'grupos_vendedores' => [],
                'total_general' => 0
            ];
        }

        $idCarrito = $carrito['id_carrito'];

        // Obtener ítems con información del vendedor y su dirección principal
        $sqlItems = "SELECT 
                    cp.id_item,
                    cp.id_producto,
                    cp.cantidad,
                    cp.precio_unitario,
                    cp.subtotal,
                    p.nombre,
                    p.stock,
                    p.id_imagen_principal,
                    p.id_vendedor,
                    v.razon_social AS nombre_vendedor,
                    v.cuenta_bancaria,
                    v.telefono_comercial,
                    v.banco,
                    d.departamento,
                    d.provincia,
                    d.ciudad,
                    d.zona,
                    d.calle,
                    d.numero,
                    d.referencias
                 FROM carrito_producto cp
                 JOIN producto p ON cp.id_producto = p.id_producto
                 JOIN vendedor v ON p.id_vendedor = v.id_vendedor
                 LEFT JOIN direccion d ON v.id_direccion_principal = d.id_direccion
                 WHERE cp.id_carrito = :id_carrito
                 ORDER BY p.id_vendedor";

        $stmt = $this->db->prepare($sqlItems);
        $stmt->bindParam(':id_carrito', $idCarrito);
        $stmt->execute();
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Agrupar por vendedor
        $grupos = [];
        $totalGeneral = 0;

        foreach ($items as $item) {
            $idVendedor = $item['id_vendedor'];
            $totalGeneral += (float)$item['subtotal'];

            if (!isset($grupos[$idVendedor])) {
                // Construir dirección legible
                $partesDireccion = [
                    $item['calle'],
                    $item['numero'],
                    $item['zona'],
                    $item['ciudad'],
                    $item['provincia'],
                    $item['departamento']
                ];
                // Filtrar partes no vacías y unirlas con ", "
                $direccionNegocio = implode(', ', array_filter($partesDireccion, function ($parte) {
                    return !empty(trim($parte));
                }));

                $grupos[$idVendedor] = [
                    'id_vendedor' => $idVendedor,
                    'nombre_vendedor' => $item['nombre_vendedor'],
                    'cuenta_bancaria' => $item['cuenta_bancaria'],
                    'banco' => $item['banco'],
                    'telefono' => $item['telefono_comercial'],
                    'direccion_negocio' => $direccionNegocio, // ✅ ¡Nuevo campo!
                    'items' => [],
                    'total_grupo' => 0
                ];
            }

            $grupos[$idVendedor]['items'][] = [
                'id_item' => $item['id_item'],
                'id_producto' => $item['id_producto'],
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $item['precio_unitario'],
                'subtotal' => $item['subtotal'],
                'nombre' => $item['nombre'],
                'stock' => $item['stock'],
                'imagen_principal' => trim($item['id_imagen_principal'])
            ];

            $grupos[$idVendedor]['total_grupo'] += (float)$item['subtotal'];
        }

        return [
            'grupos_vendedores' => array_values($grupos),
            'total_general' => $totalGeneral
        ];
    }
}
