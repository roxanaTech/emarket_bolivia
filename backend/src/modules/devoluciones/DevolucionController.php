<?php

namespace App\Modules\Devoluciones;

use App\Services\DevolucionService;
use App\Utils\ResponseHelper;

class DevolucionController
{
    private $devolucionModel;
    private $devolucionService;
    private $db;

    public function __construct($db)
    {
        $this->devolucionModel = new DevolucionModel($db);
        $this->devolucionService = new DevolucionService($db);
        $this->db = $db;
    }

    /**
     * Solicita una nueva devolución.
     */
    public function solicitarDevolucion($payload, array $data): array
    {
        $idComprador = $payload->sub;
        $idDetalleVenta = $data['id_detalle_venta'] ?? null;
        $cantidad = $data['cantidad'] ?? null;
        $motivo = $data['motivo'] ?? '';
        $comprobanteImagen = $data['comprobante_imagen'] ?? null;

        if (!$idDetalleVenta || !is_numeric($idDetalleVenta)) {
            return ResponseHelper::error('ID de detalle de venta inválido.', 400);
        }
        if (!$cantidad || !is_numeric($cantidad) || $cantidad <= 0) {
            return ResponseHelper::error('Cantidad debe ser un número entero positivo.', 400);
        }
        if (empty(trim($motivo))) {
            return ResponseHelper::error('El motivo es obligatorio.', 400);
        }

        // Validar que se pueda devolver
        $validacion = $this->devolucionService->validarSolicitudDevolucion($idComprador, (int)$idDetalleVenta, (int)$cantidad);
        if (!$validacion['valido']) {
            return ResponseHelper::error($validacion['mensaje'], 400);
        }

        $idDevolucion = $this->devolucionModel->crearDevolucion(
            (int)$idDetalleVenta,
            $idComprador,
            (int)$cantidad,
            trim($motivo),
            $comprobanteImagen
        );

        if (!$idDevolucion) {
            return ResponseHelper::error('Error al crear la solicitud de devolución.', 500);
        }

        return ResponseHelper::success('Solicitud de devolución creada exitosamente.', ['id_devolucion' => $idDevolucion]);
    }
    /**
     * Guarda una imagen de devolución en el servidor.
     * 
     * @param array $archivo $_FILES['imagen']
     * @param int $idDevolucion
     * @return string|null Nombre del archivo guardado o null si falla
     */
    private function guardarImagenDevolucion(array $archivo, int $idDevolucion): ?string
    {
        // Validar con el servicio
        $validacion = $this->devolucionService->validarImagen($archivo);
        if (!$validacion['valido']) {
            error_log("Validación de imagen fallida: " . $validacion['mensaje']);
            return null;
        }

        $extension = $validacion['extension'];
        $nombreUnico = 'dev_' . $idDevolucion . '_' . uniqid() . '.' . $extension;
        $rutaCarpeta = __DIR__ . '/../../../public/uploads/devoluciones/';
        $rutaCompleta = $rutaCarpeta . $nombreUnico;

        // Crear carpeta si no existe
        if (!is_dir($rutaCarpeta)) {
            if (!mkdir($rutaCarpeta, 0777, true)) {
                error_log("No se pudo crear la carpeta: $rutaCarpeta");
                return null;
            }
        }

        // Mover el archivo
        if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            return $nombreUnico;
        } else {
            error_log("Error al mover el archivo a: $rutaCompleta");
            return null;
        }
    }

    /**
     * Sube una imagen para una devolución existente.
     */
    public function subirImagenDevolucion(int $idDevolucion, array $files, $payload): array
    {
        $idComprador = $payload->sub;

        // Verificar que la devolución pertenece al comprador
        if (!$this->devolucionModel->devolucionPerteneceAComprador($idDevolucion, $idComprador)) {
            return ResponseHelper::error('No autorizado.', 403);
        }

        // Verificar que el archivo existe
        if (!isset($files['imagen']) || !is_array($files['imagen'])) {
            return ResponseHelper::error('No se proporcionó ninguna imagen.', 400);
        }

        $archivo = $files['imagen'];
        $nombreGuardado = $this->guardarImagenDevolucion($archivo, $idDevolucion);

        if ($nombreGuardado === null) {
            return ResponseHelper::error('Error al subir la imagen. Verifique el formato y tamaño.', 400);
        }

        // Guardar en base de datos
        $sql = "UPDATE devolucion SET comprobante_imagen = :nombre WHERE id_devolucion = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $nombreGuardado);
        $stmt->bindParam(':id', $idDevolucion);

        if ($stmt->execute()) {
            $urlPublica = "/uploads/devoluciones/" . $nombreGuardado;
            return ResponseHelper::success('Imagen subida exitosamente.', ['url' => $urlPublica]);
        } else {
            // Intentar borrar el archivo si falla la BD
            $rutaArchivo = __DIR__ . '/../../../public/uploads/devoluciones/' . $nombreGuardado;
            if (file_exists($rutaArchivo)) {
                unlink($rutaArchivo);
            }
            return ResponseHelper::error('Error al registrar la imagen en la base de datos.', 500);
        }
    }


    /**
     * Lista las devoluciones del comprador autenticado.
     */
    public function listarDevolucionesComprador($payload): array
    {
        $idComprador = $payload->sub;
        $devoluciones = $this->devolucionModel->listarDevolucionesComprador($idComprador);
        return ResponseHelper::success('Devoluciones obtenidas.', $devoluciones);
    }

    /**
     * Lista las devoluciones del vendedor autenticado.
     */
    public function listarDevolucionesVendedor($payload): array
    {
        $idUsuario = $payload->sub;
        // Obtener id_vendedor desde usuario
        $sql = "SELECT id_vendedor FROM vendedor WHERE id_usuario = :id_usuario";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $idUsuario);
        $stmt->execute();
        $vendedor = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$vendedor) {
            return ResponseHelper::error('No eres vendedor.', 403);
        }

        $devoluciones = $this->devolucionModel->listarDevolucionesVendedor($vendedor['id_vendedor']);
        return ResponseHelper::success('Devoluciones obtenidas.', $devoluciones);
    }

    /**
     * Aprueba una devolución (solo vendedor).
     */
    public function aprobarDevolucion($idDevolucion, $payload): array
    {
        $idUsuario = $payload->sub;
        $devolucion = $this->devolucionModel->obtenerDevolucionPorId($idDevolucion);
        if (!$devolucion) {
            return ResponseHelper::error('Devolución no encontrada.', 404);
        }

        // Verificar que el vendedor es dueño de la venta
        if (!$this->devolucionModel->devolucionPerteneceAVendedor($idDevolucion, $devolucion['id_vendedor'])) {
            return ResponseHelper::error('No autorizado.', 403);
        }

        if ($devolucion['estado'] !== 'solicitada') {
            return ResponseHelper::error('Solo se pueden aprobar devoluciones en estado "solicitada".', 400);
        }

        if ($this->devolucionModel->actualizarEstado($idDevolucion, 'aprobada')) {
            return ResponseHelper::success('Devolución aprobada.', ['id_devolucion' => $idDevolucion]);
        }
        return ResponseHelper::error('Error al aprobar la devolución.', 500);
    }

    /**
     * Rechaza una devolución (solo vendedor).
     */
    public function rechazarDevolucion($idDevolucion, $comentarios, $payload): array
    {
        $idUsuario = $payload->sub;
        $devolucion = $this->devolucionModel->obtenerDevolucionPorId($idDevolucion);
        if (!$devolucion) {
            return ResponseHelper::error('Devolución no encontrada.', 404);
        }

        if (!$this->devolucionModel->devolucionPerteneceAVendedor($idDevolucion, $devolucion['id_vendedor'])) {
            return ResponseHelper::error('No autorizado.', 403);
        }

        if ($devolucion['estado'] !== 'solicitada') {
            return ResponseHelper::error('Solo se pueden rechazar devoluciones en estado "solicitada".', 400);
        }

        if ($this->devolucionModel->actualizarEstado($idDevolucion, 'rechazada', $comentarios)) {
            return ResponseHelper::success('Devolución rechazada.', ['id_devolucion' => $idDevolucion]);
        }
        return ResponseHelper::error('Error al rechazar la devolución.', 500);
    }

    /**
     * Procesa una devolución aprobada (solo vendedor): devuelve stock y registra reembolso.
     */
    public function procesarDevolucion($idDevolucion, $data, $payload): array
    {
        $idUsuario = $payload->sub;
        $devolucionStock = (bool)($data['devolucion_stock'] ?? false);
        $reembolsoMetodo = $data['reembolso_metodo'] ?? 'ninguno';

        $metodosValidos = ['tarjeta', 'efectivo', 'transferencia', 'ninguno'];
        if (!in_array($reembolsoMetodo, $metodosValidos)) {
            return ResponseHelper::error('Método de reembolso no válido.', 400);
        }

        $devolucion = $this->devolucionModel->obtenerDevolucionPorId($idDevolucion);
        if (!$devolucion) {
            return ResponseHelper::error('Devolución no encontrada.', 404);
        }

        if (!$this->devolucionModel->devolucionPerteneceAVendedor($idDevolucion, $devolucion['id_vendedor'])) {
            return ResponseHelper::error('No autorizado.', 403);
        }

        if ($devolucion['estado'] !== 'aprobada') {
            return ResponseHelper::error('Solo se pueden procesar devoluciones aprobadas.', 400);
        }

        // Si se devuelve stock, actualizar producto
        if ($devolucionStock) {
            $detalle = $this->devolucionModel->obtenerDetalleVenta($devolucion['id_detalle_venta']);
            if ($detalle) {
                $this->devolucionModel->devolverStock($detalle['id_producto'], $devolucion['cantidad']);
            }
        }

        // Marcar como procesada
        if ($this->devolucionModel->actualizarEstado(
            $idDevolucion,
            'procesada',
            null,
            $devolucionStock ? 1 : 0,
            $reembolsoMetodo
        )) {
            return ResponseHelper::success('Devolución procesada exitosamente.', ['id_devolucion' => $idDevolucion]);
        }
        return ResponseHelper::error('Error al procesar la devolución.', 500);
    }
}
