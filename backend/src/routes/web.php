<?php

use App\Modules\Usuarios\UsuarioController;
use App\Modules\Vendedores\VendedorController;
use App\Modules\Productos\ProductoController;
use App\Utils\ResponseHelper;
use App\Middlewares\AuthMiddleware;
use App\Modules\Eventos\EventoController;
use App\Modules\Eventos\ProductoEventoController;
use App\Modules\Ventas\VentaController;
use App\Modules\Devoluciones\DevolucionController;
use App\Modules\Reviews\ReviewController;

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

// Listar reseñas activas de un producto (público, sin autenticación requerida)
$router->get('/reviews/producto/{id_producto}', function ($id_producto) use ($pdo) {
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    if ($pagina < 1) $pagina = 1;

    if (!is_numeric($id_producto)) {
        ResponseHelper::sendJson(ResponseHelper::error('ID de producto inválido', 400));
        return;
    }

    $controller = new \App\Modules\Reviews\ReviewController($pdo);
    $respuesta = $controller->listarResenasPorProducto((int)$id_producto, $pagina);
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

$router->before('POST|GET|PUT|DELETE', '/ventas/.*', [AuthMiddleware::class, 'handle']);
$router->before('POST|GET|PUT|DELETE', '/devoluciones/.*', [AuthMiddleware::class, 'handle']);
$router->before('POST|GET|PUT|DELETE', '/carrito/.*', [AuthMiddleware::class, 'handle']);
// Rutas protegidas de reviews (solo las que requieren autenticación)
$router->before('POST', '/reviews/.*', [AuthMiddleware::class, 'handle']); // crear
$router->before('GET', '/reviews/mi-resena/.*', [AuthMiddleware::class, 'handle']); // obtener propia
$router->before('PUT', '/reviews/.*', [AuthMiddleware::class, 'handle']); // actualizar
$router->before('DELETE', '/reviews/.*', [AuthMiddleware::class, 'handle']); // eliminar

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

// Rutas Protegidas del Carrito
// ===================================
// Agregar producto al carrito
$router->post('/carrito/agregar', function () use ($pdo) {
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

    $controller = new \App\Modules\Carrito\CarritoController($pdo);
    $respuesta = $controller->agregarProducto($decoded, $data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Listar carrito activo del usuario
$router->get('/carrito/listar', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $controller = new \App\Modules\Carrito\CarritoController($pdo);
    $respuesta = $controller->listarCarrito($decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Actualizar cantidad de un ítem en el carrito
$router->put('/carrito/actualizar-cantidad', function () use ($pdo) {
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

    $controller = new \App\Modules\Carrito\CarritoController($pdo);
    $respuesta = $controller->actualizarCantidad($decoded, $data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Eliminar un ítem del carrito
$router->delete('/carrito/eliminar-item', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $id_item = json_decode(file_get_contents("php://input"), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        ResponseHelper::sendJson(ResponseHelper::error('JSON inválido', 400));
        return;
    }

    $controller = new \App\Modules\Carrito\CarritoController($pdo);
    $respuesta = $controller->eliminarItem($decoded, $id_item);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Vaciar carrito (eliminar todos los ítems)
$router->delete('/carrito/vaciar', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $controller = new \App\Modules\Carrito\CarritoController($pdo);
    $respuesta = $controller->vaciarCarrito($decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Marcar carrito como convertido (al finalizar compra)
$router->post('/carrito/convertir', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $controller = new \App\Modules\Carrito\CarritoController($pdo);
    $respuesta = $controller->marcarComoConvertido($decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Rutas Protegidas de Ventas
// ===================================
// Crear una nueva venta (finalizar compra de un grupo de productos)
$router->post('/ventas/crear', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        ResponseHelper::sendJson(ResponseHelper::error('Datos inválidos', 400));
        return;
    }

    $controller = new \App\Modules\Ventas\VentaController($pdo);
    $respuesta = $controller->crearVenta($decoded, $input);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});



// Listar todas las ventas del comprador autenticado
$router->get('/ventas/comprador', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $controller = new \App\Modules\Ventas\VentaController($pdo);
    $respuesta = $controller->listarVentasComprador($decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200, $decoded->sub);
});

// Listar todas las ventas del vendedor autenticado
$router->get('/ventas/vendedor', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $controller = new \App\Modules\Ventas\VentaController($pdo);
    $respuesta = $controller->listarVentasVendedor($decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});
// Obtener los detalles de una venta específica
$router->get('/ventas/{id}', function ($id) use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }


    $controller = new \App\Modules\Ventas\VentaController($pdo);
    $respuesta = $controller->obtenerVenta((int)$id, $decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Actualizar el estado de una venta (pagada, enviada, cancelada, etc.)
$router->put('/ventas/{id}/estado', function ($id) use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $nuevoEstado = $input['estado'] ?? null;

    if (!$nuevoEstado || !is_string($nuevoEstado)) {
        ResponseHelper::sendJson(ResponseHelper::error('Estado no proporcionado', 400));
        return;
    }

    $controller = new \App\Modules\Ventas\VentaController($pdo);
    $respuesta = $controller->actualizarEstado((int)$id, $nuevoEstado, $decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Subir/completar comprobante de pago (para transferencia, QR, etc.)
$router->post('/ventas/{id}/comprobante', function ($id) use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $comprobante = $input['comprobante'] ?? null;

    if (!$comprobante || !is_string($comprobante)) {
        ResponseHelper::sendJson(ResponseHelper::error('Comprobante no válido', 400));
        return;
    }

    $controller = new \App\Modules\Ventas\VentaController($pdo);
    $respuesta = $controller->subirComprobante((int)$id, $comprobante, $decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Rutas Protegidas de Devoluciones
// ===================================
// Solicitar una nueva devolución
$router->post('/devoluciones/solicitar', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        ResponseHelper::sendJson(ResponseHelper::error('Datos inválidos', 400));
        return;
    }

    $controller = new \App\Modules\Devoluciones\DevolucionController($pdo);
    $respuesta = $controller->solicitarDevolucion($decoded, $input);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Listar devoluciones del comprador autenticado
$router->get('/devoluciones/comprador', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $controller = new \App\Modules\Devoluciones\DevolucionController($pdo);
    $respuesta = $controller->listarDevolucionesComprador($decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Listar devoluciones del vendedor autenticado
$router->get('/devoluciones/vendedor', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $controller = new \App\Modules\Devoluciones\DevolucionController($pdo);
    $respuesta = $controller->listarDevolucionesVendedor($decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});
// Subir imagen para una devolución existente
$router->post('/devoluciones/{id}/imagen', function ($id) use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $controller = new \App\Modules\Devoluciones\DevolucionController($pdo);
    $respuesta = $controller->subirImagenDevolucion((int)$id, $_FILES, $decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Aprobar una devolución (solo vendedor)
$router->post('/devoluciones/{id}/aprobar', function ($id) use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $controller = new \App\Modules\Devoluciones\DevolucionController($pdo);
    $respuesta = $controller->aprobarDevolucion((int)$id, $decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Rechazar una devolución (solo vendedor)
$router->post('/devoluciones/{id}/rechazar', function ($id) use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $comentarios = $input['comentarios'] ?? '';

    $controller = new \App\Modules\Devoluciones\DevolucionController($pdo);
    $respuesta = $controller->rechazarDevolucion((int)$id, $comentarios, $decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Procesar una devolución aprobada (solo vendedor)
$router->post('/devoluciones/{id}/procesar', function ($id) use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        ResponseHelper::sendJson(ResponseHelper::error('Datos inválidos', 400));
        return;
    }

    $controller = new \App\Modules\Devoluciones\DevolucionController($pdo);
    $respuesta = $controller->procesarDevolucion((int)$id, $input, $decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});
// Rutas Protegidas de Reviews (Reseñas)
// ===================================
// Crear una nueva reseña
$router->post('/reviews/crear', function () use ($pdo) {
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

    $controller = new \App\Modules\Reviews\ReviewController($pdo);
    $respuesta = $controller->crearReview($decoded, $data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Obtener la reseña propia de un usuario para un producto (para edición)
$router->get('/reviews/mi-resena/{id_producto}', function ($id_producto) use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    if (!is_numeric($id_producto)) {
        ResponseHelper::sendJson(ResponseHelper::error('ID de producto inválido', 400));
        return;
    }

    $controller = new \App\Modules\Reviews\ReviewController($pdo);
    $respuesta = $controller->obtenerReviewPropia($decoded, (int)$id_producto);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Actualizar una reseña existente
$router->put('/reviews/{id_review}', function ($id_review) use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    if (!is_numeric($id_review)) {
        ResponseHelper::sendJson(ResponseHelper::error('ID de reseña inválido', 400));
        return;
    }

    $data = json_decode(file_get_contents("php://input"), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        ResponseHelper::sendJson(ResponseHelper::error('JSON inválido', 400));
        return;
    }

    // Añadir el ID de la reseña al array de datos para el controlador
    $data['id_review'] = (int)$id_review;

    $controller = new \App\Modules\Reviews\ReviewController($pdo);
    $respuesta = $controller->actualizarReview($decoded, $data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Eliminar (lógicamente) una reseña
$router->delete('/reviews/{id_review}', function ($id_review) use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    if (!is_numeric($id_review)) {
        ResponseHelper::sendJson(ResponseHelper::error('ID de reseña inválido', 400));
        return;
    }

    $controller = new \App\Modules\Reviews\ReviewController($pdo);
    $respuesta = $controller->eliminarReview($decoded, (int)$id_review);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Manejador para rutas no encontradas
$router->set404(function () {
    ResponseHelper::sendJson(ResponseHelper::error('Ruta no encontrada', 404));
});
