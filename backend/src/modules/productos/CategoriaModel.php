<?php

namespace App\Modules\Productos;

use PDO;

class CategoriaModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Obtiene todas las categorías activas.
     *
     * @return array Lista de categorías.
     */
    public function obtenerTodasCategorias()
    {
        $sql = "SELECT id_categoria, nombre, descripcion, codigo 
                FROM categoria 
                ORDER BY id_categoria ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todas las subcategorías activas.
     *
     * @return array Lista de subcategorías con nombre de categoría.
     */
    public function obtenerTodasSubcategorias()
    {
        $sql = "SELECT s.id_subcategoria, s.nombre AS nombre_subcategoria, 
                       s.descripcion AS descripcion_subcategoria, s.codigo AS codigo_subcategoria,
                       c.nombre AS nombre_categoria, c.id_categoria
                FROM subcategoria s
                INNER JOIN categoria c ON s.id_categoria = c.id_categoria
                ORDER BY c.id_categoria ASC, s.id_subcategoria ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todas las categorías con sus subcategorías anidadas.
     *
     * @return array Lista de categorías, cada una con un array de subcategorías.
     */
    public function obtenerCategoriasConSubcategorias()
    {
        // Paso 1: Obtener todas las categorías
        $categorias = $this->obtenerTodasCategorias();
        $categoriasIndexadas = [];
        foreach ($categorias as $cat) {
            $categoriasIndexadas[$cat['id_categoria']] = [
                'id_categoria' => $cat['id_categoria'],
                'nombre' => $cat['nombre'],
                'descripcion' => $cat['descripcion'],
                'codigo' => $cat['codigo'],
                'subcategorias' => []
            ];
        }

        // Paso 2: Obtener todas las subcategorías
        $subcategorias = $this->obtenerTodasSubcategorias();

        // Paso 3: Asignar subcategorías a sus categorías
        foreach ($subcategorias as $sub) {
            if (isset($categoriasIndexadas[$sub['id_categoria']])) {
                $categoriasIndexadas[$sub['id_categoria']]['subcategorias'][] = [
                    'id_subcategoria' => $sub['id_subcategoria'],
                    'nombre' => $sub['nombre_subcategoria'],
                    'descripcion' => $sub['descripcion_subcategoria'],
                    'codigo' => $sub['codigo_subcategoria']
                ];
            }
        }

        // Paso 4: Devolver como array indexado numéricamente
        return array_values($categoriasIndexadas);
    }
}
