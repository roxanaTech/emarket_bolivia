<?php

namespace App\Modules\Ventas;

use App\Modules\Vendedores\VendedorModel;
use App\Services\VentaService;
use App\Utils\ResponseHelper;
use App\Services\CarritoService;

class VentaController
{
    private $ventaModel;
    private $vendedorModel;
    private $ventaService;
    private $carritoService;
    private $db;

    public function __construct($db)
    {
        $this->ventaModel = new VentaModel($db);
        $this->vendedorModel = new VendedorModel($db);
        $this->ventaService = new VentaService($db);
        $this->carritoService = new CarritoService($db);
        $this->db = $db;
    }

    /**
     * Crea una nueva venta a partir de un grupo de productos (de un solo vendedor).
     */
    public function crearVenta($payload, array $data): array
    {
        $idComprador = $payload->sub;
        $tipoPago = $data['tipo_pago'] ?? '';
        $tipoEntrega = $data['tipo_entrega'] ?? '';
        $items = $data['items'] ?? [];
        $direccionEntrega = $data['direccion_entrega'] ?? null;
        $telefonoContacto = $data['telefono_contacto'] ?? null;
        $comprobantePago = $data['comprobante_pago'] ?? null;

        // Validar método de pago
        $metodosValidos = ['efectivo', 'transferencia', 'tarjeta', 'qr'];
        if (!in_array($tipoPago, $metodosValidos)) {
            return ResponseHelper::error('Método de pago no válido.', 400);
        }

        // Validar datos según tipo de pago
        if ($tipoPago === 'tarjeta') {
            $numeroTarjeta = $data['numero_tarjeta'] ?? '';
            $fechaExpiracion = $data['fecha_expiracion'] ?? '';
            $cvv = $data['cvv'] ?? '';

            if (!$this->ventaService->validarTarjetaLuhn($numeroTarjeta)) {
                return ResponseHelper::error('Número de tarjeta inválido.', 400);
            }
            if (!$this->ventaService->validarFechaExpiracion($fechaExpiracion)) {
                return ResponseHelper::error('Fecha de expiración inválida.', 400);
            }
            if (!$this->ventaService->validarCVV($cvv)) {
                return ResponseHelper::error('CVV inválido.', 400);
            }
            // ¡Nunca guardamos datos de tarjeta!
        }

        if (empty($items)) {
            return ResponseHelper::error('No se proporcionaron productos.', 400);
        }

        // Validar que todos los productos sean del mismo vendedor
        if (!$this->ventaService->todosMismoVendedor($items)) {
            return ResponseHelper::error('Los productos deben pertenecer al mismo vendedor.', 400);
        }

        // Validar stock
        if (!$this->ventaService->validarStockProductos($items)) {
            return ResponseHelper::error('Stock insuficiente para uno o más productos.', 400);
        }

        // Obtener ID del vendedor (todos son del mismo)
        $idVendedor = $this->ventaService->obtenerIdVendedorDeItems($items);
        if (!$idVendedor) {
            return ResponseHelper::error('No se pudo identificar al vendedor.', 500);
        }

        // Calcular total
        $totalVenta = 0;
        foreach ($items as $item) {
            $subtotal = $item['cantidad'] * $item['precio_unitario'];
            $totalVenta += $subtotal;
        }

        // Iniciar transacción 
        $this->db->beginTransaction();

        try {
            // Crear venta
            $idVenta = $this->ventaModel->crearVenta(
                $idVendedor,
                $idComprador,
                $tipoPago,
                $totalVenta,
                $tipoEntrega,
                $comprobantePago,
                $direccionEntrega,
                $telefonoContacto
            );

            if (!$idVenta) {
                throw new \Exception('Error al crear la venta.');
            }

            // Crear detalles
            foreach ($items as $item) {
                $subtotal = $item['cantidad'] * $item['precio_unitario'];
                if (!$this->ventaModel->crearDetalleVenta(
                    $idVenta,
                    $item['id_producto'],
                    $item['cantidad'],
                    $item['precio_unitario'],
                    $subtotal
                )) {
                    throw new \Exception('Error al crear detalle de venta.');
                }
            }

            $this->db->commit();
            return ResponseHelper::success('Venta creada exitosamente.', ['id_venta' => $idVenta]);
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("Error al crear venta: " . $e->getMessage());
            return ResponseHelper::error('Error al procesar la venta.', 500);
        }
    }
    /**
     * Obtiene los detalles completos de una venta.
     */
    public function obtenerVenta($idVenta, $payload): array
    {
        $idUsuario = $payload->sub;

        $venta = $this->ventaModel->obtenerVentaPorId($idVenta);
        if (!$venta) {
            return ResponseHelper::error('Venta no encontrada.', 404);
        }

        // Verificar que el usuario sea comprador o vendedor
        if ($venta['id_comprador'] != $idUsuario && $venta['id_vendedor'] != $idUsuario) {
            return ResponseHelper::error('No autorizado.', 403);
        }

        $detalles = $this->ventaModel->obtenerDetallesVenta($idVenta);
        $venta['detalles'] = $detalles;

        return ResponseHelper::success('Venta obtenida.', $venta);
    }

    /**
     * Lista las ventas del comprador autenticado.
     */
    public function listarVentasComprador($payload): array
    {
        $idComprador = $payload->sub;
        $ventas = $this->ventaModel->listarVentasPorComprador($idComprador);
        return ResponseHelper::success('Ventas obtenidas.', $ventas);
    }

    /**
     * Lista las ventas del vendedor autenticado.
     */
    public function listarVentasVendedor($payload): array
    {
        $idUsuario = $payload->sub;
        // Obtener id_vendedor desde usuario (asumiendo relación en BD)
        $sql = "SELECT id_vendedor FROM vendedor WHERE id_usuario = :id_usuario";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $idUsuario);
        $stmt->execute();
        $vendedor = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$vendedor) {
            return ResponseHelper::error('No eres vendedor.', 403);
        }

        $ventas = $this->ventaModel->listarVentasPorVendedor($vendedor['id_vendedor']);
        return ResponseHelper::success('Ventas obtenidas.', $ventas);
    }

    /**
     * Actualiza el estado de una venta (solo vendedor o comprador según reglas).
     */
    public function actualizarEstado($idVenta, $nuevoEstado, $payload): array
    {
        $idUsuario = $payload->sub;
        $idVendedor = $this->vendedorModel->recuperarIdVendedorPorIdUsuario($idUsuario);

        $venta = $this->ventaModel->obtenerVentaPorId($idVenta);
        if (!$venta) {
            return ResponseHelper::error('Venta no encontrada.', 404);
        }

        $estadosValidos = ['pendiente', 'pagada', 'enviada', 'entregada', 'cancelada', 'eliminada'];
        if (!in_array($nuevoEstado, $estadosValidos)) {
            return ResponseHelper::error('Estado no válido.', 400);
        }

        // Reglas de transición
        $estadoActual = $venta['estado'];
        if ($estadoActual === 'pendiente') {
            if ($nuevoEstado === 'cancelada' && $venta['id_comprador'] == $idUsuario) {
                // Comprador puede cancelar
            } elseif ($nuevoEstado === 'pagada' && $venta['id_vendedor'] == $idVendedor) {
                // Vendedor puede marcar como pagada
            } else {
                return ResponseHelper::error('No puedes realizar esta acción.', 403);
            }
        } elseif ($estadoActual === 'pagada' && $venta['id_vendedor'] == $idVendedor) {
            if (in_array($nuevoEstado, ['enviada', 'cancelada'])) {
                // Vendedor puede enviar o cancelar
            } else {
                return ResponseHelper::error('Transición de estado no permitida.', 400);
            }
        } elseif ($estadoActual === 'enviada' && $venta['id_vendedor'] == $idVendedor) {
            if ($nuevoEstado === 'entregada') {
                // Vendedor marca como entregada
            } else {
                return ResponseHelper::error('Transición de estado no permitida.', 400);
            }
        } else {
            return ResponseHelper::error('No puedes cambiar el estado en este momento.', 403);
        }

        if ($this->ventaModel->actualizarEstadoVenta($idVenta, $nuevoEstado)) {
            if ($nuevoEstado === 'pagada') {
                // Reducir stock
                $this->ventaService->reducirStockPorVenta($idVenta);

                // Vaciar carrito del comprador
                $this->carritoService->vaciarCarrito($idUsuario);
            }
            return ResponseHelper::success('Estado actualizado.', ['id_venta' => $idVenta, 'estado' => $nuevoEstado]);
        }
        return ResponseHelper::error('Error al actualizar el estado.', 500);
    }

    /**
     * Sube/completa el comprobante de pago (solo para ciertos métodos).
     */
    public function subirComprobante($idVenta, $comprobante, $payload): array
    {
        $idUsuario = $payload->sub;

        if (!$this->ventaModel->ventaPerteneceAComprador($idVenta, $idUsuario)) {
            return ResponseHelper::error('No autorizado.', 403);
        }

        if ($this->ventaModel->actualizarComprobantePago($idVenta, $comprobante)) {
            return ResponseHelper::success('Comprobante registrado.', ['id_venta' => $idVenta]);
        }
        return ResponseHelper::error('Error al registrar comprobante.', 500);
    }
}
