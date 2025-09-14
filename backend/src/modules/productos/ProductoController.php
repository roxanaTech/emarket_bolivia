<?php

namespace App\Modules\Productos;

use App\Services\ImageService;
use App\Services\ProductoService;
use App\Utils\ResponseHelper;
use App\Utils\Validator;

class ProductoController
{

    private $productoService;
    private $productoModel;
    private $imageService;

    public function __construct($db)
    {
        $this->productoModel = new ProductoModel($db);
        $this->productoService = new ProductoService($db);
        $this->imageService = new ImageService($this->productoModel);
    }

    /**
     * Procesa la creación de un nuevo producto, incluyendo la subida de imágenes y la designación de la imagen principal.
     *
     * @param array $data Los datos del formulario del producto.
     * @param array $files Los archivos subidos, típicamente $_FILES.
     * @return array Un array con el resultado de la operación.
     */
    public function registrar($payload, array $files, array $data): array
    {
        $idUsuario = $payload->sub;

        // Paso 1: Obtener el ID del vendedor a partir del ID del usuario
        $idVendedor = $this->productoService->getVendedorIdPorIdUsuario($idUsuario);
        if (!$idVendedor) {
            return ResponseHelper::error('No se encontró un vendedor asociado a este usuario.', 404);
        }
        $mainImageIndex = 0;


        // Paso 2: Insertar el producto en la base de datos para obtener su ID
        $idProducto = $this->productoModel->registrarProducto($data, $idVendedor);
        if (!$idProducto) {
            return ResponseHelper::error('Error al guardar el producto en la base de datos.', 500);
        }
        // Paso 2: Generar el código de producto único y actualizar el registro
        $codProducto = $this->productoService->generarCodigoProducto($idProducto);
        $this->productoModel->actualizarCodigoProducto($idProducto, $codProducto);

        // Paso 3: Manejar la subida de imágenes y vincularlas al producto
        $imageResult = $this->imageService->handleProductImages($files, $idProducto, $idVendedor);

        if (isset($imageResult['errors'])) {
            // Si hay errores en las imágenes, se revierte la creación del producto
            $this->productoModel->eliminarProducto($idProducto);
            return ResponseHelper::error('Errores al subir las imágenes.', 400, ['errors' => $imageResult['errors']]);
        }

        $imagesData = $imageResult['images'];
        if (empty($imagesData)) {
            $this->productoModel->eliminarProducto($idProducto);
            return ResponseHelper::error('No se subieron imágenes válidas.', 400);
        }

        // Paso 4: Designar la imagen principal
        $mainImageId = $imagesData[$mainImageIndex]['id_imagen'] ?? null;
        if (!$mainImageId || !isset($imagesData[$mainImageIndex])) {
            $this->productoModel->eliminarProducto($idProducto);
            return ResponseHelper::error('El índice de la imagen principal es inválido.', 400);
        }
        $this->productoModel->vincularImagenPrincipal($idProducto, $mainImageId);

        return ResponseHelper::success('Producto creado exitosamente.', ['codigo_producto' => $codProducto, 'id_producto' => $idProducto]);
    }
    /**
     * Recupera los datos de un producto para su publicación.
     *
     * @param int $idProducto El ID del producto.
     * @return array Un array con la respuesta.
     */
    public function getProducto($idProducto): array
    {
        return $this->productoService->recuperarDatosProductoParaPublicacion($idProducto);
    }

    /**
     * Procesa la actualización de un producto existente.
     *
     * @param array $data Los datos del producto y el ID.
     * @param array $files Los archivos de las imágenes.
     * @param $payload El ID del usuario autenticado.
     * @return array
     */
    public function actualizarProducto($payload, array $files, array $data): array
    {
        $idUsuario = $payload->sub;
        // Paso 1: Validar datos y permisos
        $idProducto = $data['id_producto'] ?? null;
        if (!$idProducto) {
            error_log("Datos recibidos: " . print_r($data, true));
            return ResponseHelper::error('ID de producto no proporcionado.', 400, ['datos' => $data]);
        }

        $idVendedor = $this->productoService->getVendedorIdPorIdUsuario($idUsuario);
        if (!$idVendedor) {
            return ResponseHelper::error('No autorizado. Solo el vendedor puede actualizar este producto.', 403);
        }

        $productoExistente = $this->productoModel->recuperarProducto($idProducto);
        if (!$productoExistente || $productoExistente['id_vendedor'] != $idVendedor) {
            return ResponseHelper::error('Producto no encontrado o no pertenece a este vendedor.', 404);
        }

        // Paso 2: Actualizar los datos del producto
        $updateSuccess = $this->productoModel->actualizarProducto($idProducto, $data);
        if (!$updateSuccess) {
            return ResponseHelper::error('Error al actualizar los datos del producto.', 500);
        }

        // Paso 3: Limpiar imágenes anteriores
        $this->imageService->limpiarImagenesExistentes($idProducto);

        // Paso 4: Subir las nuevas imágenes
        $imageResult = $this->imageService->handleProductImages($files, $idProducto, $idVendedor);
        if (isset($imageResult['errors'])) {
            return ResponseHelper::error('Errores al subir las nuevas imágenes.', 400, $imageResult['errors']);
        }
        $mainImageIndex = 0;
        $imagesData = $imageResult['images'];

        // Paso 5: Designar la nueva imagen principal
        $mainImageId = $imagesData[$mainImageIndex]['id_imagen'] ?? null;
        if ($mainImageId) {
            $this->productoModel->vincularImagenPrincipal($idProducto, $mainImageId);
        } else {
            return ResponseHelper::error('El índice de la imagen principal es inválido.', 400);
        }

        return ResponseHelper::success('Producto actualizado exitosamente.', ['id_producto' => $idProducto]);
    }

    /**
     * Elimina los datos de un producto.
     *
     * @param int $idProducto El ID del producto.
     * @return array Un array con la respuesta.
     */
    public function deleteProducto($idProducto)
    {
        $result = $this->productoModel->eliminarProducto($idProducto);
        $resultFolders = $this->imageService->limpiarImagenesExistentes($idProducto, false);
        if (!$result) {
            return ResponseHelper::error('Error al eliminar producto.', 400);
        }
        if (!$resultFolders) {
            return ResponseHelper::error('Error al eliminar las imagenes del producto.', 400);
        }
        return ResponseHelper::success('Producto eliminado exitosamente.', ['id_producto' => $idProducto]);
    }
    /**
     * Recupera la lista de productos propios de un vendedor.
     *
     * @param $payload El ID del usuario autenticado.
     * @return array
     */
    public function getListaProductosPropiosPorVendedor($payload): array
    {
        $idUsuario = $payload->sub;
        return $this->productoService->getlistaMisProductos($idUsuario);
    }
    /**
     * Recupera la lista de productos de un vendedor.
     *
     * @param $idVendedor El ID del vendedor autenticado.
     * @return array
     */
    public function getListaProductosPorVendedor($idVendedor): array
    {
        return $this->productoService->getlistaProductosPorVendedor($idVendedor);
    }
    /**
     * Recupera la lista de productos de una subcategoria.
     *
     * @param $subcategoria El nombre de la subcategoria.
     * @return array
     */
    public function getListaProductosPorNombreSubcategoria($subcategoria): array
    {
        return $this->productoService->getlistaProductosPorSubcategoria($subcategoria);
    }
    /**
     * Recupera la lista de productos de una categoria.
     *
     * @param int $categoria El nombre de la categoria.
     * @return array
     */
    public function getListaProductosPorNombreCategoria($categoria): array
    {
        return $this->productoService->getlistaProductosPorCategoria($categoria);
    }
    /**
     * Recupera la lista de productos por nombre parcial.
     *
     * @param int $nombreParcial El nombre parcial del producto.
     * @return array
     */
    public function getListaProductosPorNombreParcial($nombreParcial): array
    {
        return $this->productoService->getlistaProductosPorNombre($nombreParcial);
    }
    /**
     * Recupera la lista de productos mas destacados.
     *
     * @return array
     */
    public function getListaProductosMasDestacados(): array
    {
        return $this->productoModel->productosMasDestacados();
    }
    /**
     * Busca productos por los filtros proporcionados en el body.
     *
     * @param array $data Los datos de la solicitud.
     * @return array
     */
    public function buscarProductosPorFiltros(array $data): array
    {
        return $this->productoService->buscarProductos($data);
    }
    /**
     * Actualiza un campo específico de un producto.
     *
     * @param array $data Los datos de la solicitud (id_producto, campo, valor).
     * @param int $idUsuario El ID del usuario autenticado.
     * @return array
     */
    public function actualizarCampo(array $data, $payload): array
    {
        $idUsuario = $payload->sub;
        return $this->productoService->actualizarCampoDeProducto($data, $idUsuario);
    }
}
