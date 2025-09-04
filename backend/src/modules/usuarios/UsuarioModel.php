<?php

namespace App\Modules\Usuarios;

use App\Utils\ResponseHelper;
use PDOException;
use PDO;
use Exception;

class UsuarioModel
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Crea un nuevo usuario en la base de datos
     * @param string $nombres
     * @param string $apellidos
     * @param string $email
     * @param string $password
     * @param string|null $ci_nit
     * @param string|null $telefono
     * @return array Respuesta con estado y datos
     */
    public function crear($data)
    {
        try {
            $this->db->beginTransaction();
            // 1. Insertar usuario

            // Verificar si el email ya existe
            if ($this->emailExiste($data['email'])) {
                return ResponseHelper::duplicateError('email');
            }
            if (!empty($ci_nit) && $this->ciNitExiste($data['ci_nit'])) {
                return ResponseHelper::duplicateError('ci_nit');
            }


            $sql = "INSERT INTO usuario (nombres,apellidos, email, password, telefono, ci_nit) VALUES (?, ?, ?,?, ?, ?)";
            $stmtUsuario = $this->db->prepare($sql);

            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

            $stmtUsuario->execute([$data['nombres'], $data['apellidos'], $data['email'],  $passwordHash, $data['telefono'] ?? '', $data['ci_nit'] ?? '']);
            $idUsuario = $this->db->lastInsertId();

            $idDireccionPrincipal = null;

            // 2. Insertar direcciones
            foreach ($data['direcciones'] as $index => $dir) {
                $stmtDireccion = $this->db->prepare("
                INSERT INTO direccion (id_usuario, departamento, provincia, ciudad, zona, calle, numero, referencias)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
                $stmtDireccion->execute([
                    $idUsuario,
                    $dir['departamento'] ?? '',
                    $dir['provincia'] ?? '',
                    $dir['ciudad'] ?? '',
                    $dir['zona'] ?? '',
                    $dir['calle'] ?? '',
                    $dir['numero'] ?? '',
                    $dir['referencias'] ?? ''
                ]);

                $idInsertada = $this->db->lastInsertId();

                // Si es la primera dirección o está marcada como principal
                if ($index === 0 || !empty($dir['principal'])) {
                    $idDireccionPrincipal = $idInsertada;
                }
            }

            // 3. Actualizar dirección principal en usuario
            if ($idDireccionPrincipal) {
                $stmtUpdate = $this->db->prepare("
                UPDATE usuario SET id_direccion_principal = ? WHERE id_usuario = ?
            ");
                $stmtUpdate->execute([$idDireccionPrincipal, $idUsuario]);
            }

            $this->db->commit();
            return ResponseHelper::success(
                'Usuario registrado exitosamente',
                'user_id:',
                $idUsuario
            );
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ResponseHelper::duplicateError('email o ci_nit');
            }
            return ResponseHelper::databaseError($e->getMessage());
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Recupera un usuario y todas sus direcciones asociadas.
     * @param int $id_usuario El ID del usuario a buscar.
     * @return array Resultado de la operación.
     */
    public function recuperar($id_usuario)
    {
        try {
            // Consulta SQL que une la tabla de usuarios con la de direcciones.
            // Usamos LEFT JOIN para obtener el usuario incluso si no tiene direcciones.
            $sql = "SELECT 
                    u.id_usuario, u.nombres, u.apellidos, u.email, u.telefono, u.ci_nit, u.id_direccion_principal, u.estado,
                    d.id_direccion, d.departamento, d.provincia, d.ciudad, d.zona, d.calle, d.numero, d.referencias
                FROM 
                    usuario u
                LEFT JOIN 
                    direccion d ON u.id_usuario = d.id_usuario
                WHERE 
                    u.id_usuario = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_usuario]);

            // Obtenemos todos los resultados. Si un usuario tiene 3 direcciones, obtendremos 3 filas.
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Si no se encontró ningún registro para ese ID, retornamos un error.
            if (empty($resultados)) {
                return ResponseHelper::error('Usuario no encontrado', 404);
            }

            // Estructuramos la respuesta para que no se repitan los datos del usuario.
            // El usuario será el objeto principal y sus direcciones estarán en un array anidado.
            $usuario = [
                'id_usuario' => $resultados[0]['id_usuario'],
                'nombres' => $resultados[0]['nombres'],
                'apellidos' => $resultados[0]['apellidos'],
                'email' => $resultados[0]['email'],
                'telefono' => $resultados[0]['telefono'],
                'ci_nit' => $resultados[0]['ci_nit'],
                'id_direccion_principal' => $resultados[0]['id_direccion_principal'],
                'estado' => $resultados[0]['estado'],
                'direcciones' => [] // Inicializamos el array de direcciones.
            ];

            // Recorremos los resultados para agrupar las direcciones.
            foreach ($resultados as $fila) {
                // Si la fila actual tiene datos de una dirección (id_direccion no es null).
                if ($fila['id_direccion']) {
                    $usuario['direcciones'][] = [
                        'id_direccion' => $fila['id_direccion'],
                        'departamento' => $fila['departamento'],
                        'provincia' => $fila['provincia'],
                        'ciudad' => $fila['ciudad'],
                        'zona' => $fila['zona'],
                        'calle' => $fila['calle'],
                        'numero' => $fila['numero'],
                        'referencias' => $fila['referencias']
                    ];
                }
            }

            return ResponseHelper::success('Usuario encontrado', $usuario);
        } catch (PDOException $e) {
            // En caso de un error con la base de datos, lo reportamos.
            return ResponseHelper::databaseError($e->getMessage());
        }
    }
    /**
     * Actualiza la información de un usuario y sus direcciones.
     * @param int $id_usuario El ID del usuario a actualizar.
     * @param array $datos Los nuevos datos del usuario, incluyendo un array de 'direcciones'.
     * @return array Resultado de la operación.
     */

    public function modificar($idUsuario, $datos)
    {
        $this->db->beginTransaction();

        try {
            // Validaciones previas
            if (isset($datos['email']) && $this->emailExiste($datos['email'], $idUsuario)) {
                return ResponseHelper::duplicateError('email');
            }

            if (isset($datos['ci_nit']) && !empty($datos['ci_nit']) && $this->ciNitExiste($datos['ci_nit'], $idUsuario)) {
                return ResponseHelper::duplicateError('ci_nit');
            }

            // 1. Actualizar datos del usuario
            $sqlUsuario = "UPDATE usuario SET nombres = ?, apellidos = ?, email = ?, telefono = ?, ci_nit = ? WHERE id_usuario = ?";
            $stmtUsuario = $this->db->prepare($sqlUsuario);
            $stmtUsuario->execute([
                $datos['nombres'] ?? '',
                $datos['apellidos'] ?? '',
                $datos['email'] ?? '',
                $datos['telefono'] ?? null,
                $datos['ci_nit'] ?? null,
                $idUsuario
            ]);

            // 2. Eliminar referencia a dirección principal para evitar error de integridad
            $stmtNull = $this->db->prepare("UPDATE usuario SET id_direccion_principal = NULL WHERE id_usuario = ?");
            $stmtNull->execute([$idUsuario]);

            // 3. Borrar direcciones antiguas
            $stmtDelete = $this->db->prepare("DELETE FROM direccion WHERE id_usuario = ?");
            $stmtDelete->execute([$idUsuario]);

            $idDireccionPrincipal = null;

            // 4. Insertar nuevas direcciones
            foreach ($datos['direcciones'] as $index => $dir) {
                $stmtDireccion = $this->db->prepare("
                INSERT INTO direccion (id_usuario, departamento, provincia, ciudad, zona, calle, numero, referencias)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
                $stmtDireccion->execute([
                    $idUsuario,
                    $dir['departamento'] ?? '',
                    $dir['provincia'] ?? '',
                    $dir['ciudad'] ?? '',
                    $dir['zona'] ?? '',
                    $dir['calle'] ?? '',
                    $dir['numero'] ?? '',
                    $dir['referencias'] ?? ''
                ]);

                $idInsertada = $this->db->lastInsertId();

                // Detectar si esta es la dirección principal
                if (!empty($dir['principal'])) {
                    $idDireccionPrincipal = $idInsertada;
                }
            }

            // 5. Actualizar dirección principal si se definió
            if ($idDireccionPrincipal) {
                $stmtUpdate = $this->db->prepare("
                UPDATE usuario SET id_direccion_principal = ? WHERE id_usuario = ?
            ");
                $stmtUpdate->execute([$idDireccionPrincipal, $idUsuario]);
            }

            $this->db->commit();
            return ResponseHelper::success('Usuario actualizado exitosamente');
        } catch (Exception $e) {
            $this->db->rollBack();
            return ResponseHelper::databaseError($e->getMessage());
        }
    }


    /**
     * Elimina un usuario, ya sea de forma lógica o física.
     * @param int $id_usuario El ID del usuario a eliminar.
     * @param string $tipo El tipo de eliminación: 'logico' o 'fisico'. Por defecto es 'logico'.
     * @param string $rol_usuario El rol del usuario que realiza la acción ('usuario', 'admin', 'vendedor').
     * @return array Resultado de la operación.
     */
    public function borrar($id_usuario, $tipo = 'logico', $rol_usuario = 'usuario')
    {
        if ($tipo === 'fisico') {
            // --- ELIMINACIÓN FÍSICA ---
            // Primero, verificamos si el usuario tiene permisos para esta acción.
            if ($rol_usuario !== 'admin') {
                return ResponseHelper::error('Acceso denegado. Se requieren privilegios de administrador.', 403);
            }

            // Usamos una transacción porque vamos a borrar de dos tablas.
            $this->db->beginTransaction();
            try {
                // Es importante borrar primero los registros de la tabla hija ('direccion')
                // para evitar errores de restricción de clave foránea.
                $stmtDireccion = $this->db->prepare("DELETE FROM direccion WHERE id_usuario = ?");
                $stmtDireccion->execute([$id_usuario]);

                // Ahora borramos el registro de la tabla principal ('usuario').
                $stmtUsuario = $this->db->prepare("DELETE FROM usuario WHERE id_usuario = ?");
                $stmtUsuario->execute([$id_usuario]);

                // Confirmamos la eliminación.
                $this->db->commit();
                return ResponseHelper::success('Usuario eliminado permanentemente.');
            } catch (PDOException $e) {
                // Si algo falla, revertimos la eliminación.
                $this->db->rollBack();
                return ResponseHelper::databaseError($e->getMessage());
            }
        } else {
            // --- ELIMINACIÓN LÓGICA (POR DEFECTO) ---
            try {
                // Simplemente actualizamos el estado del usuario a 'inactivo'.
                $sql = "UPDATE usuario SET estado = 'inactivo' WHERE id_usuario = ?";
                $stmt = $this->db->prepare($sql);

                if ($stmt->execute([$id_usuario])) {
                    return ResponseHelper::success('Usuario desactivado exitosamente.');
                }

                return ResponseHelper::error('No se pudo desactivar el usuario.', 500);
            } catch (PDOException $e) {
                return ResponseHelper::databaseError($e->getMessage());
            }
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
     * Verifica si un email ya existe en la base de datos.
     * Puede opcionalmente excluir un ID de usuario de la búsqueda.
     *
     * @param string $email El email a verificar.
     * @param int|null $id_usuario_a_excluir El ID del usuario a excluir de la búsqueda (útil para actualizaciones).
     * @return bool True si el email existe, false en caso contrario.
     */
    private function emailExiste($email, $id_usuario_a_excluir = null)
    {
        // 1. Iniciar la consulta base y los parámetros.
        $sql = "SELECT COUNT(*) as count FROM usuario WHERE email = ?";
        $params = [$email];

        // 2. Si se proporciona un ID para excluir, se modifica la consulta.
        if ($id_usuario_a_excluir !== null) {
            // Añadimos la condición para que no tome en cuenta al usuario que estamos actualizando.
            $sql .= " AND id_usuario != ?";
            // Añadimos el ID a la lista de parámetros para la consulta preparada.
            $params[] = $id_usuario_a_excluir;
        }

        // 3. Preparar y ejecutar la consulta.
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        // 4. Retornar si el conteo es mayor a cero.
        return $result['count'] > 0;
    }

    /**
     * Verifica si un CI o NIT ya existe en la base de datos.
     * Puede opcionalmente excluir un ID de usuario de la búsqueda.
     *
     * @param string $ci_nit El CI o NIT a verificar.
     * @param int|null $id_usuario_a_excluir El ID del usuario a excluir de la búsqueda.
     * @return bool True si el CI/NIT existe, false en caso contrario.
     */
    private function ciNitExiste($ci_nit, $id_usuario_a_excluir = null)
    {
        // La lógica es idéntica a la de emailExiste.
        $sql = "SELECT COUNT(*) as count FROM usuario WHERE ci_nit = ?";
        $params = [$ci_nit];

        if ($id_usuario_a_excluir !== null) {
            $sql .= " AND id_usuario != ?";
            $params[] = $id_usuario_a_excluir;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }
}
