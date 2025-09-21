<?php

use App\Modules\Usuarios\UsuarioController;
use App\Modules\Vendedores\VendedorController;
use App\Modules\Productos\ProductoController;
use App\Utils\ResponseHelper;
use App\Middlewares\AuthMiddleware;
use App\Modules\Eventos\EventoController;
use App\Modules\Eventos\ProductoEventoController;

error_log('Cargando rutas en web.php');

// Rutas Públicas
// ===================================

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

// Login de usuario
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

// Ruta pública para ver producto por ID (no requiere autenticación)
$router->post('/productos/verProducto', function () use ($pdo) {
    $data = json_decode(file_get_contents("php://input"), true);
    $idProducto = $data['id_producto'] ?? null;

    if (!$idProducto) {
        ResponseHelper::sendJson(ResponseHelper::error('ID de producto requerido', 400));
        return;
    }

    $controller = new ProductoController($pdo);
    $respuesta = $controller->getProducto($idProducto);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});
// Listar productos del un vendedor
$router->get('/productos/listarPorVendedor', function () use ($pdo) {
    $data = json_decode(file_get_contents("php://input"), true);
    $idVendedor = $data['id_vendedor'] ?? null;

    $controller = new ProductoController($pdo);
    $respuesta = $controller->getListaProductosPorVendedor($idVendedor);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});
// Listar productos de una subcategoria
$router->get('/productos/listarPorSubcategoria', function () use ($pdo) {
    $data = json_decode(file_get_contents("php://input"), true);
    $subcategoria = $data['subcategoria'] ?? null;

    $controller = new ProductoController($pdo);
    $respuesta = $controller->getListaProductosPorNombreSubcategoria($subcategoria);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});
// Listar productos de una categoria
$router->get('/productos/listarPorCategoria', function () use ($pdo) {
    $data = json_decode(file_get_contents("php://input"), true);
    $categoria = $data['categoria'] ?? null;

    $controller = new ProductoController($pdo);
    $respuesta = $controller->getListaProductosPorNombreCategoria($categoria);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});
// Listar productos por su nombre parcial
$router->get('/productos/listarPorNombre', function () use ($pdo) {
    $data = json_decode(file_get_contents("php://input"), true);
    $nombre = $data['nombre'] ?? null;

    $controller = new ProductoController($pdo);
    $respuesta = $controller->getListaProductosPorNombreParcial($nombre);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});
// Listar productos destacados
$router->get('/productos/listarDestacados', function () use ($pdo) {
    $controller = new ProductoController($pdo);
    $respuesta = $controller->getListaProductosMasDestacados();
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});
// Ruta pública para buscar productos por filtros 
$router->post('/productos/buscar', function () use ($pdo) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        ResponseHelper::sendJson(ResponseHelper::error('JSON inválido', 400));
        return;
    }
    $controller = new ProductoController($pdo);
    $respuesta = $controller->buscarProductosPorFiltros($data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Middleware para rutas protegidas
// ===================================

$router->before('GET|PUT|DELETE', '/usuarios/.*', [AuthMiddleware::class, 'handle']);
$router->before('POST|GET|PUT', '/vendedores/.*', [AuthMiddleware::class, 'handle']);

$router->before('POST', '/productos/registro', [AuthMiddleware::class, 'handle']);
$router->before('POST', '/productos/actualizar', [AuthMiddleware::class, 'handle']);
$router->before('DELETE', '/productos/eliminar', [AuthMiddleware::class, 'handle']);

$router->before('GET', '/productos/listarMisProductos', [AuthMiddleware::class, 'handle']);
$router->before('POST', '/productos/actualizar-campo', [AuthMiddleware::class, 'handle']);
$router->before('POST|GET|PUT|DELETE', '/eventos/.*', [AuthMiddleware::class, 'handle']);
// Rutas Protegidas de Usuario
// ===================================

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
    $controller = new UsuarioController($pdo);
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
    $respuesta = $controller->eliminar($decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Rutas Protegidas de Vendedores
// ===================================

// Registro de vendedor
$router->post('/vendedores/registro', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $data = json_decode(file_get_contents("php://input"), true);
    $controller = new VendedorController($pdo);
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
    $controller = new VendedorController($pdo);
    $respuesta = $controller->actualizar($data, $decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Rutas Protegidas de Productos
// ===================================

// Registro de producto
$router->post('/productos/registro', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $data = $_POST;
    $files = $_FILES['images'] ?? [];

    $controller = new ProductoController($pdo);
    $respuesta = $controller->registrar($decoded, $files, $data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Registro de producto
$router->post('/productos/actualizar', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $data = $_POST;
    $files = $_FILES['images'] ?? [];
    /*ResponseHelper::sendJson(ResponseHelper::error(
        'ID de producto no proporcionado.',
        400,
        ['datos' => $files]
    ));*/
    $controller = new ProductoController($pdo);
    $respuesta = $controller->actualizarProducto($decoded, $files, $data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

//elimnar un producto
$router->delete('/productos/eliminar', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $data = json_decode(file_get_contents("php://input"), true);
    $idProducto = $data['id_producto'] ?? null;

    $controller = new ProductoController($pdo);
    $respuesta = $controller->deleteProducto($idProducto);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});


// Listar productos propios del vendedor
$router->get('/productos/listarMisProductos', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $controller = new ProductoController($pdo);
    $respuesta = $controller->getListaProductosPropiosPorVendedor($decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Actualizar campo específico de un producto
$router->post('/productos/actualizar-campo', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $data = json_decode(file_get_contents("php://input"), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        ResponseHelper::sendJson(ResponseHelper::error('JSON inválido', 400));
        return;
    }

    $controller = new ProductoController($pdo);
    $respuesta = $controller->actualizarCampo($data, $decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Rutas Protegidas de Eventos
// ===================================
// Registro de evento
$router->post('/eventos/registrar', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $data = json_decode(file_get_contents("php://input"), true);
    /*ResponseHelper::sendJson(ResponseHelper::error(
        'ID de producto no proporcionado.',
        400,
        ['datos' => $data]
    ));*/
    $controller = new EventoController($pdo);
    $respuesta = $controller->registrar($decoded, $data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Ver evento
$router->get('/eventos/evento', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $data = json_decode(file_get_contents("php://input"), true);
    $controller = new EventoController($pdo);
    $respuesta = $controller->obtenerPorId($data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Actualizar evento
$router->put('/eventos/actualizar', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $data = json_decode(file_get_contents("php://input"), true);
    $controller = new EventoController($pdo);
    $respuesta = $controller->actualizar($data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Listar eventos del vendedor
$router->get('/eventos/listarEventos', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $data = json_decode(file_get_contents("php://input"), true);
    $controller = new EventoController($pdo);
    $respuesta = $controller->listarMisEventos($decoded, $data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Actualizar campo estado de evento
$router->post('/eventos/actualizar-estado', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $data = json_decode(file_get_contents("php://input"), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        ResponseHelper::sendJson(ResponseHelper::error('JSON inválido', 400));
        return;
    }

    $controller = new EventoController($pdo);
    $respuesta = $controller->cambiarEstado($data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

//eliminar un evento
$router->delete('/eventos/eliminar', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $data = json_decode(file_get_contents("php://input"), true);

    $controller = new EventoController($pdo);
    $respuesta = $controller->eliminar($data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

//vincular productos a eventos
$router->post('/eventos/vincular', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $data = json_decode(file_get_contents("php://input"), true);

    $controller = new ProductoEventoController($pdo);
    $respuesta = $controller->vincularProductosAEvento($data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});
//cambiar estado de producto vinculado a evento
$router->post('/eventos/estado-producto-vinculado', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $data = json_decode(file_get_contents("php://input"), true);

    $controller = new ProductoEventoController($pdo);
    $respuesta = $controller->cambiarEstadoVinculo($data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});
//eliminar producto vinculado a evento
$router->delete('/eventos/eliminar-producto-vinculado', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $data = json_decode(file_get_contents("php://input"), true);

    $controller = new ProductoEventoController($pdo);
    $respuesta = $controller->eliminar($data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});


// Manejador para rutas no encontradas
$router->set404(function () {
    ResponseHelper::sendJson(ResponseHelper::error('Ruta no encontrada', 404));
});
