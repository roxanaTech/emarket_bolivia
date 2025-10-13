<?php

namespace App\Services;

use App\Utils\ResponseHelper;
use App\Modules\Reviews\ReviewModel;
use App\Modules\Productos\ProductoModel;

class ReviewService
{
    private $reviewModel;
    private $productoModel;
    private $db;

    public function __construct($db)
    {
        $this->reviewModel = new ReviewModel($db);
        $this->productoModel = new ProductoModel($db);
        $this->db = $db;
    }
    public function crearReview($idUsuario, array $data): array
    {
        $idProducto = $data['id_producto'] ?? null;
        $titulo = $data['titulo'] ?? null;
        $calificacion = $data['calificacion'] ?? null;
        $comentario = $data['comentario'] ?? null;

        if (!$idProducto) {
            return ResponseHelper::error('ID de producto es requerido.', 400);
        }

        // Validar título
        if (empty(trim($titulo))) {
            return ResponseHelper::error('El título de la reseña es requerido.', 400);
        }
        if (strlen(trim($titulo)) > 255) {
            return ResponseHelper::error('El título es demasiado largo.', 400);
        }

        // Validar calificación
        $error = $this->validarCalificacion($calificacion);
        if ($error) return $error;

        // Verificar si compró (pero NO bloquear si no compró)
        $compraVerificada = $this->reviewModel->verificarCompraEntregada($idUsuario, $idProducto);

        // Validar duplicado (solo si ya tiene una reseña activa)
        $error = $this->validarDuplicado($idUsuario, $idProducto);
        if ($error) return $error;

        $this->db->beginTransaction();
        try {
            // Pasar $compraVerificada como valor de `verificada`
            $idResena = $this->reviewModel->crearReview(
                $idProducto,
                $idUsuario,
                $calificacion,
                $comentario,
                trim($titulo),
                $compraVerificada // true o false
            );

            if (!$idResena) {
                throw new \Exception('Error al crear reseña');
            }

            $success = $this->productoModel->actualizarEstadisticasProducto($idProducto);
            if (!$success) {
                throw new \Exception('Error al actualizar estadísticas');
            }

            $this->db->commit();
            return ResponseHelper::success('Reseña creada', ['id' => $idResena]);
        } catch (\Exception $e) {
            $this->db->rollback();
            return ResponseHelper::error('Error al crear reseña: ' . $e->getMessage(), 500);
        }
    }

    public function actualizarReview(int $idReview, int $idUsuario, array $data): array
    {
        $error = $this->validarAutoria($idReview, $idUsuario);
        if ($error) return $error;

        $titulo = $data['titulo'] ?? null;
        $calificacion = $data['calificacion'] ?? null;

        // Validar título
        if (empty(trim($titulo))) {
            return ResponseHelper::error('El título de la reseña es requerido.', 400);
        }

        $error = $this->validarCalificacion($calificacion);
        if ($error) return $error;

        $review = $this->reviewModel->obtenerReviewPorId($idReview);
        if (!$review) {
            return ResponseHelper::error('Reseña no encontrada.', 404);
        }

        // Actualizar en BD 
        $success = $this->reviewModel->actualizarReview($idReview, $calificacion, $data['comentario'], $titulo);
        if (!$success) {
            return ResponseHelper::error('Error al actualizar la reseña.', 500);
        }

        $this->productoModel->actualizarEstadisticasProducto($review['id_producto']);
        return ResponseHelper::success('Reseña actualizada exitosamente.');
    }

    public function eliminarReview(int $idReview, int $idUsuario): array
    {
        // Validar autoría
        $error = $this->validarAutoria($idReview, $idUsuario);
        if ($error) return $error;

        // Obtener id_producto antes de eliminar
        $review = $this->reviewModel->obtenerReviewPorId($idReview);
        if (!$review) {
            return ResponseHelper::error('Reseña no encontrada.', 404);
        }

        // Eliminar en BD
        $success = $this->reviewModel->eliminarReview($idReview);
        if (!$success) {
            return ResponseHelper::error('Error al eliminar la reseña.', 500);
        }

        // ACTUALIZAR ESTADÍSTICAS DEL PRODUCTO
        $this->productoModel->actualizarEstadisticasProducto($review['id_producto']);

        return ResponseHelper::success('Reseña eliminada exitosamente.');
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
