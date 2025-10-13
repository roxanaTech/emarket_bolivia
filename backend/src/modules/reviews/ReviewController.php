<?php

namespace App\Modules\Reviews;

use App\Services\ReviewService;
use App\Utils\ResponseHelper;

class ReviewController
{
    private $reviewModel;
    private $reviewService;

    public function __construct($db)
    {
        $this->reviewModel = new ReviewModel($db);
        $this->reviewService = new ReviewService($db);
    }

    /**
     * Crea una nueva reseña.
     *
     * @param object $payload JWT payload con el ID del usuario autenticado.
     * @param array $data Datos de la reseña: id_producto, calificacion, comentario (opcional).
     * @return array Respuesta en formato ResponseHelper.
     */
    public function crearReview($payload, array $data): array
    {
        // Crear reseña
        if ($this->reviewService->crearReview($payload->sub, $data)) {
            return ResponseHelper::success('Reseña creada exitosamente.');
        }

        return ResponseHelper::databaseError('Error al crear la reseña.');
    }

    /**
     * Obtiene la reseña propia de un usuario para un producto (para edición).
     *
     * @param object $payload JWT payload con el ID del usuario autenticado.
     * @param int $idProducto ID del producto.
     * @return array Respuesta en formato ResponseHelper.
     */
    public function obtenerReviewPropia($payload, int $idProducto): array
    {
        $idUsuario = $payload->sub;
        $review = $this->reviewModel->obtenerReviewActivaPorUsuarioYProducto($idUsuario, $idProducto);

        if (!$review || $review['estado'] === 'eliminado') {
            return ResponseHelper::error('No tienes una reseña activa para este producto.', 404);
        }

        return ResponseHelper::success('Reseña encontrada.', $review);
    }

    /**
     * Actualiza una reseña existente.
     *
     * @param object $payload JWT payload con el ID del usuario autenticado.
     * @param array $data Datos: id_review, calificacion, comentario (opcional).
     * @return array Respuesta en formato ResponseHelper.
     */
    public function actualizarReview($payload, array $data): array
    {
        $idUsuario = $payload->sub;
        $idReview = $data['id_review'] ?? null;
        // Actualizar
        if ($this->reviewService->actualizarReview($idReview, $idUsuario, $data)) {
            return ResponseHelper::success('Reseña actualizada exitosamente.');
        }

        return ResponseHelper::databaseError('Error al actualizar la reseña.');
    }

    /**
     * Elimina lógicamente una reseña (marca como 'eliminado').
     *
     * @param object $payload JWT payload con el ID del usuario autenticado.
     * @param int $idReview ID de la reseña.
     * @return array Respuesta en formato ResponseHelper.
     */
    public function eliminarReview($payload, int $idReview): array
    {
        $idUsuario = $payload->sub;

        if ($this->reviewService->eliminarReview($idReview, $idUsuario)) {
            return ResponseHelper::success('Reseña eliminada exitosamente.');
        }

        return ResponseHelper::databaseError('Error al eliminar la reseña.');
    }

    /**
     * Lista las reseñas activas de un producto (público).
     *
     * @param int $idProducto ID del producto.
     * @param int $pagina Número de página (por defecto 1).
     * @return array Respuesta en formato ResponseHelper.
     */
    public function listarResenasPorProducto(int $idProducto, int $pagina = 1): array
    {
        $resenas = $this->reviewModel->obtenerResenasPorProducto($idProducto, $pagina, 10);
        return ResponseHelper::success('Reseñas obtenidas.', $resenas);
    }
}
