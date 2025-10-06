<?php

namespace App\Services;

use App\Utils\ResponseHelper;

class ReviewService
{
    private $reviewModel;

    public function __construct($db)
    {
        $this->reviewModel = new \App\Modules\Reviews\ReviewModel($db);
    }

    public function validarCalificacion($calificacion)
    {
        if (!is_int($calificacion) || $calificacion < 1 || $calificacion > 5) {
            return ResponseHelper::error('La calificación debe ser un número entero entre 1 y 5.', 400);
        }
        return null;
    }

    public function validarCompraEntregada($idUsuario, $idProducto)
    {
        if (!$this->reviewModel->verificarCompraEntregada($idUsuario, $idProducto)) {
            return ResponseHelper::error('Solo los usuarios que han comprado y recibido el producto pueden dejar una reseña.', 403);
        }
        return null;
    }

    public function validarDuplicado($idUsuario, $idProducto)
    {
        $reviewActiva = $this->reviewModel->obtenerReviewActivaPorUsuarioYProducto($idUsuario, $idProducto);
        if ($reviewActiva) {
            return ResponseHelper::error('Ya tienes una reseña activa para este producto.', 409);
        }
        return null;
    }

    public function validarAutoria($idReview, $idUsuario)
    {
        $review = $this->reviewModel->obtenerReviewPorId($idReview);
        if (!$review) {
            return ResponseHelper::error('Reseña no encontrada.', 404);
        }
        if ($review['id_usuario'] != $idUsuario) {
            return ResponseHelper::error('No tienes permiso para editar esta reseña.', 403);
        }
        return null;
    }
}
