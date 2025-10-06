<?php

namespace App\Modules\Reviews;

class ReviewModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function crearReview($idProducto, $idUsuario, $calificacion, $comentario = null)
    {
        $sql = "INSERT INTO review (id_producto, id_usuario, calificacion, comentario) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$idProducto, $idUsuario, $calificacion, $comentario]);
    }

    public function obtenerReviewPorId($idReview)
    {
        $sql = "SELECT * FROM review WHERE id_review = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idReview]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function obtenerReviewActivaPorUsuarioYProducto($idUsuario, $idProducto)
    {
        $sql = "SELECT * FROM review 
            WHERE id_usuario = ? 
              AND id_producto = ? 
              AND estado = 'activo'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idUsuario, $idProducto]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function obtenerResenasPorProducto($idProducto, $pagina = 1, $porPagina = 10)
    {
        $offset = ($pagina - 1) * $porPagina;
        $sql = "SELECT r.*, u.nombres AS nombre_usuario 
                FROM review r
                INNER JOIN usuario u ON r.id_usuario = u.id_usuario
                WHERE r.id_producto = ? AND r.estado = 'activo'
                ORDER BY r.fecha DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idProducto, $porPagina, $offset]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function actualizarReview($idReview, $calificacion, $comentario = null)
    {
        $sql = "UPDATE review SET calificacion = ?, comentario = ? WHERE id_review = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$calificacion, $comentario, $idReview]);
    }

    public function eliminarReview($idReview)
    {
        $sql = "UPDATE review SET estado = 'eliminado' WHERE id_review = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$idReview]);
    }

    public function verificarCompraEntregada($idUsuario, $idProducto)
    {
        $sql = "SELECT dv.id_detalle
                FROM detalle_venta dv
                INNER JOIN venta v ON dv.id_venta = v.id_venta
                WHERE dv.id_producto = ? 
                  AND v.id_comprador = ? 
                  AND v.estado = 'entregada'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idProducto, $idUsuario]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) !== false;
    }
}
