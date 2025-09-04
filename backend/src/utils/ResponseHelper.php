<?php

namespace App\Utils;

class ResponseHelper
{
    // Respuesta de éxito
    public static function success($mensaje, $data = [], $codigo = 200)
    {
        http_response_code($codigo);
        return [
            'status' => 'success',
            'mensaje' => $mensaje,
            'data' => $data
        ];
    }

    // Respuesta de error
    public static function error($mensaje, $codigo = 400, $detalles = null)
    {
        http_response_code($codigo);
        $response = [
            'status' => 'error',
            'mensaje' => $mensaje,
            'code' => $codigo
        ];

        if ($detalles !== null) {
            $response['detalles'] = $detalles;
        }

        return $response;
    }

    // Método específico para errores de duplicación
    public static function duplicateError($campo = 'email')
    {
        return self::error(
            "El {$campo} ya está registrado",
            409
        );
    }

    // Método específico para errores de validación
    public static function validationError($errores)
    {
        return self::error(
            'Error de validación',
            400,
            $errores
        );
    }

    // Método específico para errores de base de datos
    public static function databaseError($mensaje = 'Error de base de datos')
    {
        return self::error($mensaje, 500);
    }

    // Método para respuestas JSON directas
    public static function sendJson($data, $codigo = 200)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($codigo);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
