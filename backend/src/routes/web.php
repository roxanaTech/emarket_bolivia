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
use \App\Modules\Carrito\CarritoController;
use App\Utils\RequestHelper;

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
    $data = RequestHelper::getJsonBody();
    $controller = new UsuarioController($pdo);
    $respuesta = $controller->registrar($data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Login de usuario
$router->post('/login', function () use ($pdo) {
    error_log('POST /login called');
    $data = RequestHelper::getJsonBody(); // Valida JSON automáticamente
    $controller = new UsuarioController($pdo);
    $respuesta = $controller->login($data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});
// Listar categorías
$router->get('/categorias', function () use ($pdo) {
    $controller = new ProductoController($pdo);
    $respuesta = $controller->listarCategorias();
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Listar subcategorías
$router->get('/subcategorias', function () use ($pdo) {
    $controller = new ProductoController($pdo);
    $respuesta = $controller->listarSubcategorias();
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Listar categorías con subcategorías
$router->get('/categorias-con-subcategorias', function () use ($pdo) {
    $controller = new ProductoController($pdo);
    $respuesta = $controller->listarCategoriasConSubcategorias();
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Ruta pública para ver producto por ID (no requiere autenticación)
$router->post('/productos/verProducto', function () use ($pdo) {
    $data = RequestHelper::getJsonBody();
    $idProducto = RequestHelper::requireField($data, 'id_producto', 'ID de producto requerido');
    $controller = new ProductoController($pdo);
    $respuesta = $controller->obtenerProducto($idProducto);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Listar productos de un vendedor (por ID en URL)
$router->get('/productos/listarPorVendedor/{idVendedor}', function ($idVendedor) use ($pdo) {
    $pagina = (int)($_GET['pagina'] ?? 1);
    $por_pagina = (int)($_GET['por_pagina'] ?? 10);

    // Validar que idVendedor sea numérico
    if (!is_numeric($idVendedor)) {
        ResponseHelper::sendJson(ResponseHelper::error('ID de vendedor inválido.', 400));
        return;
    }

    $pagina = max(1, $pagina);
    $por_pagina = max(1, min(100, $por_pagina)); // Máximo 100 por seguridad

    $controller = new ProductoController($pdo);
    $respuesta = $controller->getListaProductosPorVendedor((int)$idVendedor, $pagina, $por_pagina);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Listar productos de una subcategoría (por ID en URL)
$router->get('/productos/listarPorSubcategoria/{idSubcategoria}', function ($idSubcategoria) use ($pdo) {
    $pagina = (int)($_GET['pagina'] ?? 1);
    $por_pagina = (int)($_GET['por_pagina'] ?? 10);

    $pagina = max(1, $pagina);
    $por_pagina = max(1, min(100, $por_pagina));

    $controller = new ProductoController($pdo);
    $respuesta = $controller->getListaProductosPorIDSubcategoria($idSubcategoria, $pagina, $por_pagina);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Listar productos de una categoría (por ID en URL)
$router->get('/productos/listarPorCategoria/{idCategoria}', function ($idCategoria) use ($pdo) {
    $pagina = (int)($_GET['pagina'] ?? 1);
    $por_pagina = (int)($_GET['por_pagina'] ?? 10);

    $pagina = max(1, $pagina);
    $por_pagina = max(1, min(100, $por_pagina));

    $controller = new ProductoController($pdo);
    $respuesta = $controller->getListaProductosPorIDCategoria($idCategoria, $pagina, $por_pagina);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Listar productos por nombre parcial (nombre como query param)
$router->get('/productos/listarPorNombre', function () use ($pdo) {
    $nombre = $_GET['nombre'] ?? null;
    $pagina = (int)($_GET['pagina'] ?? 1);
    $por_pagina = (int)($_GET['por_pagina'] ?? 10);

    if ($nombre === null || trim($nombre) === '') {
        ResponseHelper::sendJson(ResponseHelper::error('El parámetro "nombre" es requerido y no puede estar vacío.', 400));
        return;
    }

    $pagina = max(1, $pagina);
    $por_pagina = max(1, min(100, $por_pagina));

    $controller = new ProductoController($pdo);
    $respuesta = $controller->getListaProductosPorNombreParcial($nombre, $pagina, $por_pagina);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Listar productos destacados 
$router->get('/productos/listarDestacados', function () use ($pdo) {
    $controller = new ProductoController($pdo);
    $respuesta = $controller->getListaProductosMasDestacados();
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Buscar productos por filtros (todos los filtros como query params)
$router->get('/productos/buscar', function () use ($pdo) {
    $filtros = [
        'terminoBusqueda' => $_GET['q'] ?? null,
        'palabras' => $_GET['palabras'] ?? [],
        'id_subcategoria' => $_GET['id_subcategoria'] ?? null,
        'id_categoria' => $_GET['id_categoria'] ?? null,
        'marca' => isset($_GET['marca'])
            ? (is_array($_GET['marca']) ? $_GET['marca'] : [$_GET['marca']])
            : [],
        'precio_min' => is_numeric($_GET['precio_min'] ?? null) ? (float)$_GET['precio_min'] : null,
        'precio_max' => is_numeric($_GET['precio_max'] ?? null) ? (float)$_GET['precio_max'] : null,
        'calificacion_min' => is_numeric($_GET['calificacion_min'] ?? null) ? (float)$_GET['calificacion_min'] : null,
        'estado_producto' => $_GET['estado_producto'] ?? null,
        'disponible' => isset($_GET['disponible']) ? (bool)$_GET['disponible'] : null,
        'en_oferta' => isset($_GET['en_oferta']) ? (bool)$_GET['en_oferta'] : null,
    ];
    //ResponseHelper::sendJson($filtros, $filtros['code'] ?? 200);

    $pagina = max(1, (int)($_GET['pagina'] ?? 1));
    $por_pagina = max(1, min(100, (int)($_GET['por_pagina'] ?? 10)));

    $controller = new ProductoController($pdo);
    $respuesta = $controller->buscarProductosPorFiltros($filtros, $pagina, $por_pagina);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Buscar marcas de productos por filtros
$router->get('/productos/marcas', function () use ($pdo) {
    $filtros = [
        'terminoBusqueda' => $_GET['q'] ?? null,
        'palabras' => $_GET['palabras'] ?? [],
        'id_subcategoria' => $_GET['id_subcategoria'] ?? null,
        'id_categoria' => $_GET['id_categoria'] ?? null,
        'marca' => isset($_GET['marca'])
            ? (is_array($_GET['marca']) ? $_GET['marca'] : [$_GET['marca']])
            : [],
        'precio_min' => is_numeric($_GET['precio_min'] ?? null) ? (float)$_GET['precio_min'] : null,
        'precio_max' => is_numeric($_GET['precio_max'] ?? null) ? (float)$_GET['precio_max'] : null,
        'calificacion_min' => is_numeric($_GET['calificacion_min'] ?? null) ? (float)$_GET['calificacion_min'] : null,
        'estado_producto' => $_GET['estado_producto'] ?? null,
        'disponible' => isset($_GET['disponible']) ? (bool)$_GET['disponible'] : null,

    ];
    //ResponseHelper::sendJson($filtros, $filtros['code'] ?? 200);
    $controller = new ProductoController($pdo);
    $respuesta = $controller->buscarMarcasProductos($filtros);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Listar campos de calificacion
$router->get('/productos/calificacion/{id_producto}', function ($id_producto) use ($pdo) {
    if (!is_numeric($id_producto)) {
        ResponseHelper::sendJson(ResponseHelper::error('ID de producto inválido', 400));
        return;
    }

    $controller = new ProductoController($pdo);
    $respuesta = $controller->RecuperarCalificacionPorIDProducto((int)$id_producto);
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

    $controller = new ReviewController($pdo);
    $respuesta = $controller->listarResenasPorProducto((int)$id_producto, $pagina);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});
$router->get('/vendedores/perfil/{idVendedor}', function ($idVendedor) use ($pdo) {
    $controller = new VendedorController($pdo);
    $respuesta = $controller->leer($idVendedor);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});



// Middleware para rutas protegidas
// ===================================

$router->before('POST', '/usuarios/perfil/contrasena', [AuthMiddleware::class, 'handle']);
$router->before('POST', '/usuarios/imagen', [AuthMiddleware::class, 'handle']);
$router->before('GET|PUT|DELETE', '/usuarios/.*', [AuthMiddleware::class, 'handle']);
$router->before('POST|PUT', '/vendedores/.*', [AuthMiddleware::class, 'handle']);

$router->before('POST', '/productos/registro', [AuthMiddleware::class, 'handle']);
$router->before('POST', '/productos/actualizar', [AuthMiddleware::class, 'handle']);
$router->before('DELETE', '/productos/eliminar', [AuthMiddleware::class, 'handle']);

$router->before('GET', '/productos/listarMisProductos', [AuthMiddleware::class, 'handle']);
$router->before('POST', '/productos/actualizar-campo', [AuthMiddleware::class, 'handle']);
$router->before('POST|GET|PUT|DELETE', '/eventos/.*', [AuthMiddleware::class, 'handle']);

$router->before('POST|GET|PUT|DELETE', '/ventas/.*', [AuthMiddleware::class, 'handle']);
$router->before('POST|GET|PUT|DELETE', '/devoluciones/.*', [AuthMiddleware::class, 'handle']);
$router->before('POST|GET|PUT|DELETE', '/carrito/.*', [AuthMiddleware::class, 'handle']);
$router->before('POST', '/reviews/.*', [AuthMiddleware::class, 'handle']);
$router->before('GET', '/reviews/mi-resena/.*', [AuthMiddleware::class, 'handle']);
$router->before('PUT', '/reviews/.*', [AuthMiddleware::class, 'handle']);
$router->before('DELETE', '/reviews/.*', [AuthMiddleware::class, 'handle']);

// Rutas Protegidas de Usuario
// ===================================
//actualizar contraseña
$router->post('/usuarios/perfil/contrasena', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $data = RequestHelper::getJsonBody();
    $controller = new UsuarioController($pdo);
    $respuesta = $controller->actualizarPassword($decoded, $data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

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
// obtener imagen para un usuario existente
$router->get('/usuarios/imagen', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $controller = new UsuarioController($pdo);
    $respuesta = $controller->obtenerImagenPerfil($decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});
// Subir imagen para un usuario existente
$router->post('/usuarios/imagen', function () use ($pdo) {
    error_log("[DEBUG] decoded en rutas: " . ($decoded ?? 'sin ID'));
    $decoded = $GLOBALS['auth_user'] ?? null;

    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    error_log("[DEBUG] Usuario autorizado en ruta imagen: " . print_r($decoded, true));

    $controller = new UsuarioController($pdo);
    $respuesta = $controller->actualizarImagenPerfil($_FILES, $decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// eliminar imagen para un usuario existente
$router->delete('/usuarios/imagen', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $controller = new UsuarioController($pdo);
    $respuesta = $controller->eliminarImagenPerfil($decoded);
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
    $data = RequestHelper::getJsonBody();
    $controller = new VendedorController($pdo);
    $respuesta = $controller->registrar($decoded, $data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Ver perfil vendedor


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

// Actualizar producto
$router->post('/productos/actualizar', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $data = $_POST;
    $files = $_FILES['images'] ?? [];
    $controller = new ProductoController($pdo);
    $respuesta = $controller->actualizarProducto($decoded, $files, $data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Eliminar un producto
$router->delete('/productos/eliminar', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $data = RequestHelper::getJsonBody();
    $idProducto = RequestHelper::requireField($data, 'id_producto', 'ID de producto requerido');

    $controller = new ProductoController($pdo);
    $respuesta = $controller->deleteProducto($idProducto);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Listar productos propios del vendedor autenticado
$router->get('/productos/listarMisProductos', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $pagina = (int)($_GET['pagina'] ?? 1);
    $por_pagina = (int)($_GET['por_pagina'] ?? 10);

    $pagina = max(1, $pagina);
    $por_pagina = max(1, min(100, $por_pagina));

    $controller = new ProductoController($pdo);
    $respuesta = $controller->getListaProductosPropiosPorVendedor($decoded, $pagina, $por_pagina);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Actualizar campo específico de un producto
$router->post('/productos/actualizar-campo', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $data = RequestHelper::getJsonBody();
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
    $data = RequestHelper::getJsonBody();
    $controller = new EventoController($pdo);
    $respuesta = $controller->registrar($decoded, $data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Ver evento → ahora POST (porque envía id en JSON)
$router->post('/eventos/evento', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $data = RequestHelper::getJsonBody();
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
    $data = RequestHelper::getJsonBody();
    $controller = new EventoController($pdo);
    $respuesta = $controller->actualizar($data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Listar eventos del vendedor → ahora POST (por filtros complejos)
$router->post('/eventos/listarEventos', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $filtros = RequestHelper::getJsonBody();
    $controller = new EventoController($pdo);
    $respuesta = $controller->listarMisEventos($decoded, $filtros);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Actualizar campo estado de evento
$router->post('/eventos/actualizar-estado', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $data = RequestHelper::getJsonBody();
    $controller = new EventoController($pdo);
    $respuesta = $controller->cambiarEstado($data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Eliminar un evento
$router->delete('/eventos/eliminar', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $data = RequestHelper::getJsonBody();
    $controller = new EventoController($pdo);
    $respuesta = $controller->eliminar($data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Vincular productos a eventos
$router->post('/eventos/vincular', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $data = RequestHelper::getJsonBody();
    $controller = new ProductoEventoController($pdo);
    $respuesta = $controller->vincularProductosAEvento($data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Cambiar estado de producto vinculado a evento
$router->post('/eventos/estado-producto-vinculado', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $data = RequestHelper::getJsonBody();
    $controller = new ProductoEventoController($pdo);
    $respuesta = $controller->cambiarEstadoVinculo($data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Eliminar producto vinculado a evento
$router->delete('/eventos/eliminar-producto-vinculado', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }
    $data = RequestHelper::getJsonBody();
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

    $data = RequestHelper::getJsonBody();
    $controller = new CarritoController($pdo);
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

    $controller = new CarritoController($pdo);
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
    $data = RequestHelper::getJsonBody();
    $controller = new CarritoController($pdo);
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

    $data = RequestHelper::getJsonBody();
    $controller = new CarritoController($pdo);
    $respuesta = $controller->eliminarItem($decoded, $data);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Vaciar carrito (eliminar todos los ítems)
$router->delete('/carrito/vaciar', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $controller = new CarritoController($pdo);
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

    $controller = new CarritoController($pdo);
    $respuesta = $controller->marcarComoConvertido($decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});
// contar items del carrito
$router->get('/carrito/items', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $controller = new CarritoController($pdo);
    $respuesta = $controller->sumarItems($decoded);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});
// Obtener carrito agrupado por vendedor (para finalizar compra)
$router->get('/carrito/agrupado', function () use ($pdo) {
    $decoded = $GLOBALS['auth_user'] ?? null;
    if (!$decoded) {
        ResponseHelper::sendJson(ResponseHelper::error('No autorizado', 401));
        return;
    }

    $controller = new \App\Modules\Carrito\CarritoController($pdo);
    $respuesta = $controller->obtenerCarritoAgrupado($decoded);
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

    $controller = new VentaController($pdo);
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

    $controller = new VentaController($pdo);
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

    $controller = new VentaController($pdo);
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


    $controller = new VentaController($pdo);
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
    $data = RequestHelper::getJsonBody();
    $nuevoEstado = RequestHelper::requireField($data, 'estado', 'Estado no proporcionado');

    $controller = new VentaController($pdo);
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
    $data = RequestHelper::getJsonBody();
    $comprobante = RequestHelper::requireField($data, 'comprobante', 'Comprobante no válido');
    $controller = new VentaController($pdo);
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

    $controller = new DevolucionController($pdo);
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

    $controller = new DevolucionController($pdo);
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

    $controller = new DevolucionController($pdo);
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

    $controller = new DevolucionController($pdo);
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

    $controller = new DevolucionController($pdo);
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
    $data = RequestHelper::getJsonBody();
    $comentarios = RequestHelper::requireField($data, 'comentarios', 'Falta Comentarios');

    $controller = new DevolucionController($pdo);
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

    $controller = new DevolucionController($pdo);
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

    $data = RequestHelper::getJsonBody();
    $controller = new ReviewController($pdo);
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

    $controller = new ReviewController($pdo);
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

    $controller = new ReviewController($pdo);
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

    $controller = new ReviewController($pdo);
    $respuesta = $controller->eliminarReview($decoded, (int)$id_review);
    ResponseHelper::sendJson($respuesta, $respuesta['code'] ?? 200);
});

// Manejador para rutas no encontradas
$router->set404(function () {
    ResponseHelper::sendJson(ResponseHelper::error('Ruta no encontrada', 404));
});
