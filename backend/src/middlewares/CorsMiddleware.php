<?php

namespace App\Middlewares;

class CorsMiddleware
{
    public static function handle(): void
    {
        // Limpieza: Asegurar que no hay headers CORS previos
        if (function_exists('header_remove')) {
            header_remove('Access-Control-Allow-Origin');
            header_remove('Access-Control-Allow-Methods');
            header_remove('Access-Control-Allow-Headers');
        }

        // Obtener el origen de la solicitud
        $origin = $_SERVER['HTTP_ORIGIN'] ?? null;

        // Lista de orígenes permitidos
        $allowedOrigins = [
            'http://localhost',
            'https://localhost',
            'http://localhost:3000',
            'http://localhost:8080',
            'http://127.0.0.1',
            'https://127.0.0.1'
        ];

        // Solo establecer UN header Access-Control-Allow-Origin
        if ($origin && in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        } else {
            header('Access-Control-Allow-Origin: *');
        }

        // Headers CORS adicionales
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With, X-Requested-With');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Max-Age: 86400');

        // Manejar solicitudes preflight (OPTIONS)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            // Enviar headers adicionales para preflight
            header('Content-Length: 0');
            header('Content-Type: text/plain');
            http_response_code(200);
            exit;
        }
    }
}
