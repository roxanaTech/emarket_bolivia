<?php

namespace App\Modules\Productos;

use App\Services\ImageService;
use App\Services\ProductoService;
use App\Utils\ResponseHelper;
use App\Utils\Validator;

class CategoriaController
{

    private $productoService;
    private $productoModel;
    private $categoriaModel;
    private $imageService;

    public function __construct($db)
    {
        $this->productoModel = new ProductoModel($db);
        $this->categoriaModel = new CategoriaModel($db);
    }
    /**
     * Obtiene la lista de todas las subcategorías.
     *
     * @return array Respuesta con las subcategorías.
     */
    public function listarSubcategoriasPorCategoria($categoria): array
    {
        $subcategorias = $this->categoriaModel->obtenerSubcategoriasDeCategoria($categoria);
        return ResponseHelper::success('Subcategorías obtenidas.', $subcategorias);
    }
}
