<?php

namespace App\Services;

class DevolucionService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Valida que se pueda solicitar una devolución:
     * - La venta debe estar en estado 'entregada'
     * - El ítem debe pertenecer al comprador
     * - La cantidad solicitada no debe exceder la comprada
     */
    public function validarSolicitudDevolucion($idComprador, $idDetalleVenta, $cantidad)
    {
        // Verificar que el detalle de venta pertenece a una venta del comprador y está entregada
        $sql = "SELECT dv.cantidad AS cantidad_vendida
                FROM detalle_venta dv
                JOIN venta v ON dv.id_venta = v.id_venta
                WHERE dv.id_detalle = :id_detalle 
                  AND v.id_comprador = :id_comprador 
                  AND v.estado = 'entregada'";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_detalle', $idDetalleVenta);
        $stmt->bindParam(':id_comprador', $idComprador);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return ['valido' => false, 'mensaje' => 'No se puede devolver este ítem. Verifique que la venta esté entregada.'];
        }

        if ($cantidad > $row['cantidad_vendida']) {
            return ['valido' => false, 'mensaje' => 'La cantidad solicitada excede la comprada.'];
        }

        return ['valido' => true];
    }
    /**
     * Valida los datos de una imagen antes de subirla.
     * 
     * @param array $archivo $_FILES['imagen']
     * @return array ['valido' => bool, 'mensaje' => string, 'extension' => string]
     */
    public function validarImagen(array $archivo): array
    {
        if (!isset($archivo['error']) || $archivo['error'] !== UPLOAD_ERR_OK) {
            return ['valido' => false, 'mensaje' => 'No se subió ninguna imagen válida.'];
        }

        $tipo = $archivo['type'];
        $tamano = $archivo['size'];
        $nombre = $archivo['name'];

        // Validar tipo MIME
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($tipo, $tiposPermitidos)) {
            return ['valido' => false, 'mensaje' => 'Solo se permiten imágenes JPG o PNG.'];
        }

        // Validar tamaño (máx. 5MB)
        if ($tamano > 5 * 1024 * 1024) {
            return ['valido' => false, 'mensaje' => 'La imagen no debe superar 5MB.'];
        }

        // Obtener extensión segura
        $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
            return ['valido' => false, 'mensaje' => 'Extensión de archivo no permitida.'];
        }

        return ['valido' => true, 'extension' => $extension];
    }
}
