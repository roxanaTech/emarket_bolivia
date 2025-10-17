<?php

namespace App\Services;

use App\Modules\Vendedores\VendedorModel;
use App\Modules\Productos\ProductoModel;
use App\Modules\Eventos\EventoModel;
use App\Modules\Eventos\ProductoEventoModel;
use App\Utils\ResponseHelper;
use App\Utils\Validator;

class ProductoService
{

    private $vendedorModel;
    private $productoModel;
    private $eventoModel;
    private $productoEventoModel;
    private $validator;

    public function __construct($db)
    {
        $this->vendedorModel = new VendedorModel($db);
        $this->productoModel = new ProductoModel($db);
        $this->eventoModel = new EventoModel($db);
        $this->productoEventoModel = new ProductoEventoModel($db);
        $this->validator = new Validator($db);
    }


    /**
     * Genera un código de producto único.
     *
     * @param int $productId El ID del producto para garantizar la unicidad.
     * @return string El código de producto generado.
     */
    public function generarCodigoProducto(int $productId): string
    {
        $randomPart = substr(md5(uniqid(rand(), true)), 0, 6);
        return "PROD-{$productId}-{$randomPart}";
    }

    /**
     * Recupera el ID del vendedor usando el ID del usuario.
     *
     * @param int $idUsuario El ID del usuario.
     * @return int|false El ID del vendedor o false si no se encuentra.
     */
    public function obtenerVendedorIdPorIdUsuario(int $idUsuario): int|false
    {
        $idVendedor = $this->vendedorModel->recuperarIdVendedorPorIdUsuario($idUsuario);

        // Si el valor es válido, lo retornamos directamente
        return $idVendedor !== false ? (int) $idVendedor : false;
    }
    /**
     * Recupera los datos completos del producto para su publicación, incluyendo información del vendedor e imágenes.
     *
     * @param int $idProducto El ID del producto.
     * @return array El array de datos del producto o un array de errores.
     */
    public function recuperarDatosProductoParaPublicacion(int $idProducto): array
    {
        // Recuperar datos básicos del producto
        $producto = $this->productoModel->recuperarProducto($idProducto);
        if (!$producto) {
            return ResponseHelper::error('Producto no encontrado.', 404);
        }

        // Recuperar la razón social del vendedor
        $vendedorRazonSocial = $this->vendedorModel->obtenerRazonSocialPorIdVendedor($producto['id_vendedor']);
        if (!$vendedorRazonSocial) {
            return ResponseHelper::error('Vendedor no encontrado.', 404);
        }

        // Recuperar las rutas de las imágenes
        $imagenes = $this->productoModel->obtenerImagenes($idProducto);
        if (empty($imagenes)) {
            return ResponseHelper::error('Imágenes del producto no encontradas.', 404);
        }

        // Reordenar las imágenes para que la principal esté al inicio
        $imagenPrincipalRuta = '';
        $rutasOrdenadas = [];
        foreach ($imagenes as $imagen) {
            if ($imagen['id_imagen'] == $producto['id_imagen_principal']) {
                $imagenPrincipalRuta = $imagen['ruta'];
            } else {
                $rutasOrdenadas[] = $imagen['ruta'];
            }
        }

        // Añadir la imagen principal al inicio del array
        if ($imagenPrincipalRuta) {
            array_unshift($rutasOrdenadas, $imagenPrincipalRuta);
        }

        // Consolidar todos los datos en un solo array
        $datosPublicacion = [
            'id_producto' => $producto['id_producto'],
            'id_vendedor' => $producto['id_vendedor'],
            'id_categoria' => $producto['id_categoria'],
            'categoria' => $producto['nombre_categoria'],
            'id_subcategoria' => $producto['id_subcategoria'],
            'subcategoria' => $producto['nombre_subcategoria'],
            'nombre' => $producto['nombre'],
            'descripcion' => $producto['descripcion'],
            'marca' => $producto['marca'],
            'precio' => $producto['precio'],
            'stock' => $producto['stock'],
            'sku' => $producto['sku'],
            'codigo' => $producto['codigo'],
            'estado_producto' => $producto['estado_producto'],
            'color' => $producto['color'],
            'modelo' => $producto['modelo'],
            'peso' => $producto['peso'],
            'promedio_calificacion' => $producto['promedio_calificacion'],
            'total_opiniones' => $producto['total_opiniones'],
            'fecha_publicacion' => $producto['fecha_publicacion'],
            'dimensiones' => $producto['dimensiones'],
            'razon_social' => $vendedorRazonSocial,
            'rutas_imagenes' => $rutasOrdenadas
        ];
        // Recuperar vinculación activa si existe
        $vinculacion = $this->productoEventoModel->obtenerVinculacionActivaPorProducto($idProducto);
        if ($vinculacion) {
            $datosPublicacion['precio_promocional'] = $vinculacion['precio_promocional'];
            $datosPublicacion['evento_asociado'] = [
                'id_evento' => $vinculacion['id_evento'],
                'nombre_evento' => $vinculacion['nombre_evento'],
                'tipo_aplicacion' => $vinculacion['tipo_aplicacion'],
                'valor_descuento' => $vinculacion['valor_descuento'],
                'condiciones' => $vinculacion['condiciones'],
                'fecha_inicio' => $vinculacion['fecha_inicio'],
                'fecha_vencimiento' => $vinculacion['fecha_vencimiento'],
                'fecha_vinculacion' => $vinculacion['fecha_vinculacion']
            ];
        } else {
            $datosPublicacion['precio_promocional'] = null;
            $datosPublicacion['evento_asociado'] = null;
        }
        return ResponseHelper::success('Datos del producto recuperados exitosamente.', $datosPublicacion);
    }
    /**
     * Obtiene la lista de todos los productos de un vendedor.
     *
     * @param int $idUsuario El ID del usuario autenticado.
     * @return array
     */
    public function obtenerMisProductos(int $idUsuario, int $pagina = 1, int $por_pagina = 10): array
    {
        $idVendedor = $this->obtenerVendedorIdPorIdUsuario($idUsuario);
        if (!$idVendedor) {
            return ResponseHelper::error('No se encontró un vendedor asociado a este usuario.', 404);
        }

        $total = $this->productoModel->contarProductosPorIdVendedor($idVendedor);
        if ($total === 0) {
            return $this->respuestaPaginada([], 0, $pagina, $por_pagina);
        }

        $productos = $this->productoModel->obtenerProductosPorIdVendedor($idVendedor, $pagina, $por_pagina);
        foreach ($productos as &$producto) {
            $this->enriquecerProductoConPromocion($producto);
        }

        return $this->respuestaPaginada($productos, $total, $pagina, $por_pagina, 'Productos del vendedor recuperados exitosamente.');
    }

    /**
     * Obtiene la lista de todos los productos de un vendedor.
     *
     * @param int $idVendedor El ID del usuario autenticado.
     * @return array
     */
    public function obtenerProductosPorVendedor(int $idVendedor, int $pagina = 1, int $por_pagina = 10): array
    {
        if (!$idVendedor) {
            return ResponseHelper::error('No se encontró un vendedor.', 404);
        }

        $total = $this->productoModel->contarProductosPorIdVendedor($idVendedor);
        if ($total === 0) {
            return $this->respuestaPaginada([], 0, $pagina, $por_pagina);
        }

        $productos = $this->productoModel->obtenerProductosPorIdVendedor($idVendedor, $pagina, $por_pagina);
        foreach ($productos as &$producto) {
            $this->enriquecerProductoConPromocion($producto);
        }

        return $this->respuestaPaginada($productos, $total, $pagina, $por_pagina, 'Productos del vendedor recuperados exitosamente.');
    }
    /**
     * Obtiene la lista de todos los productos de un vendedor.
     *
     * @param int $subcategoria El nombre de la subcategoria.
     * @return array
     */
    public function obtenerProductosPorSubcategoria($subcategoria, int $pagina = 1, int $por_pagina = 10): array
    {
        if (!$subcategoria) {
            return ResponseHelper::error('No se encontró suficientes datos.', 404);
        }

        $total = $this->productoModel->contarProductosPorIDSubcategoria($subcategoria);
        if ($total === 0) {
            return $this->respuestaPaginada([], 0, $pagina, $por_pagina);
        }

        $productos = $this->productoModel->obtenerProductosPorIDSubcategoria($subcategoria, $pagina, $por_pagina);
        foreach ($productos as &$producto) {
            $this->enriquecerProductoConPromocion($producto);
        }

        return $this->respuestaPaginada($productos, $total, $pagina, $por_pagina, 'Productos de la subcategoría recuperados exitosamente.');
    }
    /**
     * Obtiene la lista de todos los productos de un vendedor.
     *
     * @param int $categoria El nombre de la categoria.
     * @return array
     */
    public function obtenerProductosPorCategoria($categoria, int $pagina = 1, int $por_pagina = 10): array
    {
        if (!$categoria) {
            return ResponseHelper::error('No se encontró suficientes datos.', 404);
        }

        $total = $this->productoModel->contarProductosPorIDCategoria($categoria);
        if ($total === 0) {
            return $this->respuestaPaginada([], 0, $pagina, $por_pagina);
        }

        $productos = $this->productoModel->obtenerProductosPorIDCategoria($categoria, $pagina, $por_pagina);
        foreach ($productos as &$producto) {
            $this->enriquecerProductoConPromocion($producto);
        }

        return $this->respuestaPaginada($productos, $total, $pagina, $por_pagina, 'Productos de la categoría recuperados exitosamente.');
    }
    /**
     * Obtiene la lista de todos los productos de un vendedor.
     *
     * @param int $nombre El nombre parcial del producto
     * @return array
     */
    public function obtenerProductosPorNombre($nombre, int $pagina = 1, int $por_pagina = 10): array
    {
        if (!$nombre) {
            return ResponseHelper::error('No se encontró productos.', 404);
        }
        $palabras = preg_split('/\s+/', strtolower(trim($nombre)));

        $total = $this->productoModel->contarProductosPorPalabras($palabras);
        if ($total === 0) {
            return $this->respuestaPaginada([], 0, $pagina, $por_pagina);
        }

        $productos = $this->productoModel->buscarProductosPorPalabras($palabras, $pagina, $por_pagina);
        foreach ($productos as &$producto) {
            $this->enriquecerProductoConPromocion($producto);
        }

        return $this->respuestaPaginada($productos, $total, $pagina, $por_pagina, 'Productos recuperados exitosamente.');
    }
    /**
     * Busca productos por los filtros proporcionados.
     *
     * @param array $filtros Los filtros de búsqueda.
     * @return array
     */
    public function buscarProductos(array $filtros, int $pagina = 1, int $por_pagina = 10): array
    {
        $total = $this->productoModel->contarProductosPorFiltros($filtros);
        if ($total === 0) {
            return $this->respuestaPaginada([], 0, $pagina, $por_pagina, 'No se encontraron productos.');
        }

        $productos = $this->productoModel->buscarProductosPorFiltros($filtros, $pagina, $por_pagina);
        foreach ($productos as &$producto) {
            $this->enriquecerProductoConPromocion($producto);
        }

        return $this->respuestaPaginada($productos, $total, $pagina, $por_pagina, 'Productos encontrados.');
    }
    /**
     * Obtiene la lista de los productos destacados.
     *
     * @return array
     */
    public function obtenerProductosDestacados(): array
    {
        $productos = $this->productoModel->productosMasDestacados();
        return ResponseHelper::success('Productos Destacados.', $productos);
    }
    /**
     * Actualiza un campo específico de un producto.
     *
     * @param array $data Los datos de la solicitud (id_producto, campo, valor).
     * @param int $idUsuario El ID del usuario autenticado.
     * @return array
     */
    public function actualizarCampoDeProducto(array $data, int $idUsuario): array
    {
        $idProducto = $data['id_producto'] ?? null;
        $campo = $data['campo'] ?? null;
        $valor = $data['valor'] ?? null;

        if (!$idProducto || !$campo || !isset($valor)) {
            return ResponseHelper::error('Faltan datos para la actualización.', 400);
        }

        // 1. Verificar que el producto existe y pertenece al usuario
        $idVendedor = $this->vendedorModel->recuperarIdVendedorPorIdUsuario($idUsuario);
        if (!$idVendedor) {
            return ResponseHelper::error('No se encontró un vendedor asociado a este usuario.', 404);
        }

        $productoExistente = $this->productoModel->recuperarProducto($idProducto);
        if (!$productoExistente || $productoExistente['id_vendedor'] != $idVendedor) {
            return ResponseHelper::error('Producto no encontrado o no pertenece a este vendedor.', 404);
        }

        // 2. Actualizar el campo en la base de datos
        $updateSuccess = $this->productoModel->modificarCampoProducto($idProducto, $campo, $valor);

        if ($updateSuccess) {
            return ResponseHelper::success("Campo '{$campo}' actualizado exitosamente.", ['id_producto' => $idProducto]);
        } else {
            return ResponseHelper::error("Error al actualizar el campo '{$campo}'.", 500);
        }
    }
    /**
     * Obtiene los indicadores de calificacion de un producto.
     *
     * @return array
     */
    public function obtenerCalificacionDeProducto($idProducto): array
    {
        $calificacion = $this->productoModel->obtenerCalificacionPorIDProducto($idProducto);
        return ResponseHelper::success('Calificacion del Producto.', $calificacion);
    }
    /**
     * Obtiene las marcas mas usadas de un producto.
     *
     * @return array
     */
    public function obtenerMarcasMasUsadas(array $filtros): array
    {
        $marcas = $this->productoModel->obtenerMarcasMasUsadas($filtros);
        return ResponseHelper::success('Marcas del Producto.', $marcas);
    }

    private function enriquecerProductoConPromocion(array &$producto): void
    {
        $vinculacion = $this->productoEventoModel->obtenerVinculacionActivaPorProducto($producto['id_producto']);
        if ($vinculacion) {
            $producto['precio_promocional'] = $vinculacion['precio_promocional'];
            $producto['evento_asociado'] = [
                'id_evento' => $vinculacion['id_evento'],
                'nombre_evento' => $vinculacion['nombre_evento'],
                'tipo_aplicacion' => $vinculacion['tipo_aplicacion'],
                'valor_descuento' => $vinculacion['valor_descuento'],
                'condiciones' => $vinculacion['condiciones'],
                'fecha_inicio' => $vinculacion['fecha_inicio'],
                'fecha_vencimiento' => $vinculacion['fecha_vencimiento'],
                'fecha_vinculacion' => $vinculacion['fecha_vinculacion']
            ];
        } else {
            $producto['precio_promocional'] = null;
            $producto['evento_asociado'] = null;
        }
    }
    /**
     * Valida los datos de un producto.
     *
     * @param array $data Datos del producto.
     * @param int|null $idProducto ID si es una actualización (para excluir en unicidad).
     * @return array Lista de errores.
     */
    public function validarDatosProducto(array $data, ?int $idProducto = null): array
    {
        // Reglas base
        $reglas = [
            'nombre' => ['requerido', 'min_len:3', 'max_len:255'],
            'descripcion' => ['min_len:10'], // opcional, pero si existe, mínimo 10
            'marca' => ['requerido', 'min_len:2', 'max_len:100'],
            'precio' => ['requerido', 'numeric', 'positive'],
            'stock' => ['requerido', 'integer', 'non_negative'],
            'sku' => ['requerido', 'min_len:3', 'max_len:50', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'estado_producto' => ['requerido', 'in:nuevo,usado,reacondicionado']
        ];

        $errores = Validator::validarCampos($data, $reglas);
        return $errores;
    }
    private function respuestaPaginada(array $productos, int $total, int $pagina, int $por_pagina, string $mensaje = 'Operación exitosa.'): array
    {
        $totalPaginas = $por_pagina > 0 ? ceil($total / $por_pagina) : 0;
        return ResponseHelper::success($mensaje, [
            'productos' => $productos,
            'total' => $total,
            'pagina' => $pagina,
            'por_pagina' => $por_pagina,
            'total_paginas' => $totalPaginas
        ]);
    }
}
