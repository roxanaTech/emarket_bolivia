<?php

namespace App\Modules\Productos;

use PDO;

class ProductoModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Registra un nuevo producto en la tabla 'producto'.
     *
     * @param array $data Los datos del producto (nombre, descripción, etc.).
     * @return int|false El ID del producto insertado o false si falla.
     */
    public function registrarProducto(array $data, $idVendedor): int|false
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO producto (id_subcategoria, id_vendedor, nombre, descripcion, marca, precio, stock, sku,estado_producto, color, modelo, peso, dimensiones, fecha_publicacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $data['id_subcategoria'],
                $idVendedor,
                $data['nombre'],
                $data['descripcion'],
                $data['marca'],
                $data['precio'],
                $data['stock'],
                $data['sku'],
                $data['estado_producto'],
                $data['color'] ?? null,
                $data['modelo'] ?? null,
                $data['peso'] ?? null,
                $data['dimensiones'] ?? null
            ]);

            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Error al registrar el producto: " . $e->getMessage());
            return false;
        }
    }
    public function actualizarProducto(int $productId, array $data): bool
    {
        try {
            $sql = "UPDATE producto SET nombre = ?, descripcion = ?, marca = ?, precio = ?, stock = ?, 
            estado_producto = ?, id_subcategoria = ?, sku = ?,
            color = ?, modelo = ?, peso = ?, dimensiones = ?
            WHERE id_producto = ?";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['nombre'],
                $data['descripcion'],
                $data['marca'],
                $data['precio'],
                $data['stock'],
                $data['estado_producto'],
                $data['id_subcategoria'],
                $data['sku'],
                $data['color'] ?? null,
                $data['modelo'] ?? null,
                $data['peso'] ?? null,
                $data['dimensiones'] ?? null,
                $productId
            ]);
        } catch (\PDOException $e) {
            error_log("Error al actualizar el producto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza el código de un producto.
     *
     * @param int $idProducto El ID del producto.
     * @param string $CodProducto El código único generado.
     * @return bool
     */
    public function actualizarCodigoProducto(int $idProducto, string $CodProducto): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE producto SET codigo = ? WHERE id_producto = ?");
            return $stmt->execute([$CodProducto, $idProducto]);
        } catch (\PDOException $e) {
            error_log("Error al actualizar el código del producto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Inserta una imagen en la tabla 'imagen' y la vincula a un producto.
     *
     * @param int $productId El ID del producto.
     * @param string $imagePath La ruta de la imagen.
     * @param int $vendedorId El ID del vendedor.
     * @return int|false El ID de la imagen insertada o false si falla.
     */
    public function vincularImagenes(int $productId, string $imagePath, int $vendedorId): int|false
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO imagen (id_producto, ruta, id_vendedor) VALUES (?, ?, ?)");
            $stmt->execute([$productId, $imagePath, $vendedorId]);
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Error al vincular la imagen: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza el id_imagen_principal en la tabla 'producto'.
     *
     * @param int $productId El ID del producto.
     * @param int $mainImageId El ID de la imagen principal.
     * @return bool
     */
    public function vincularImagenPrincipal(int $productId, int $mainImageId): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE producto SET id_imagen_principal = ? WHERE id_producto = ?");
            return $stmt->execute([$mainImageId, $productId]);
        } catch (\PDOException $e) {
            error_log("Error al vincular la imagen principal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un producto de la base de datos (se usa para revertir una creación fallida).
     *
     * @param int $idProducto El ID del producto a eliminar.
     * @return bool
     */
    public function eliminarProducto($idProducto): bool
    {
        $stmtNull = $this->db->prepare("UPDATE producto SET id_imagen_principal = NULL WHERE id_producto = ?");
        $stmtNull->execute([$idProducto]);
        try {
            $this->db->prepare("DELETE FROM producto WHERE id_producto = ?")->execute([$idProducto]);
            return true;
        } catch (\PDOException $e) {
            error_log("Error al eliminar el producto: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Elimina los registros de imágenes de un producto.
     *
     * @param int $idProducto El ID del producto.
     * @return bool
     */
    public function eliminarImagenesPorProducto(int $idProducto): bool
    {
        $stmtNull = $this->db->prepare("UPDATE producto SET id_imagen_principal = NULL WHERE id_producto = ?");
        $stmtNull->execute([$idProducto]);
        try {
            $this->db->prepare("DELETE FROM imagen WHERE id_producto = ?")->execute([$idProducto]);
            return true;
        } catch (\PDOException $e) {
            error_log("Error al eliminar las imágenes del producto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Recupera los datos básicos de un producto.
     *
     * @param int $idProducto El ID del producto.
     * @return array|false Los datos del producto o false si no se encuentra.
     */
    public function recuperarProducto(int $idProducto): array|false
    {
        try {
            $sql = "SELECT * FROM producto WHERE id_producto = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idProducto]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al recuperar el producto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Recupera las rutas de todas las imágenes de un producto.
     *
     * @param int $productId El ID del producto.
     * @return array Un array de objetos con el id_imagen y la ruta.
     */
    public function obtenerImagenes(int $productId): array
    {
        try {
            $sql = "SELECT id_imagen, ruta FROM imagen WHERE id_producto = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$productId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener las imágenes del producto: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Recupera la lista de productos de un vendedor publica, ordenados por subcategoría.
     *
     * @param int $idVendedor El ID del vendedor.
     * @return array La lista de productos.
     */
    public function obtenerProductosPorIdVendedor(int $idVendedor, int $pagina = 1, int $por_pagina = 10): array
    {
        try {
            $offset = ($pagina - 1) * $por_pagina;
            $sql = "SELECT 
                    p.*,
                    s.nombre AS nombre_subcategoria,
                    i.ruta AS imagen_principal_ruta
                FROM producto p
                JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
                LEFT JOIN imagen i ON p.id_imagen_principal = i.id_imagen
                WHERE p.id_vendedor = ? AND p.estado = 'activo'
                ORDER BY p.id_producto
                LIMIT ? OFFSET ?";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(1, $idVendedor, PDO::PARAM_INT);
            $stmt->bindValue(2, $por_pagina, PDO::PARAM_INT);
            $stmt->bindValue(3, $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener productos por vendedor con paginación: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Recupera la lista de productos de una subcategoria, 
     * ordenados por fecha de publicacion dec.
     *
     * @param string $subcategoria El id de la subcategoria.
     * @return array La lista de productos.
     */
    public function obtenerProductosPorIDSubcategoria(string $idSubcategoria, int $pagina = 1, int $por_pagina = 10): array
    {
        try {
            $offset = ($pagina - 1) * $por_pagina;
            $sql = "SELECT 
                    p.id_producto,
                    p.id_vendedor,
                    p.nombre,
                    p.descripcion,
                    p.marca,
                    p.precio,
                    p.stock,
                    p.estado_producto,
                    p.fecha_publicacion,
                    p.promedio_calificacion,
                    p.total_opiniones,
                    s.nombre AS nombre_subcategoria,
                    i.ruta AS imagen_principal_ruta
                FROM producto p
                JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
                LEFT JOIN imagen i ON p.id_imagen_principal = i.id_imagen
                WHERE s.id_subcategoria = ? AND p.estado = 'activo'
                ORDER BY p.fecha_publicacion DESC
                LIMIT ? OFFSET ?";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(1, $idSubcategoria, PDO::PARAM_STR);
            $stmt->bindValue(2, $por_pagina, PDO::PARAM_INT);
            $stmt->bindValue(3, $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener productos por subcategoría con paginación: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Recupera la lista de productos de una categoria, 
     * ordenados por fecha de publicacion dec.
     *
     * @param string $categoria El id de la categoria.
     * @return array La lista de productos.
     */
    public function obtenerProductosPorIDCategoria(string $idCategoria, int $pagina = 1, int $por_pagina = 10): array
    {
        try {
            $offset = ($pagina - 1) * $por_pagina;
            $sql = "SELECT 
                    p.id_producto,
                    p.id_vendedor,
                    p.nombre,
                    p.descripcion,
                    p.marca,
                    p.precio,
                    p.stock,
                    p.estado_producto,
                    p.fecha_publicacion,
                    p.promedio_calificacion,
                    p.total_opiniones,
                    s.nombre AS nombre_subcategoria,
                    c.nombre AS nombre_categoria,
                    i.ruta AS imagen_principal_ruta
                FROM producto p
                JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
                JOIN categoria c ON s.id_categoria = c.id_categoria
                LEFT JOIN imagen i ON p.id_imagen_principal = i.id_imagen
                WHERE c.id_categoria = ? AND p.estado = 'activo'
                ORDER BY p.fecha_publicacion DESC, s.nombre ASC
                LIMIT ? OFFSET ?";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(1, $idCategoria, PDO::PARAM_STR);
            $stmt->bindValue(2, $por_pagina, PDO::PARAM_INT);
            $stmt->bindValue(3, $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener productos por categoría con paginación: " . $e->getMessage());
            return [];
        }
    }


    /**
     * Recupera la lista de productos de un vendedor, ordenados por subcategoría.
     *
     * @param array $palabras Las palabras que ingrese al buscador.
     * @return array La lista de productos.
     */
    public function buscarProductosPorPalabras(array $palabras, int $pagina = 1, int $por_pagina = 10): array
    {
        try {
            $offset = ($pagina - 1) * $por_pagina;
            $sql = "SELECT 
                    p.id_producto,
                    p.nombre,
                    p.descripcion,
                    p.marca,
                    p.precio,
                    p.estado_producto,
                    p.fecha_publicacion,
                    p.promedio_calificacion,
                    p.total_opiniones,
                    s.nombre AS nombre_subcategoria,
                    i.ruta AS imagen_principal_ruta
                FROM producto p
                JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
                LEFT JOIN imagen i ON p.id_imagen_principal = i.id_imagen
                WHERE p.estado = 'activo'";

            $params = [];
            foreach ($palabras as $palabra) {
                $sql .= " AND (
                        LOWER(p.nombre) LIKE ? OR
                        LOWER(p.descripcion) LIKE ? OR
                        LOWER(p.marca) LIKE ?
                    )";
                $params[] = '%' . $palabra . '%';
                $params[] = '%' . $palabra . '%';
                $params[] = '%' . $palabra . '%';
            }

            $sql .= " ORDER BY p.fecha_publicacion DESC LIMIT ? OFFSET ?";
            $params[] = $por_pagina;
            $params[] = $offset;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en búsqueda por palabras con paginación: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca productos en la base de datos usando filtros dinámicos.
     *
     * @param array $filtros Los filtros de búsqueda.
     * @return array La lista de productos encontrados.
     */
    public function buscarProductosPorFiltros(array $filtros, int $pagina = 1, int $por_pagina = 10): array
    {
        try {
            $offset = ($pagina - 1) * $por_pagina;
            $sql = "SELECT 
                p.id_producto,
                p.id_vendedor,
                p.nombre,
                p.descripcion,
                p.marca,
                p.precio,
                p.stock,
                p.estado_producto,
                p.fecha_publicacion,
                p.promedio_calificacion,
                p.total_opiniones,
                s.nombre AS nombre_subcategoria,
                c.nombre AS nombre_categoria,
                i.ruta AS imagen_principal_ruta
        FROM producto p
        JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
        JOIN categoria c ON s.id_categoria = c.id_categoria
        LEFT JOIN imagen i ON p.id_imagen_principal = i.id_imagen
         WHERE p.estado IN ('activo','agotado')";

            $params = [];

            // 1. Búsqueda por término simple (q=...)
            if (!empty($filtros['terminoBusqueda'])) {
                $sql .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
                $termino = "%{$filtros['terminoBusqueda']}%";
                $params[] = $termino;
                $params[] = $termino;
            }

            // 2. Búsqueda por palabras múltiples (palabras=[...])
            if (!empty($filtros['palabras']) && is_array($filtros['palabras'])) {
                foreach ($filtros['palabras'] as $palabra) {
                    $palabra = trim($palabra);
                    if ($palabra !== '') {
                        $sql .= " AND (
                        LOWER(p.nombre) LIKE ? OR
                        LOWER(p.descripcion) LIKE ? OR
                        LOWER(p.marca) LIKE ?
                    )";
                        $like = '%' . strtolower($palabra) . '%';
                        $params[] = $like;
                        $params[] = $like;
                        $params[] = $like;
                    }
                }
            }

            // Resto de filtros (categoría, marca, precio, etc.)
            if (!empty($filtros['id_subcategoria'])) {
                $sql .= " AND p.id_subcategoria = ?";
                $params[] = $filtros['id_subcategoria'];
            }

            if (!empty($filtros['id_categoria'])) {
                $sql .= " AND s.id_categoria = ?";
                $params[] = $filtros['id_categoria'];
            }

            if (!empty($filtros['marca'])) {
                $placeholders = implode(',', array_fill(0, count($filtros['marca']), '?'));
                $sql .= " AND p.marca IN ($placeholders)";
                $params = array_merge($params, $filtros['marca']);
            }

            if (isset($filtros['precio_min']) && is_numeric($filtros['precio_min'])) {
                $sql .= " AND p.precio >= ?";
                $params[] = $filtros['precio_min'];
            }

            if (isset($filtros['precio_max']) && is_numeric($filtros['precio_max'])) {
                $sql .= " AND p.precio <= ?";
                $params[] = $filtros['precio_max'];
            }

            if (isset($filtros['calificacion_min']) && is_numeric($filtros['calificacion_min'])) {
                $sql .= " AND p.promedio_calificacion >= ?";
                $params[] = $filtros['calificacion_min'];
            }

            if (!empty($filtros['estado_producto'])) {
                $sql .= " AND p.estado_producto = ?";
                $params[] = $filtros['estado_producto'];
            }

            if (isset($filtros['disponible']) && $filtros['disponible'] === true) {
                $sql .= " AND p.stock > 0";
            }
            // Filtro: productos en oferta (vinculados a un evento activo y vigente)
            if (!empty($filtros['en_oferta']) && $filtros['en_oferta'] === true) {
                $sql .= " AND EXISTS (
                            SELECT 1
                            FROM producto_evento pe
                            INNER JOIN evento e ON pe.id_evento = e.id_evento
                            WHERE pe.id_producto = p.id_producto
                            AND pe.estado_vinculacion = 'activo'
                            AND e.estado = 'activo'
                            AND e.fecha_inicio IS NOT NULL
                            AND e.fecha_vencimiento IS NOT NULL
                            AND e.fecha_inicio <= CURDATE()
                            AND e.fecha_vencimiento >= CURDATE()
                        )";
            }

            $sql .= " ORDER BY p.fecha_publicacion DESC LIMIT ? OFFSET ?";
            $params[] = $por_pagina;
            $params[] = $offset;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al buscar productos por filtros con paginación: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualiza un campo específico de un producto.
     *
     * @param int $idProducto El ID del producto a actualizar.
     * @param string $campo El nombre del campo a actualizar.
     * @param mixed $valor El nuevo valor del campo.
     * @return bool
     */
    public function modificarCampoProducto(int $idProducto, string $campo, $valor): bool
    {
        $allowedFields = ['stock', 'precio', 'estado'];

        if (!in_array($campo, $allowedFields)) {
            error_log("Intento de actualizar un campo no permitido: {$campo}");
            return false;
        }

        try {
            $sql = "UPDATE producto SET `{$campo}` = ? WHERE id_producto = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$valor, $idProducto]);
        } catch (\PDOException $e) {
            error_log("Error al actualizar el campo '{$campo}' del producto #{$idProducto}: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Actualiza promedio_calificacion y total_opiniones de un producto.
     */
    public function actualizarEstadisticasProducto(int $idProducto): bool
    {
        try {
            $sql = "
            UPDATE producto p
            SET 
                total_opiniones = (
                    SELECT COUNT(*) 
                    FROM review r 
                    WHERE r.id_producto = p.id_producto
                ),
                promedio_calificacion = (
                    SELECT COALESCE(AVG(r.calificacion), 0) 
                    FROM review r 
                    WHERE r.id_producto = p.id_producto
                )
            WHERE p.id_producto = ?
        ";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$idProducto]);
        } catch (\PDOException $e) {
            error_log("Error al actualizar estadísticas del producto: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Recupera la lista de productos más destacados, ordenados por calificación.
     *
     * @return array La lista de productos.
     */
    public function productosMasDestacados(): array
    {
        try {
            $sql = "SELECT 
                        p.id_producto,
                        p.nombre,
                        p.descripcion,
                        p.marca,
                        p.precio,
                        p.estado,
                        p.fecha_publicacion,
                        p.promedio_calificacion,
                        p.total_opiniones,
                        i.ruta AS imagen_principal_ruta
                    FROM producto p
                    LEFT JOIN imagen i ON p.id_imagen_principal = i.id_imagen
                    WHERE p.total_opiniones > 0
                  AND p.estado = 'activo'
                ORDER BY p.promedio_calificacion DESC, p.total_opiniones DESC
                LIMIT 10";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener productos destacados: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Recupera los campos indicadores de calificacion del producto
     *
     * @param string $idProducto
     * @return array Los campos promedio y total de opiniones
     */
    public function obtenerCalificacionPorIDProducto($idProducto): array
    {
        try {
            $sql = "SELECT 
                    p.id_producto,
                    p.promedio_calificacion,
                    p.total_opiniones
                FROM producto p
                WHERE p.id_producto = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idProducto]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener productos por nombre por categoría: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Obtiene las marcas más usadas en productos activos,
     * filtradas opcionalmente por categoría o subcategoría.
     *
     * @param array $filtros Debe contener opcionalmente 'id_categoria' o 'id_subcategoria'
     * @param int $limite Número máximo de marcas a devolver (por defecto 20)
     * @return array Lista de marcas con su cantidad de productos
     */
    public function obtenerMarcasMasUsadas(array $filtros = [], int $limite = 10): array
    {
        try {
            $limite = max(1, min($limite, 100));

            $sql = "SELECT 
                p.marca,
                COUNT(*) AS cantidad
            FROM producto p
            JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
            JOIN categoria c ON s.id_categoria = c.id_categoria
            WHERE p.estado = 'activo'";

            $params = [];

            // ✅ 1. Búsqueda por término simple (q)
            if (!empty($filtros['terminoBusqueda'])) {
                $sql .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
                $termino = "%{$filtros['terminoBusqueda']}%";
                $params[] = $termino;
                $params[] = $termino;
            }

            // ✅ 2. Búsqueda por palabras múltiples
            if (!empty($filtros['palabras']) && is_array($filtros['palabras'])) {
                foreach ($filtros['palabras'] as $palabra) {
                    $palabra = trim($palabra);
                    if ($palabra !== '') {
                        $sql .= " AND (
                        LOWER(p.nombre) LIKE ? OR
                        LOWER(p.descripcion) LIKE ? OR
                        LOWER(p.marca) LIKE ?
                    )";
                        $like = '%' . strtolower($palabra) . '%';
                        $params[] = $like;
                        $params[] = $like;
                        $params[] = $like;
                    }
                }
            }

            // ✅ 3. Filtros de categoría/subcategoría (si existen)
            if (!empty($filtros['id_subcategoria'])) {
                $sql .= " AND p.id_subcategoria = ?";
                $params[] = $filtros['id_subcategoria'];
            } elseif (!empty($filtros['id_categoria'])) {
                $sql .= " AND s.id_categoria = ?";
                $params[] = $filtros['id_categoria'];
            }

            // ✅ 4. Filtros adicionales (marca, precio, etc.)
            if (!empty($filtros['marca'])) {
                $placeholders = implode(',', array_fill(0, count($filtros['marca']), '?'));
                $sql .= " AND p.marca IN ($placeholders)";
                $params = array_merge($params, $filtros['marca']);
            }

            if (isset($filtros['precio_min']) && is_numeric($filtros['precio_min'])) {
                $sql .= " AND p.precio >= ?";
                $params[] = $filtros['precio_min'];
            }

            if (isset($filtros['precio_max']) && is_numeric($filtros['precio_max'])) {
                $sql .= " AND p.precio <= ?";
                $params[] = $filtros['precio_max'];
            }

            if (isset($filtros['calificacion_min']) && is_numeric($filtros['calificacion_min'])) {
                $sql .= " AND p.promedio_calificacion >= ?";
                $params[] = $filtros['calificacion_min'];
            }

            if (!empty($filtros['estado_producto'])) {
                $sql .= " AND p.estado_producto = ?";
                $params[] = $filtros['estado_producto'];
            }

            if (isset($filtros['disponible']) && $filtros['disponible'] === true) {
                $sql .= " AND p.stock > 0";
            }

            $sql .= " 
            GROUP BY p.marca
            HAVING p.marca IS NOT NULL AND p.marca != ''
            ORDER BY cantidad DESC
            LIMIT ?";

            $params[] = $limite;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener marcas más usadas con filtros: " . $e->getMessage());
            return [];
        }
    }
    // Contar productos por vendedor
    public function contarProductosPorIdVendedor(int $idVendedor): int
    {
        try {
            $sql = "SELECT COUNT(*) 
                FROM producto p 
                WHERE p.id_vendedor = ? AND p.estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idVendedor]);
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Error al contar productos por vendedor: " . $e->getMessage());
            return 0;
        }
    }

    // Contar productos por subcategoría
    public function contarProductosPorIDSubcategoria(string $idSubcategoria): int
    {
        try {
            $sql = "SELECT COUNT(*) 
                FROM producto p 
                JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
                WHERE s.id_subcategoria = ? AND p.estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idSubcategoria]);
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Error al contar productos por subcategoría: " . $e->getMessage());
            return 0;
        }
    }

    // Contar productos por categoría
    public function contarProductosPorIDCategoria(string $idCategoria): int
    {
        try {
            $sql = "SELECT COUNT(*) 
                FROM producto p
                JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
                JOIN categoria c ON s.id_categoria = c.id_categoria
                WHERE c.id_categoria = ? AND p.estado = 'activo'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idCategoria]);
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Error al contar productos por categoría: " . $e->getMessage());
            return 0;
        }
    }

    // Contar productos por búsqueda de palabras
    public function contarProductosPorPalabras(array $palabras): int
    {
        try {
            $sql = "SELECT COUNT(*) 
                FROM producto p
                JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
                WHERE p.estado = 'activo'";

            $params = [];
            foreach ($palabras as $palabra) {
                $sql .= " AND (
                        LOWER(p.nombre) LIKE ? OR
                        LOWER(p.descripcion) LIKE ? OR
                        LOWER(p.marca) LIKE ?
                    )";
                $params[] = '%' . $palabra . '%';
                $params[] = '%' . $palabra . '%';
                $params[] = '%' . $palabra . '%';
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Error al contar productos por palabras: " . $e->getMessage());
            return 0;
        }
    }

    // Contar productos por filtros
    public function contarProductosPorFiltros(array $filtros): int
    {
        try {
            $sql = "SELECT COUNT(*) 
                FROM producto p
                JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
                WHERE p.estado IN ('activo','agotado')";

            $params = [];

            if (!empty($filtros['terminoBusqueda'])) {
                $sql .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
                $terminoBusqueda = "%{$filtros['terminoBusqueda']}%";
                $params[] = $terminoBusqueda;
                $params[] = $terminoBusqueda;
            }
            // 2. Búsqueda por palabras múltiples (palabras=[...])
            if (!empty($filtros['palabras']) && is_array($filtros['palabras'])) {
                foreach ($filtros['palabras'] as $palabra) {
                    $palabra = trim($palabra);
                    if ($palabra !== '') {
                        $sql .= " AND (
                        LOWER(p.nombre) LIKE ? OR
                        LOWER(p.descripcion) LIKE ? OR
                        LOWER(p.marca) LIKE ?
                    )";
                        $like = '%' . strtolower($palabra) . '%';
                        $params[] = $like;
                        $params[] = $like;
                        $params[] = $like;
                    }
                }
            }

            if (!empty($filtros['id_subcategoria'])) {
                $sql .= " AND p.id_subcategoria = ?";
                $params[] = $filtros['id_subcategoria'];
            }

            if (!empty($filtros['marca'])) {
                if (is_array($filtros['marca'])) {
                    $placeholders = implode(',', array_fill(0, count($filtros['marca']), '?'));
                    $sql .= " AND p.marca IN ($placeholders)";
                    foreach ($filtros['marca'] as $marca) {
                        $params[] = $marca;
                    }
                } else {
                    $sql .= " AND p.marca = ?";
                    $params[] = $filtros['marca'];
                }
            }


            if (isset($filtros['precio_min']) && is_numeric($filtros['precio_min'])) {
                $sql .= " AND p.precio >= ?";
                $params[] = $filtros['precio_min'];
            }

            if (isset($filtros['precio_max']) && is_numeric($filtros['precio_max'])) {
                $sql .= " AND p.precio <= ?";
                $params[] = $filtros['precio_max'];
            }

            if (!empty($filtros['id_categoria'])) {
                $sql .= " AND s.id_categoria = ?";
                $params[] = $filtros['id_categoria'];
            }

            if (isset($filtros['calificacion_min']) && is_numeric($filtros['calificacion_min'])) {
                $sql .= " AND p.promedio_calificacion >= ?";
                $params[] = $filtros['calificacion_min'];
            }
            if (isset($filtros['calificacion_min']) && is_numeric($filtros['calificacion_min'])) {
                $sql .= " AND p.promedio_calificacion >= ?";
                $params[] = $filtros['calificacion_min'];
            }

            // Filtro por estado_producto
            if (!empty($filtros['estado_producto'])) {
                $sql .= " AND p.estado_producto = ?";
                $params[] = $filtros['estado_producto'];
            }

            // Filtro por disponibilidad
            if (isset($filtros['disponible']) && $filtros['disponible'] === true) {
                $sql .= " AND p.stock > 0";
            }
            if (!empty($filtros['en_oferta']) && $filtros['en_oferta'] === true) {
                $sql .= " AND EXISTS (
                            SELECT 1
                            FROM producto_evento pe
                            INNER JOIN evento e ON pe.id_evento = e.id_evento
                            WHERE pe.id_producto = p.id_producto
                            AND pe.estado_vinculacion = 'activo'
                            AND e.estado = 'activo'
                            AND e.fecha_inicio IS NOT NULL
                            AND e.fecha_vencimiento IS NOT NULL
                            AND e.fecha_inicio <= CURDATE()
                            AND e.fecha_vencimiento >= CURDATE()
                        )";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Error al contar productos por filtros: " . $e->getMessage());
            return 0;
        }
    }
}
