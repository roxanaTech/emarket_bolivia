<?php

namespace App\Middlewares;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Utils\ResponseHelper;

class AuthMiddleware
{
    public static function handle(): void
    {
        // DEBUG: Separador para identificar el inicio de la ejecución en el log
        error_log("--- [DEBUG] AuthMiddleware iniciado ---");

        // Intentar obtener el header Authorization
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (empty($authHeader) && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                $authHeader = $headers['Authorization'];
            }
        }

        // DEBUG: Muestra el contenido del header Authorization que se recibió
        error_log("[DEBUG] Contenido de Authorization Header: " . $authHeader);

        // Validar formato Bearer
        if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            // DEBUG: Registra por qué falló la validación
            error_log("[DEBUG] Error: Token no proporcionado o formato incorrecto.");
            ResponseHelper::sendJson(ResponseHelper::error('Token no proporcionado o en formato incorrecto', 401));
            exit();
        }

        // El token JWT es el primer grupo capturado por la expresión regular
        $jwt = $matches[1];

        // DEBUG: Muestra el token JWT extraído
        error_log("[DEBUG] JWT extraído: " . $jwt);

        try {
            $key = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
            // DEBUG: Confirma qué clave se está usando (útil si falla la variable de entorno)
            if (!isset($_ENV['JWT_SECRET'])) {
                error_log("[DEBUG] ADVERTENCIA: Usando la clave JWT secreta de respaldo (fallback).");
            }

            $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

            // Guardar usuario decodificado para usarlo en la ruta
            $GLOBALS['auth_user'] = $decoded;

            // DEBUG: Muestra el contenido del token decodificado.
            // Usamos print_r con el segundo parámetro a 'true' para capturar su salida como un string.
            error_log("[DEBUG] Token decodificado exitosamente: " . print_r($decoded, true));
        } catch (\Exception $e) {
            // DEBUG: ¡MUY IMPORTANTE! Registra el mensaje de error específico de la excepción.
            // Esto te dirá exactamente por qué el token es inválido (ej. "Signature verification failed", "Expired token").
            error_log("[DEBUG] Error al decodificar token: " . $e->getMessage());

            ResponseHelper::sendJson(ResponseHelper::error('Token inválido o expirado: ' . $e->getMessage(), 401));
            exit();
        }
    }
}
