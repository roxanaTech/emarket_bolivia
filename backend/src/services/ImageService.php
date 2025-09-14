<?php

namespace App\Services;

use App\Modules\Productos\ProductoModel;

class ImageService
{
    //constante con la ruta base donde se guardarán las imágenes. 
    //Usa __DIR__ para obtener el directorio actual del archivo
    private const UPLOAD_DIR = __DIR__ . '/../../public/uploads/productos/';
    private $productoModel;

    public function __construct(ProductoModel $productoModel)
    {
        $this->productoModel = $productoModel;
    }

    /**
     * Procesa la subida de imágenes de un producto, validando y guardando los archivos.
     *
     * @param array $files Un array de archivos subidos, típicamente $_FILES.
     * @param int $productId El ID del producto al que se asocian las imágenes.
     * @param int $vendedorId El ID del vendedor.
     * @return array Un array de objetos con los IDs y rutas de las imágenes, o un array de errores.
     */
    public function handleProductImages(array $files, int $productId, int $vendedorId): array
    {
        //array para errores de validacion
        $errors = [];

        // Validaciones: cantidad, formato y tamaño de las imágenes
        if (count($files['name']) === 0 || count($files['name']) > 6) {
            $errors[] = 'Debe subir entre 1 y 6 imágenes.';
        }

        $allowedFormats = ['image/jpeg', 'image/png', 'image/webp'];
        //Itera sobre los archivos subidos. 
        //Extrae el tamaño y tipo MIME de cada archivo.
        foreach ($files['tmp_name'] as $index => $tmpName) {
            $fileSize = $files['size'][$index];
            $fileType = $files['type'][$index];

            if (!in_array($fileType, $allowedFormats)) {
                $errors[] = "El archivo #{$index} tiene un formato no permitido. Solo se aceptan JPG, PNG y WebP.";
            }

            if ($fileSize < 50 * 1024 || $fileSize > 5 * 1024 * 1024) {
                $errors[] = "El archivo #{$index} tiene un tamaño fuera del rango (50 KB - 5 MB).";
            }
        }
        //Si hay errores, retorna inmediatamente el array con los mensajes.
        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        // Si no hay errores, procede a la subida y a la inserción en la base de datos
        $imageObjects = [];
        //define el directorio específico para el producto.
        $targetDir = self::UPLOAD_DIR . $productId . '/';
        //Si el directorio no existe, 
        //lo crea con permisos 0777 (lectura/escritura total).
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        //Obtiene la extensión del archivo en minúsculas.
        foreach ($files['tmp_name'] as $index => $tmpName) {
            $extension = strtolower(pathinfo($files['name'][$index], PATHINFO_EXTENSION));
            //Genera un nombre único para el archivo usando el ID del producto, timestamp y el índice.
            $fileName = "producto_{$productId}_" . time() . "_{$index}.{$extension}";
            //Define la ruta completa donde se guardará el archivo.
            $targetPath = $targetDir . $fileName;
            //Mueve el archivo desde la carpeta temporal al destino final.
            if (move_uploaded_file($tmpName, $targetPath)) {
                //Define la ruta relativa que se almacenará en la base de datos.
                $relativePath = "uploads/productos/{$productId}/{$fileName}";
                //vincula la imagen con el producto en la base de datos
                $insertedImageId = $this->productoModel->vincularImagenes(
                    $productId,
                    $relativePath,
                    $vendedorId
                );
                //Si la imagen se sube y vincula correctamente, se guarda en el array. 
                //Si falla, se registra el error correspondiente.
                if ($insertedImageId) {
                    $imageObjects[] = [
                        'id_imagen' => $insertedImageId,
                        'ruta' => $relativePath
                    ];
                } else {
                    $errors[] = "Error al insertar la imagen #{$index} en la base de datos.";
                }
            } else {
                $errors[] = "Hubo un error al subir el archivo #{$index}.";
            }
        }
        //Si hubo errores en la subida o vinculación, se devuelven. 
        //Si todo fue exitoso, se retorna el array con los datos de las imágenes.
        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        return ['images' => $imageObjects];
    }
    /**
     * Limpia las imágenes existentes y su directorio para un producto dado.
     *
     * @param int $idProducto El ID del producto.
     * @return bool
     */
    public function limpiarImagenesExistentes(int $idProducto, $delete = true): bool
    {
        try {
            if ($delete) {
                // Elimina los registros de la base de datos
                $this->productoModel->eliminarImagenesPorProducto($idProducto);
            }

            // Elimina el directorio físico y sus archivos
            $dirPath = self::UPLOAD_DIR . $idProducto;
            if (is_dir($dirPath)) {
                $files = glob("{$dirPath}/*");
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                rmdir($dirPath);
            }
            return true;
        } catch (\Exception $e) {
            error_log("Error al limpiar las imágenes del producto #{$idProducto}: " . $e->getMessage());
            return false;
        }
    }
}
