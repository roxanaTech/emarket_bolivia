<?php

namespace App\Modules\Vendedores;

use App\Modules\Usuarios\UsuarioController;
use App\Utils\ResponseHelper;
use App\Utils\Validator;
use PDOException;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class VendedorController
{
    private $model;
    private $controller;

    public function __construct($pdo)
    {
        $this->model = new VendedorModel($pdo);
        $this->controller = new UsuarioController($pdo);
    }

    /**
     * Registra un nuevo vendedor
     * @param array $data Datos del usuario a registrar
     * @return array Respuesta con estado y datos
     */
    public function registrar($payload, $data)
    {
        $id_usuario = $payload->sub;
        $reglasVendedor = [
            'tipo_vendedor' => ['requerido'],
            'cuenta_bancaria' => ['requerido'],
            'razon_social' => ['requerido'],
            'nit' => ['requerido'],
            'correo_comercial' => ['email']
        ];

        $errores = Validator::validarCampos($data, $reglasVendedor);


        if (!empty($errores)) {
            return ResponseHelper::validationError($errores);
        }

        $resultadoRegistro = $this->model->crear($data, $id_usuario);
        // 2. Si el registro fue exitoso, actualiza el token
        if ($resultadoRegistro['status'] == 'success') {

            $usuarioActualizado = $this->controller->actualizarTokenPorRol($id_usuario);

            $key = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
            $nuevoPayload = [
                'iss' => 'emarket',
                'sub' => $usuarioActualizado['id_usuario'],
                'iat' => time(),
                'exp' => time() + (86400),
                'data' => [
                    'nombres' => $usuarioActualizado['nombres'],
                    'rol' => $usuarioActualizado['rol'] // ¡Rol actualizado!
                ]
            ];
            $nuevoToken = JWT::encode($nuevoPayload, $key, 'HS256');

            // 3. Devuelve la respuesta con el nuevo token
            return ResponseHelper::success('Registro de vendedor exitoso y token actualizado.', [
                'token' => $nuevoToken,
                'usuario' => $usuarioActualizado
            ]);
        } else {
            // El registro no fue exitoso
            return $resultadoRegistro;
        }
    }

    /**
     * Recupera los datos de un usuario autenticado
     * @param string $authHeader Encabezado de autorización con el token JWT
     * @return array Respuesta con estado y datos del usuario
     */
    public function leer($idVendedor)
    {
        return $this->model->recuperar($idVendedor);
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
            'tipo_vendedor' => trim($data['tipo_vendedor'] ?? ''),
            'cuenta_bancaria' => trim($data['cuenta_bancaria'] ?? ''),
            'nit' => trim($data['nit'] ?? ''),
            'matricula_comercial' => trim($data['matricula_comercial'] ?? null),
            'correo_comercial' => trim($data['correo_comercial']),
            'telefono_comercial' => trim($data['telefono_comercial']),
            'razon_social' => trim($data['razon_social']),
            'direcciones' => $data['direcciones'] ?? []
        ]);
    }
}
