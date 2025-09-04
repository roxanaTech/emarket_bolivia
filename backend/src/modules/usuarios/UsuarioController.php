<?php

namespace App\Modules\Usuarios;

use App\Utils\ResponseHelper;
use App\Utils\Validator;
use PDOException;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UsuarioController
{
    private $model;

    public function __construct($pdo)
    {
        $this->model = new UsuarioModel($pdo);
    }
    /**
     * Registra un nuevo usuario
     * @param array $data Datos del usuario a registrar
     * @return array Respuesta con estado y datos
     */
    public function registrar($data)
    {
        $errores = Validator::validarUsuario($data);

        if (!empty($errores)) {
            return ResponseHelper::validationError($errores);
        }

        return $this->model->crear($data);
    }

    /**
     * Autentica a un usuario y genera un token JWT
     * @param array $data Datos de login (email y password)
     * @return array Respuesta con estado, token y datos del usuario
     */
    public function login($data)
    {
        $errores = Validator::validarLogin($data);

        if (!empty($errores)) {
            return ResponseHelper::validationError($errores);
        }

        try {
            $usuario = $this->model->autenticar($data['email'], $data['password']);
            if ($usuario) {
                $key = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
                $payload = [
                    'iss' => 'emarket',
                    'sub' => $usuario['id_usuario'],
                    'iat' => time(),
                    'exp' => time() + (60 * 60), // Expira en 1 hora
                    'data' => [
                        'nombres' => $usuario['nombres'],
                        'rol' => $usuario['rol']
                    ]
                ];
                $jwt = JWT::encode($payload, $key, 'HS256');
                return ResponseHelper::success('Login exitoso', [
                    'token' => $jwt,
                    'id_usuario' => $usuario['id_usuario'],
                    'nombres' => $usuario['nombres'],
                    'rol' => $usuario['rol']
                ]);
            } else {
                return ResponseHelper::error('Credenciales inválidas', 401);
            }
        } catch (PDOException $e) {
            return ResponseHelper::databaseError($e->getMessage());
        }
    }

    /**
     * Recupera los datos de un usuario autenticado
     * @param string $authHeader Encabezado de autorización con el token JWT
     * @return array Respuesta con estado y datos del usuario
     */
    public function leer($payload)
    {
        $id_usuario = $payload->sub;
        return $this->model->recuperar($id_usuario);
    }

    /**
     * Actualiza los datos de un usuario autenticado
     * @param string $authHeader Encabezado de autorización con el token JWT
     * @param array $data Nuevos datos del usuario
     * @return array Respuesta con estado
     */
    public function actualizar($data, $payload)
    {
        $id_usuario = $payload->sub;

        return $this->model->modificar($id_usuario, [
            'nombres' => trim($data['nombres'] ?? ''),
            'apellidos' => trim($data['apellidos'] ?? ''),
            'email' => trim($data['email'] ?? ''),
            'telefono' => trim($data['telefono'] ?? null),
            'ci_nit' => trim($data['ci_nit'] ?? null)
        ]);
    }

    /**
     * Elimina un usuario (lógica o físicamente) según el rol
     * @param string $authHeader Encabezado de autorización con el token JWT
     * @param string $tipo Tipo de eliminación: 'logico' o 'fisico'
     * @return array Respuesta con estado
     */
    public function eliminar($payload)
    {
        $id_usuario = $payload->sub;
        return $this->model->eliminarCuenta($id_usuario);
    }
}
