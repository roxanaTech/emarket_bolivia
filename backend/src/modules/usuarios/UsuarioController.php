<?php

namespace App\Modules\Usuarios;

use App\Utils\ResponseHelper;
use App\Utils\Validator;
use PDOException;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Services\UsuarioService;


class UsuarioController
{
    private $model;
    private $service;

    public function __construct($pdo)
    {
        $this->model = new UsuarioModel($pdo);
        $this->service = new UsuarioService($pdo);
    }
    /**
     * Registra un nuevo usuario
     * @param array $data Datos del usuario a registrar
     * @return array Respuesta con estado y datos
     */
    public function registrar($data)
    {
        $reglasVendedor = [
            'nombres' => ['requerido'],
            'email' => ['requerido'],
            'password' => ['requerido']
        ];
        $reglasVendedor = [
            'email' => ['email']
        ];

        $errores = Validator::validarCampos($data, $reglasVendedor);

        if (!empty($errores)) {
            return ResponseHelper::validationError($errores);
        }

        $id_usuario = null;

        return $this->model->crear($data, $id_usuario);
    }

    /**
     * Autentica a un usuario y genera un token JWT
     * @param array $data Datos de login (email y password)
     * @return array Respuesta con estado, token y datos del usuario
     */
    public function login($data)
    {
        $reglasVendedor = [
            'email' => ['requerido'],
            'password' => ['requerido']
        ];

        $reglasVendedor = [
            'email' => ['email']
        ];

        $errores = Validator::validarCampos($data, $reglasVendedor);

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
                    'exp' => time() + (86400),
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
     * comparar contraseña de un usuario
     * @param array $data Datos de login (password)
     * @return array Respuesta con estado
     */
    public function actualizarPassword($payload, $data)
    {
        $idUsuario = $payload->sub;
        $reglasVendedor = [
            'nueva_contrasena' => ['requerido'],
            'confirmar_contrasena' => ['requerido'],
            'contrasena_actual' => ['requerido'],

        ];

        $errores = Validator::validarCampos($data, $reglasVendedor);

        if (!empty($errores)) {
            return ResponseHelper::validationError($errores);
        }
        return $this->model->modificarPassword($idUsuario, $data);
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
            'email' => trim($data['email'] ?? ''),
            'telefono' => trim($data['telefono'] ?? '')
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
    /**
     * Actualiza la imagen de perfil de un usuario
     * @param string $authHeader Encabezado de autorización con el token JWT
     * @param array $files la imagen
     * @return array Respuesta con estado
     */
    public function actualizarImagenPerfil(array $files, $payload): array
    {
        $id_usuario = $payload->sub;
        return $this->service->subirImagenPerfil($files, $id_usuario);
    }
    /**
     * Restablece la imagen de perfil de un usuario
     * @param string $authHeader Encabezado de autorización con el token JWT
     * @return array Respuesta con estado
     */
    public function eliminarImagenPerfil($payload): array
    {
        $id_usuario = $payload->sub;
        return $this->model->restablecerAvatar($id_usuario);
    }
    /**
     * Actualiza un token JWT dado un token válido.
     * @return array Respuesta con el nuevo token y datos de usuario o un error.
     */
    public function actualizarTokenPorRol($id_usuario)
    {
        return $this->model->obtenerUsuarioPorId($id_usuario);
    }
    /**
     * obtener la ruta de la imagen de perfil.
     * @return array Respuesta con el nuevo token y datos de usuario o un error.
     */
    public function obtenerImagenPerfil($payload)
    {
        $id_usuario = $payload->sub;
        return $this->model->obtenerImagenPerfil($id_usuario);
    }
}
