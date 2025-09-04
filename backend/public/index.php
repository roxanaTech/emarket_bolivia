<?php

use Bramus\Router\Router;
use App\Utils\ResponseHelper;
use App\Middlewares\CorsMiddleware;

// Incluye el autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Aplica CORS INMEDIATAMENTE antes que cualquier otra cosa
CorsMiddleware::handle();

// Carga las variables de entorno usando phpdotenv
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Incluye la configuración de la base de datos
require_once __DIR__ . '/../src/config/database.php';

// Habilita la visualización de errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Establece el content type por defecto
header('Content-Type: application/json; charset=utf-8');

// Crea una instancia del enrutador
$router = new Router();

// Define el directorio base para el enrutamiento
$router->setBasePath('/emarket/backend/public');

// Incluye el archivo que define todas las rutas
require_once __DIR__ . '/../src/routes/web.php';

// Inicia el enrutador y maneja excepciones
try {
    $router->run();
} catch (\Exception $e) {
    // Asegurar que CORS esté presente incluso en errores
    CorsMiddleware::handle();

    if ($e->getCode() === 404) {
        http_response_code(404);
        ResponseHelper::sendJson(ResponseHelper::error('Ruta no encontrada', 404));
    } else {
        http_response_code(500);
        ResponseHelper::sendJson(ResponseHelper::error('Error del servidor: ' . $e->getMessage(), 500));
    }
}
