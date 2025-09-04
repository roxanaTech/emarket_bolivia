<?php

use App\Modules\Usuarios\UsuarioController;
use App\Utils\ResponseHelper;

error_log('Loading routes in web.php');

// Ruta de prueba para verificar que la API funciona
$router->get('/', function () {
    ResponseHelper::sendJson(ResponseHelper::success('API Backend E-Market funcionando correctamente'));
});

// Ruta de prueba para el estado de la API
$router->get('/status', function () {
    ResponseHelper::sendJson(ResponseHelper::success([
        'status' => 'active',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => '1.0.0'
    ]));
});

// Define la ruta POST para registrar un usuario
$router->post('/usuarios/registro', function () use ($pdo) {
    // Obtiene los datos del cuerpo de la petición (JSON)
    $data = json_decode(file_get_contents("php://input"), true);

    // Si el JSON es inválido, envía una respuesta de error
    if (json_last_error() !== JSON_ERROR_NONE) {
        ResponseHelper::sendJson(ResponseHelper::error('JSON inválido', 400));
        return;
    }

    // Instancia el controlador y llama al método para registrar el usuario
    $controller = new UsuarioController($pdo);
    $respuesta = $controller->registrar($data);

    // Envía la respuesta al cliente
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Ruta para login
$router->post('/login', function () use ($pdo) {
    error_log('POST /login called');
    $data = json_decode(file_get_contents("php://input"), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        ResponseHelper::sendJson(ResponseHelper::error('JSON inválido', 400));
        return;
    }

    $controller = new UsuarioController($pdo);
    $respuesta = $controller->login($data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Configura un manejador para rutas no encontradas (404)
$router->set404(function () {
    ResponseHelper::sendJson(ResponseHelper::error('Ruta no encontrada', 404));
});

// Ejemplo de ruta protegida con JWT
$router->before('POST', '/productos', [\App\Middlewares\AuthMiddleware::class, 'handle']);
$router->post('/productos', function () use ($pdo) {
    error_log('POST /productos called');
    // Aquí iría la lógica para crear un producto, solo accesible por usuarios autenticados
    ResponseHelper::sendJson(ResponseHelper::success('Producto creado (simulado)', []));
});

// Manejador para rutas no encontradas
$router->set404(function () {
    ResponseHelper::sendJson(ResponseHelper::error('Ruta no encontrada', 404));
});
