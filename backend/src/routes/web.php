<?php

use App\Modules\Usuarios\UsuarioController;
use App\Modules\Vendedores\VendedorController;
use App\Utils\ResponseHelper;
use App\Middlewares\AuthMiddleware;

error_log('Cargando rutas en web.php');

// Rutas Públicas

// Punto de acceso raíz
$router->get('/', function () {
    ResponseHelper::sendJson(ResponseHelper::success('API Backend E-Market funcionando correctamente'));
});

// Estado de la API
$router->get('/status', function () {
    ResponseHelper::sendJson(ResponseHelper::success([
        'status' => 'active',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => '1.0.0'
    ]));
});

// Registro de usuario
$router->post('/usuarios/registro', function () use ($pdo) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        ResponseHelper::sendJson(ResponseHelper::error('JSON inválido', 400));
        return;
    }
    $controller = new UsuarioController($pdo);
    $respuesta = $controller->registrar($data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Login
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

// Middleware para rutas protegidas
$router->before('GET|PUT|DELETE', '/usuarios/.*', [AuthMiddleware::class, 'handle']);

// Rutas Protegidas de Usuario

// Ver perfil
$router->get('/usuarios/perfil', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $controller = new UsuarioController($pdo);
    $respuesta = $controller->leer($decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Actualizar perfil
$router->put('/usuarios/perfil', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $data = json_decode(file_get_contents("php://input"), true);
    //validación de JSON
    $controller = new UsuarioController($pdo);
    // El controlador obtendrá el ID del usuario del token internamente.
    $respuesta = $controller->actualizar($data, $decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Eliminar perfil
$router->delete('/usuarios/perfil', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $controller = new UsuarioController($pdo);
    // El controlador obtendrá el ID y el rol del token internamente.
    $respuesta = $controller->eliminar($decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});
// Manejador para rutas no encontradas
$router->set404(function () {
    ResponseHelper::sendJson(ResponseHelper::error('Ruta no encontrada', 404));
});

$router->before('POST|GET|PUT', '/vendedores/.*', [AuthMiddleware::class, 'handle']);

// Rutas Protegidas de Vendedores

// Registro de vendedor
$router->post('/vendedores/registro', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $data = json_decode(file_get_contents("php://input"), true);
    //validación de JSON
    $controller = new VendedorController($pdo);
    // El controlador obtendrá el ID del usuario del token internamente.
    $respuesta = $controller->registrar($decoded, $data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Ver perfil vendedor
$router->get('/vendedores/perfil', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $controller = new VendedorController($pdo);
    $respuesta = $controller->leer($decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Actualizar perfil vendedor
$router->put('/vendedores/perfil', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $data = json_decode(file_get_contents("php://input"), true);
    //validación de JSON
    $controller = new VendedorController($pdo);
    // El controlador obtendrá el ID del usuario del token internamente.
    $respuesta = $controller->actualizar($data, $decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});
