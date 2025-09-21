<?php

namespace App\Services;

use App\Modules\Vendedores\VendedorModel;
use App\Modules\Productos\ProductoModel;
use App\Modules\Eventos\EventoModel;
use App\Modules\Eventos\ProductoEventoModel;
use App\Utils\ResponseHelper;

class ProductoService
{

    private $vendedorModel;
    private $productoModel;
    private $eventoModel;
    private $productoEventoModel;

    public function __construct($db)
    {
        $this->vendedorModel = new VendedorModel($db);
        $this->productoModel = new ProductoModel($db);
        $this->eventoModel = new EventoModel($db);
        $this->productoEventoModel = new ProductoEventoModel($db);
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
    public function getVendedorIdPorIdUsuario(int $idUsuario): int|false
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
        $vendedorRazonSocial = $this->vendedorModel->getRazonSocialPorIdVendedor($producto['id_vendedor']);
        if (!$vendedorRazonSocial) {
            return ResponseHelper::error('Vendedor no encontrado.', 404);
        }

        // Recuperar las rutas de las imágenes
        $imagenes = $this->productoModel->getImagenes($idProducto);
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
            'nombre' => $producto['nombre'],
            'descripcion' => $producto['descripcion'],
            'marca' => $producto['marca'],
            'precio' => $producto['precio'],
            'stock' => $producto['stock'],
            'estado_producto' => $producto['estado_producto'],
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
    public function getlistaMisProductos(int $idUsuario): array
    {
        $idVendedor = $this->getVendedorIdPorIdUsuario($idUsuario);
        if (!$idVendedor) {
            return ResponseHelper::error('No se encontró un vendedor asociado a este usuario.', 404);
        }

        $productos = $this->productoModel->getMisProductosPorIdVendedor($idVendedor);

        foreach ($productos as &$producto) {
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

        return ResponseHelper::success('Productos del vendedor recuperados exitosamente.', $productos);
    }

    /**
     * Obtiene la lista de todos los productos de un vendedor.
     *
     * @param int $idVendedor El ID del usuario autenticado.
     * @return array
     */
    public function getlistaProductosPorVendedor(int $idVendedor): array
    {
        if (!$idVendedor) {
            return ResponseHelper::error('No se encontró un vendedor.', 404);
        }

        $productos = $this->productoModel->getProductosPorIdVendedor($idVendedor);
        foreach ($productos as &$producto) {
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


        return ResponseHelper::success('Productos del vendedor recuperados exitosamente.', $productos);
    }
    /**
     * Obtiene la lista de todos los productos de un vendedor.
     *
     * @param int $subcategoria El nombre de la subcategoria.
     * @return array
     */
    public function getlistaProductosPorSubcategoria($subcategoria): array
    {
        if (!$subcategoria) {
            return ResponseHelper::error('No se encontró suficientes datos.', 404);
        }

        $productos = $this->productoModel->getProductosPorNombreSubcategoria($subcategoria);
        foreach ($productos as &$producto) {
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

        return ResponseHelper::success('Productos de la subcategoria recuperados exitosamente.', $productos);
    }
    /**
     * Obtiene la lista de todos los productos de un vendedor.
     *
     * @param int $categoria El nombre de la categoria.
     * @return array
     */
    public function getlistaProductosPorCategoria($categoria): array
    {
        if (!$categoria) {
            return ResponseHelper::error('No se encontró suficientes datos.', 404);
        }

        $productos = $this->productoModel->getProductosPorNombreCategoria($categoria);
        foreach ($productos as &$producto) {
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

        return ResponseHelper::success('Productos de la categoria recuperados exitosamente.', $productos);
    }
    /**
     * Obtiene la lista de todos los productos de un vendedor.
     *
     * @param int $nombre El nombre parcial del producto
     * @return array
     */
    public function getlistaProductosPorNombre($nombre): array
    {
        if (!$nombre) {
            return ResponseHelper::error('No se encontró productos.', 404);
        }
        $palabras = preg_split('/\s+/', strtolower(trim($nombre)));
        $resultados = $this->productoModel->buscarProductosPorPalabras($palabras);
        foreach ($resultados as &$resultado) {
            $vinculacion = $this->productoEventoModel->obtenerVinculacionActivaPorProducto($resultado['id_producto']);
            if ($vinculacion) {
                $resultado['precio_promocional'] = $vinculacion['precio_promocional'];
                $resultado['evento_asociado'] = [
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
                $resultado['precio_promocional'] = null;
                $resultado['evento_asociado'] = null;
            }
        }

        return ResponseHelper::success('Productos recuperados exitosamente.', $resultados);
    }
    /**
     * Busca productos por los filtros proporcionados.
     *
     * @param array $filtros Los filtros de búsqueda.
     * @return array
     */
    public function buscarProductos(array $filtros): array
    {
        $productos = $this->productoModel->buscarProductosPorFiltros($filtros);
        foreach ($productos as &$producto) {
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

        return ResponseHelper::success('Productos encontrados.', $productos);
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
}
