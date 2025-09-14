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
            $stmt = $this->db->prepare("INSERT INTO producto (id_subcategoria, id_vendedor, nombre, descripcion, marca, precio, stock, sku,estado_producto, fecha_publicacion) VALUES (?, ?, ?, ?, ?, ?, ?,?,?, NOW())");
            $stmt->execute([
                $data['id_subcategoria'],
                $idVendedor,
                $data['nombre'],
                $data['descripcion'],
                $data['marca'],
                $data['precio'],
                $data['stock'],
                $data['sku'],
                $data['estado_producto']
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
            $sql = "UPDATE producto SET nombre = ?, descripcion = ?, marca = ?, precio = ?, stock = ?, estado_producto = ?, id_subcategoria = ?, sku = ? WHERE id_producto = ?";
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
     * @param int $productId El ID del producto.
     * @return array|false Los datos del producto o false si no se encuentra.
     */
    public function recuperarProducto(int $productId): array|false
    {
        try {
            $sql = "SELECT id_producto, id_vendedor, nombre, descripcion, marca, precio, stock, estado_producto, id_imagen_principal, id_vendedor FROM producto WHERE id_producto = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$productId]);
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
    public function getImagenes(int $productId): array
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
     * Recupera el nombre de la empresa usando el ID del usuario.
     *
     * @param int $idUsuario El ID del usuario.
     * @return int|false El nombre de la empresa o false si no se encuentra.
     */
    public function getRazonSocialPorIdUsuario($idUsuario): string|false
    {
        try {
            $sql = "SELECT razon_social FROM vendedor WHERE id_usuario = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idUsuario]);
            $result = $stmt->fetchColumn();

            if ($result === false) {
                error_log("Debug - No se encontró razon social del vendedor para id_usuario: " . $idUsuario);
            }

            return $result; // Puede ser string o false
        } catch (\PDOException $e) {
            error_log("Error al recuperar razon social del vendedor por ID de usuario: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Recupera la lista de productos propios de un vendedor, ordenados por subcategoría.
     *
     * @param int $idVendedor El ID del vendedor.
     * @return array La lista de productos.
     */
    public function getMisProductosPorIdVendedor(int $idVendedor): array
    {
        try {
            $sql = "SELECT 
                        p.id_producto,
                        p.nombre,
                        p.descripcion,
                        p.marca,
                        p.precio,
                        p.stock,
                        p.sku,
                        p.estado_producto,
                        p.estado,
                        s.nombre AS nombre_subcategoria,
                        i.ruta AS imagen_principal_ruta
                    FROM producto p
                    JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
                    LEFT JOIN imagen i ON p.id_imagen_principal = i.id_imagen
                    WHERE p.id_vendedor = ?
                    ORDER BY s.nombre";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idVendedor]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener la lista de tus productos: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Recupera la lista de productos de un vendedor publica, ordenados por subcategoría.
     *
     * @param int $idVendedor El ID del vendedor.
     * @return array La lista de productos.
     */
    public function getProductosPorIdVendedor(int $idVendedor): array
    {
        try {
            $sql = "SELECT 
                        p.id_producto,
                        p.id_vendedor,
                        p.nombre,
                        p.descripcion,
                        p.marca,
                        p.precio,
                        p.stock,
                        p.estado_producto,
                        s.nombre AS nombre_subcategoria,
                        i.ruta AS imagen_principal_ruta
                    FROM producto p
                    JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
                    LEFT JOIN imagen i ON p.id_imagen_principal = i.id_imagen
                    WHERE p.id_vendedor = ? AND p.estado like 'activo'
                    ORDER BY s.nombre";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idVendedor]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener la lista de productos: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Recupera la lista de productos de una subcategoria, 
     * ordenados por fecha de publicacion dec.
     *
     * @param string $subcategoria El nombre de la subcategoria.
     * @return array La lista de productos.
     */
    public function getProductosPorNombreSubcategoria(string $nombreSubcategoria): array
    {
        try {
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
                    s.nombre AS nombre_subcategoria,
                    i.ruta AS imagen_principal_ruta
                FROM producto p
                JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
                LEFT JOIN imagen i ON p.id_imagen_principal = i.id_imagen
                WHERE s.nombre = ? AND p.estado = 'activo'
                ORDER BY p.fecha_publicacion DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nombreSubcategoria]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener productos por nombre de subcategoría: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Recupera la lista de productos de una categoria, 
     * ordenados por fecha de publicacion dec.
     *
     * @param string $categoria El nombre de la categoria.
     * @return array La lista de productos.
     */
    public function getProductosPorNombreCategoria(string $nombreCategoria): array
    {
        try {
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
                    s.nombre AS nombre_subcategoria,
                    c.nombre AS nombre_categoria,
                    i.ruta AS imagen_principal_ruta
                FROM producto p
                JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
                JOIN categoria c ON s.id_categoria = c.id_categoria
                LEFT JOIN imagen i ON p.id_imagen_principal = i.id_imagen
                WHERE c.nombre = ? AND p.estado = 'activo'
                ORDER BY p.fecha_publicacion DESC, s.nombre ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nombreCategoria]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener productos por nombre por categoría: " . $e->getMessage());
            return [];
        }
    }


    /**
     * Recupera la lista de productos de un vendedor, ordenados por subcategoría.
     *
     * @param array $palabras Las palabras que ingrese al buscador.
     * @return array La lista de productos.
     */
    public function buscarProductosPorPalabras(array $palabras): array
    {
        try {
            $sql = "SELECT 
                    p.id_producto,
                    p.nombre,
                    p.descripcion,
                    p.marca,
                    p.precio,
                    p.estado_producto,
                    p.fecha_publicacion,
                    s.nombre AS nombre_subcategoria,
                    i.ruta AS imagen_principal_ruta
                FROM producto p
                JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
                LEFT JOIN imagen i ON p.id_imagen_principal = i.id_imagen
                WHERE p.estado = 'activo'";

            $params = [];
            foreach ($palabras as $index => $palabra) {
                $sql .= " AND (
                        LOWER(p.nombre) LIKE ? OR
                        LOWER(p.descripcion) LIKE ? OR
                        LOWER(p.marca) LIKE ?
                    )";
                $params[] = '%' . $palabra . '%';
                $params[] = '%' . $palabra . '%';
                $params[] = '%' . $palabra . '%';
            }

            $sql .= " ORDER BY p.fecha_publicacion DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en búsqueda por palabras clave: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Recupera la lista de productos mas destacados, ordenados por calificacion.
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
                    p.precio,
                    p.estado_producto,
                    AVG(r.calificacion) AS promedio_calificacion,
                    COUNT(r.id_resena) AS total_resenas
                    FROM productos p
                    JOIN resena r ON p.id_producto = r.id_producto
                    GROUP BY p.id_producto, p.nombre, p.descripcion, p.precio
                    HAVING COUNT(r.id_resena) >= 2
                    ORDER BY promedio_calificacion DESC
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
     * Busca productos en la base de datos usando filtros dinámicos.
     *
     * @param array $filtros Los filtros de búsqueda.
     * @return array La lista de productos encontrados.
     */
    public function buscarProductosPorFiltros(array $filtros): array
    {
        try {
            $sql = "SELECT 
                        p.id_producto,
                        p.nombre,
                        p.descripcion,
                        p.marca,
                        p.precio,
                        p.estado,
                        s.nombre AS nombre_subcategoria,
                        i.ruta AS imagen_principal_ruta
                    FROM producto p
                    JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
                    LEFT JOIN imagen i ON p.id_imagen_principal = i.id_imagen
                    WHERE p.estado = 'activo'";

            $params = [];

            // Construir la cláusula WHERE dinámicamente
            if (!empty($filtros['buscarTerm'])) {
                $sql .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
                $buscarTerm = "%{$filtros['buscarTerm']}%";
                $params[] = $buscarTerm;
                $params[] = $buscarTerm;
            }

            if (!empty($filtros['id_subcategoria'])) {
                $sql .= " AND p.id_subcategoria = ?";
                $params[] = $filtros['id_subcategoria'];
            }

            if (!empty($filtros['marca'])) {
                $sql .= " AND p.marca = ?";
                $params[] = $filtros['marca'];
            }

            if (isset($filtros['precio_min']) && is_numeric($filtros['precio_min'])) {
                $sql .= " AND p.precio >= ?";
                $params[] = $filtros['precio_min'];
            }

            if (isset($filtros['precio_max']) && is_numeric($filtros['precio_max'])) {
                $sql .= " AND p.precio <= ?";
                $params[] = $filtros['precio_max'];
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al buscar productos por filtros: " . $e->getMessage());
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
}
