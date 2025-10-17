<?php

namespace App\Modules\Carrito;

use App\Services\CarritoService;
use App\Utils\ResponseHelper;

class CarritoController
{
    private CarritoModel $carritoModel;
    private CarritoService $carritoService;

    public function __construct(\PDO $db)
    {
        $this->carritoModel = new CarritoModel($db);
        $this->carritoService = new CarritoService($db);
    }

    public function agregarProducto($payload, array $data, array $files = []): array
    {
        $idUsuario = $payload->sub;
        $idProducto = $data['id_producto'] ?? null;
        $cantidad = $data['cantidad'] ?? 1;

        if (!$idProducto || !is_numeric($idProducto) || $idProducto <= 0) {
            return ResponseHelper::error('ID de producto inválido.', 400);
        }
        if (!is_numeric($cantidad) || $cantidad <= 0) {
            return ResponseHelper::error('Cantidad debe ser un número entero positivo.', 400);
        }

        return $this->carritoService->agregarProducto($idUsuario, (int)$idProducto, (int)$cantidad);
    }

    public function listarCarrito($payload, array $data = [], array $files = []): array
    {
        $idUsuario = $payload->sub;
        return $this->carritoService->listarCarrito($idUsuario);
    }

    public function actualizarCantidad($payload, array $data, array $files = []): array
    {
        $idUsuario = $payload->sub;
        $idItem = $data['id_item'] ?? null;
        $cantidad = $data['cantidad'] ?? null;

        if (!$idItem || !is_numeric($idItem) || $idItem <= 0) {
            return ResponseHelper::error('ID de ítem inválido.', 400);
        }
        if (!is_numeric($cantidad)) {
            return ResponseHelper::error('Cantidad debe ser un número entero.', 400);
        }

        return $this->carritoService->actualizarCantidad($idUsuario, (int)$idItem, (int)$cantidad);
    }

    public function eliminarItem($payload, array $data, array $files = []): array
    {
        $idUsuario = $payload->sub;
        $idItem = $data['id_item'] ?? null;

        if (!$idItem || !is_numeric($idItem) || $idItem <= 0) {
            return ResponseHelper::error('ID de ítem inválido.', 400);
        }

        return $this->carritoService->eliminarItem($idUsuario, (int)$idItem);
    }

    public function vaciarCarrito($payload, array $data = [], array $files = []): array
    {
        $idUsuario = $payload->sub;
        return $this->carritoService->vaciarCarrito($idUsuario);
    }

    public function marcarComoConvertido($payload, array $data = [], array $files = []): array
    {
        $idUsuario = $payload->sub;
        return $this->carritoService->marcarComoConvertido($idUsuario);
    }
}
