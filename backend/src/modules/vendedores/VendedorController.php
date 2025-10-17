<?php

namespace App\Modules\Vendedores;

use App\Modules\Usuarios\UsuarioController;
use App\Utils\ResponseHelper;
use App\Utils\Validator;
use App\Services\VendedorService;
use PDOException;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class VendedorController
{
    private $model;
    private $controller;
    private $vendedorService;
    private $db;

    public function __construct($pdo)
    {
        $this->model = new VendedorModel($pdo);
        $this->controller = new UsuarioController($pdo);
        $this->vendedorService = new VendedorService($pdo);
        $this->db = $pdo;
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
    /**
     * Obtiene los KPIs del vendedor autenticado.
     */
    public function obtenerKPIs($payload): array
    {
        $idUsuario = $payload->sub;

        // Obtener id_vendedor desde el usuario
        $sql = "SELECT id_vendedor FROM vendedor WHERE id_usuario = :id_usuario";
        $stmt = $this->db->prepare($sql); // Acceso temporal a db
        $stmt->bindParam(':id_usuario', $idUsuario);
        $stmt->execute();
        $vendedor = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$vendedor) {
            return ResponseHelper::error('No eres vendedor.', 403);
        }

        $kpis = $this->vendedorService->obtenerKPIsVendedor($vendedor['id_vendedor']);
        return ResponseHelper::success('KPIs obtenidos.', $kpis);
    }
    /**
     * Obtiene datos para gráficos del vendedor.
     */
    public function obtenerDatosGraficos($payload): array
    {
        $idUsuario = $payload->sub;

        // Obtener id_vendedor
        $sql = "SELECT id_vendedor FROM vendedor WHERE id_usuario = :id_usuario";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $idUsuario);
        $stmt->execute();
        $vendedor = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$vendedor) {
            return ResponseHelper::error('No eres vendedor.', 403);
        }

        $idVendedor = $vendedor['id_vendedor'];

        $datos = [
            'ventas_mensuales' => $this->vendedorService->obtenerVentasMensuales($idVendedor),
            'ordenes_por_categoria' => $this->vendedorService->obtenerOrdenesPorCategoria($idVendedor),
            'distribucion_estados' => $this->vendedorService->obtenerDistribucionEstados($idVendedor)
        ];

        return ResponseHelper::success('Datos para gráficos obtenidos.', $datos);
    }
}
