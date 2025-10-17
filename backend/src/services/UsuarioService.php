<?php

namespace App\Services;

use App\Modules\Usuarios\UsuarioModel;
use App\Utils\ResponseHelper;

class UsuarioService
{
    //constante con la ruta base donde se guardarán las imágenes. 
    //Usa __DIR__ para obtener el directorio actual del archivo
    private const UPLOAD_DIR = __DIR__ . '/../../public/uploads/usuarios/';
    private $usuarioModel;
    private $db;

    public function __construct($pdo)
    {
        $this->usuarioModel = new UsuarioModel($pdo);
        $this->db = $pdo;
    }
    public function subirImagenPerfil(array $files, int $idUsuario): array
    {
        if (!isset($files['imagen']) || !is_array($files['imagen'])) {
            return ResponseHelper::error('No se proporcionó ninguna imagen.', 400);
        }

        $usuarioActual = $this->usuarioModel->obtenerPorId($idUsuario);
        if (!$usuarioActual) {
            return ResponseHelper::error('Usuario no encontrado.', 404);
        }

        $archivo = $files['imagen'];
        $nombreGuardado = $this->guardarImagenPerfil($archivo, $idUsuario);

        if ($nombreGuardado === null) {
            return ResponseHelper::error('Error al subir la imagen.', 400);
        }

        $this->eliminarImagenAntigua($usuarioActual['imagen_perfil']);
        return $this->usuarioModel->actualizarImagenPerfil($idUsuario, $nombreGuardado);
    }

    private function eliminarImagenAntigua(?string $rutaAntigua): void
    {
        if (empty($rutaAntigua) || str_starts_with($rutaAntigua, 'http')) {
            return;
        }
        $rutaArchivo = self::UPLOAD_DIR . ltrim($rutaAntigua, '/');
        if (file_exists($rutaArchivo)) {
            unlink($rutaArchivo);
        }
    }

    private function guardarImagenPerfil(array $archivo, int $idUsuario): ?string
    {
        // Validar con el servicio
        $validacion = $this->validarImagen($archivo);
        if (!$validacion['valido']) {
            error_log("Validación de imagen fallida: " . $validacion['mensaje']);
            return null;
        }

        $extension = $validacion['extension'];
        $nombreLimpio = preg_replace('/[^a-zA-Z0-9]/', '_', $idUsuario);
        $nombreUnico = 'dev_' . $nombreLimpio . '_' . uniqid() . '.' . $extension;
        $rutaCarpeta = self::UPLOAD_DIR;
        $rutaCompleta = $rutaCarpeta . $nombreUnico;

        // Crear carpeta si no existe
        if (!is_dir($rutaCarpeta)) {
            if (!mkdir($rutaCarpeta, 0777, true)) {
                error_log("No se pudo crear la carpeta: $rutaCarpeta");
                return null;
            }
        }

        // Mover el archivo
        if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            return $nombreUnico;
        } else {
            error_log("Error al mover el archivo a: $rutaCompleta");
            return null;
        }
    }
    public function validarImagen(array $archivo): array
    {
        if (!isset($archivo['error']) || $archivo['error'] !== UPLOAD_ERR_OK) {
            return ['valido' => false, 'mensaje' => 'No se subió ninguna imagen válida.'];
        }

        $tipo = $archivo['type'];
        $tamano = $archivo['size'];
        $nombre = $archivo['name'];

        // Validar tipo MIME
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($tipo, $tiposPermitidos)) {
            return ['valido' => false, 'mensaje' => 'Solo se permiten imágenes JPG o PNG.'];
        }

        // Validar tamaño (máx. 5MB)
        if ($tamano > 5 * 1024 * 1024) {
            return ['valido' => false, 'mensaje' => 'La imagen no debe superar 5MB.'];
        }

        // Obtener extensión segura
        $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
            return ['valido' => false, 'mensaje' => 'Extensión de archivo no permitida.'];
        }

        return ['valido' => true, 'extension' => $extension];
    }
}
