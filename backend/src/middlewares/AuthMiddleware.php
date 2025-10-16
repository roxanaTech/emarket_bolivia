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
        error_log("--- Empezando de nuevo ---");
        error_log("--- [DEBUG] AuthMiddleware iniciado ---");

        // Intentar obtener el header Authorization
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        // Intentar con apache_request_headers si está disponible
        if (empty($authHeader) && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $authHeader = $headers['Authorization'] ?? '';
        }

        // 👇 Log del header para debug
        error_log("[DEBUG] Header Authorization recibido: " . ($authHeader ?? 'vacío'));

        // Intentar obtener el token del cuerpo de la solicitud (para uploads de archivos)
        $jwt = null;
        if (!empty($authHeader) && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $jwt = $matches[1];
            error_log("[DEBUG] Token extraído del header Bearer");
        } elseif (!empty($_POST['auth_token'])) {
            // 👇 Token enviado como campo del formulario
            $jwt = $_POST['auth_token'];
            error_log("[DEBUG] Token obtenido desde POST: auth_token");
            // 👇 Log extra para ver el contenido de POST
            error_log("[DEBUG] Contenido de _POST: " . print_r($_POST, true));
        }

        if (empty($jwt)) {
            error_log("[DEBUG] Error: Token no proporcionado en header ni en POST.");
            ResponseHelper::sendJson(ResponseHelper::error('Token no proporcionado', 401));
            exit();
        }

        error_log("[DEBUG] JWT a decodificar: " . substr($jwt, 0, 20) . '...');  // No loguees el token completo por seguridad

        try {
            $key = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
            $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
            $GLOBALS['auth_user'] = $decoded;
            error_log("[DEBUG] Token decodificado exitoso. Usuario SUB: " . ($decoded->sub ?? 'sin ID'));
        } catch (\Exception $e) {
            error_log("[DEBUG] Error al decodificar: " . $e->getMessage());
            ResponseHelper::sendJson(ResponseHelper::error('Token inválido o expirado', 401));
            exit();
        }
    }
}
