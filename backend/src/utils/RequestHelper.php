<?php

namespace App\Utils;

use App\Utils\ResponseHelper;

class RequestHelper
{
    /**
     * Obtiene y valida el cuerpo de la petición como JSON.
     * Si el JSON es inválido, responde con error 400 y termina la ejecución.
     * 
     * @return array Datos decodificados del JSON
     */
    public static function getJsonBody(): array
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            ResponseHelper::sendJson(ResponseHelper::error('JSON inválido', 400));
            exit; // Termina la ejecución (como un "return" global)
        }

        return $data;
    }

    /**
     * Obtiene un valor requerido del JSON. Si no existe, responde con error.
     * 
     * @param array $data Datos del JSON
     * @param string $key Clave requerida
     * @param string $errorMessage Mensaje de error personalizado
     * @return mixed Valor requerido
     */
    public static function requireField(array $data, string $key, ?string $errorMessage = null): mixed
    {
        if (!isset($data[$key])) {
            $msg = $errorMessage ?? "El campo '{$key}' es requerido";
            ResponseHelper::sendJson(ResponseHelper::error($msg, 400));
            exit;
        }
        return $data[$key];
    }
}
