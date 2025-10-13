<?php

namespace App\Modules\Usuarios;

use App\Utils\ResponseHelper;
use App\Utils\Validator;
use PDOException;
use PDO;
use Exception;

class UsuarioModel
{
    private $db;
    private $validator;

    public function __construct($pdo)
    {
        $this->db = $pdo;
        $this->validator = new Validator($this->db);
    }

    /**
     * Crea un nuevo usuario en la base de datos
     * @param string $nombres
     * @param string $email
     * @param string $password
     * @param string|null $telefono
     * @return array Respuesta con estado y datos
     */
    public function crear($data, $idUsuario)
    {
        try {
            $this->db->beginTransaction();
            // 1. Insertar usuario

            // Verificar si el email ya existe
            if ($this->validator->datoExiste('usuario', 'email', $data['email'])) {
                return ResponseHelper::duplicateError('email');
            }

            $sql = "INSERT INTO usuario (nombres, email, password, telefono) VALUES ( ?, ?,?, ?)";
            $stmtUsuario = $this->db->prepare($sql);

            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

            $stmtUsuario->execute([$data['nombres'], $data['email'],  $passwordHash, $data['telefono'] ?? '']);
            $idUsuario = $this->db->lastInsertId();

            $this->db->commit();
            return ResponseHelper::success(
                'Usuario registrado exitosamente',
                'user_id:',
                $idUsuario
            );
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ResponseHelper::duplicateError('email');
            }
            return ResponseHelper::databaseError($e->getMessage());
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Recupera un usuario.
     * @param int $id_usuario El ID del usuario a buscar.
     * @return array Resultado de la operación.
     */
    public function recuperar($id_usuario)
    {
        try {
            $sql = "SELECT 
                    id_usuario, nombres, email, telefono, estado
                FROM 
                    usuario 
                WHERE 
                    id_usuario = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_usuario]);

            // Obtenemos todos los resultados.
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Si no se encontró ningún registro para ese ID, retornamos un error.
            if (empty($resultados)) {
                return ResponseHelper::error('Usuario no encontrado', 404);
            }

            // Estructuramos la respuesta para que no se repitan los datos del usuario.
            // El usuario será el objeto principal
            $usuario = [
                'id_usuario' => $resultados[0]['id_usuario'],
                'nombres' => $resultados[0]['nombres'],
                'email' => $resultados[0]['email'],
                'telefono' => $resultados[0]['telefono'],
                'estado' => $resultados[0]['estado']
            ];

            return ResponseHelper::success('Usuario encontrado', $usuario);
        } catch (PDOException $e) {
            // En caso de un error con la base de datos, lo reportamos.
            return ResponseHelper::databaseError($e->getMessage());
        }
    }
    /**
     * Actualiza la información de un usuario.
     * @param int $id_usuario El ID del usuario a actualizar.
     * @return array Resultado de la operación.
     */
    public function modificar($idUsuario, $datos)
    {
        $this->db->beginTransaction();

        try {
            // Validaciones previas
            if ($this->validator->datoExiste('usuario', 'email', $datos['email'], 'id_usuario', $idUsuario)) {
                return ResponseHelper::duplicateError('email');
            }

            // 1. Actualizar datos del usuario
            $sqlUsuario = "UPDATE usuario SET nombres = ?, email = ?, telefono = ? WHERE id_usuario = ?";
            $stmtUsuario = $this->db->prepare($sqlUsuario);
            $stmtUsuario->execute([
                $datos['nombres'] ?? '',
                $datos['email'] ?? '',
                $datos['telefono'] ?? null,
                $idUsuario
            ]);

            $this->db->commit();
            return ResponseHelper::success('Usuario actualizado exitosamente');
        } catch (Exception $e) {
            $this->db->rollBack();
            return ResponseHelper::databaseError($e->getMessage());
        }
    }

    /**
     * Desactiva (bloquea) un usuario cambiando su estado a 'false'.
     * @param int $id_usuario ID del usuario a desactivar.
     * @param string $rol_usuario Rol del usuario que realiza la acción.
     * @return array Resultado de la operación.
     */
    public function desactivarUsuario($id_usuario, $rol_usuario = 'usuario')
    {
        if ($rol_usuario !== 'admin') {
            return ResponseHelper::error('Acceso denegado. Solo los administradores pueden desactivar usuarios.', 403);
        }

        try {
            $sql = "UPDATE usuario SET estado = 'false' WHERE id_usuario = ?";
            $stmt = $this->db->prepare($sql);

            if ($stmt->execute([$id_usuario])) {
                return ResponseHelper::success('Usuario desactivado exitosamente.');
            }

            return ResponseHelper::error('No se pudo desactivar el usuario.', 500);
        } catch (PDOException $e) {
            return ResponseHelper::databaseError($e->getMessage());
        }
    }

    /**
     * Elimina físicamente la cuenta del usuario (autoborrado).
     * @param int $id_usuario ID del usuario a eliminar.
     * @param int $id_solicitante ID del usuario que realiza la acción.
     * @param string $rol_usuario Rol del solicitante.
     * @return array Resultado de la operación.
     */
    public function eliminarCuenta($id_usuario)
    {

        $this->db->beginTransaction();
        try {
            $stmtUsuario = $this->db->prepare("DELETE FROM usuario WHERE id_usuario = ?");
            $stmtUsuario->execute([$id_usuario]);

            $this->db->commit();
            return ResponseHelper::success('Tu cuenta ha sido eliminada permanentemente.');
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ResponseHelper::databaseError($e->getMessage());
        }
    }

    /**
     * Autentica a un usuario verificando email y contraseña
     * @param string $email
     * @param string $password
     * @return array|null Datos del usuario o null si las credenciales son inválidas
     */
    public function autenticar($email, $password)
    {
        $sql = "SELECT id_usuario, password, rol, nombres FROM usuario WHERE email = ? ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {
            return $usuario;
        }
        return null;
    }


    /**
     * Obtiene los datos de un usuario por su ID desde la base de datos.
     * @param int $id_usuario El ID del usuario.
     * @return array|null Los datos del usuario o null si no se encuentra.
     */
    public function obtenerUsuarioPorId($id_usuario)
    {
        $sql = "SELECT id_usuario, nombres, rol FROM usuario WHERE id_usuario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    /**
     * Actualizar la ruta de imagen del perfil de usuario.
     * @param int $id_usuario El ID del usuario.
     * @return array|null Los datos del usuario o null si no se encuentra.
     */
    public function actualizarImagenPerfil($idUsuario, $ruta)
    {
        // Guardar en base de datos
        $sql = "UPDATE usuario SET imagen_perfil = :nombre WHERE id_usuario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $ruta);
        $stmt->bindParam(':id', $idUsuario);

        if ($stmt->execute()) {
            $urlPublica = "/uploads/usuarios/" . $ruta;
            return ResponseHelper::success('Imagen subida exitosamente.', ['url' => $urlPublica]);
        } else {
            // Intentar borrar el archivo si falla la BD
            $rutaArchivo = __DIR__ . '/../../../public/uploads/usuarios/' . $ruta;
            if (file_exists($rutaArchivo)) {
                unlink($rutaArchivo);
            }
            return ResponseHelper::error('Error al registrar la imagen en la base de datos.', 500);
        }
    }
    public function restablecerAvatar($idUsuario)
    {
        $avatarPorDefecto = "https://i.pravatar.cc/150?img=" . ($idUsuario % 70 + 1);
        $sql = "UPDATE usuario SET imagen_perfil = ? WHERE id_usuario = ?";
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute([$avatarPorDefecto, $idUsuario])) {
            return ResponseHelper::success('Avatar restablecido al predeterminado');
        }
        return ResponseHelper::error('Error al restablecer el avatar', 500);
    }
    public function obtenerPorId(int $idUsuario): ?array
    {
        $sql = "SELECT id_usuario, imagen_perfil FROM usuario WHERE id_usuario = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idUsuario]);
        return $stmt->fetch() ?: null;
    }
    public function obtenerImagenPerfil(int $idUsuario): ?array
    {
        $sql = "SELECT imagen_perfil FROM usuario WHERE id_usuario = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idUsuario]);
        return $stmt->fetch() ?: null;
    }
}
