<?php

namespace App\Modules\Vendedores;

use App\Utils\ResponseHelper;
use App\Utils\Validator;
use PDOException;
use PDO;
use Exception;

class VendedorModel
{
    private $db;
    private Validator $validator;

    public function __construct($pdo)
    {
        $this->db = $pdo;
        // Inicialización de la propiedad en el constructor
        // Se le pasa la conexión a la base de datos (PDO) que necesita
        $this->validator = new Validator($this->db);
    }

    /**
     * Crea un nuevo vendedor en la base de datos
     * @param int $id_usuario
     * @param string $tipo_vendedor
     * @param string $cuenta_bancaria (unico)
     * @param string $nit (unico)
     * @param string|null $matricula_comercial
     * @param string|null $correo_comercial
     * @param string|null $telefono_comercial
     * @param string $razon_social (unico)
     * @param string $id_direccion_principal
     * @return array Respuesta con estado y datos
     * "id_vendedor" INT [pk, increment]
     */
    public function crear($data, $id_usuario)
    {
        try {
            $this->db->beginTransaction();

            // Verificar unicidad de datos
            if ($this->validator->datoExiste('vendedor', 'cuenta_bancaria', $data['cuenta_bancaria'])) {
                return ResponseHelper::duplicateError('cuenta_bancaria');
            }
            if ($this->validator->datoExiste('vendedor', 'nit', $data['nit'])) {
                return ResponseHelper::duplicateError('nit');
            }
            if (!empty($data['razon_social']) && $this->validator->datoExiste('vendedor', 'razon_social', $data['razon_social'])) {
                return ResponseHelper::duplicateError('razon_social');
            }

            $sql = "INSERT INTO vendedor (id_usuario, tipo_vendedor, cuenta_bancaria, nit, matricula_comercial, correo_comercial, telefono_comercial, razon_social) VALUES (?, ?, ?,?, ?, ?,?,?)";
            $stmtVendedor = $this->db->prepare($sql);

            $stmtVendedor->execute([$id_usuario, $data['tipo_vendedor'], $data['cuenta_bancaria'], $data['nit'], $data['matricula_comercial'] ?? '', $data['correo_comercial'], $data['telefono_comercial'], $data['razon_social'] ?? '']);
            $idVendedor = $this->db->lastInsertId();

            $idDireccionPrincipal = null;

            // 2. Insertar direcciones
            foreach ($data['direcciones'] as $index => $dir) {
                $stmtDireccion = $this->db->prepare("
                INSERT INTO direccion (id_vendedor, departamento, provincia, ciudad, zona, calle, numero, referencias)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
                $stmtDireccion->execute([
                    $idVendedor,
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

            // 3. Actualizar dirección principal en vendedor
            if ($idDireccionPrincipal) {
                $stmtUpdate = $this->db->prepare("
                UPDATE vendedor SET id_direccion_principal = ? WHERE id_vendedor = ?
            ");
                $stmtUpdate->execute([$idDireccionPrincipal, $idVendedor]);
            }
            //4. Actualizar rol del Usuario
            $stmtUpdateRol = $this->db->prepare("
                UPDATE usuario SET rol = ? WHERE id_usuario = ?
            ");
            $stmtUpdateRol->execute(["vendedor", $id_usuario]);

            $this->db->commit();
            return ResponseHelper::success(
                'Vendedor registrado exitosamente',
                'vendedor_id:',
                $idVendedor
            );
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ResponseHelper::duplicateError('dato');
            }
            return ResponseHelper::databaseError($e->getMessage());
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Recupera un vendedor y todas sus direcciones asociadas.
     * @param int $id_vendedor El ID del vendedor a buscar.
     * @return array Resultado de la operación.
     */
    public function recuperar($id_vendedor)
    {
        try {
            // Consulta SQL que une la tabla de vendedores con la de direcciones.
            // Usamos LEFT JOIN para obtener el vendedor incluso si no tiene direcciones.
            $sql = "SELECT 
                    v.id_vendedor, v.tipo_vendedor, v.cuenta_bancaria, v.nit, v.matricula_comercial, v.correo_comercial, v.telefono_comercial,v.razon_social, v.id_direccion_principal, 
                    d.id_direccion, d.departamento, d.provincia, d.ciudad, d.zona, d.calle, d.numero, d.referencias
                FROM 
                    vendedor v
                LEFT JOIN 
                    direccion d ON v.id_vendedor = d.id_vendedor
                WHERE 
                    v.id_vendedor = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_vendedor]);

            // Obtenemos todos los resultados. Si un usuario tiene 3 direcciones, obtendremos 3 filas.
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Si no se encontró ningún registro para ese ID, retornamos un error.
            if (empty($resultados)) {
                return ResponseHelper::error('Vendedor no encontrado', 404);
            }

            // Estructuramos la respuesta para que no se repitan los datos del vendedor.
            // El vendedor será el objeto principal y sus direcciones estarán en un array anidado.
            $vendedor = [
                'id_vendedor' => $resultados[0]['id_vendedor'],
                'tipo_vendedor' => $resultados[0]['tipo_vendedor'],
                'cuenta_bancaria' => $resultados[0]['cuenta_bancaria'],
                'nit' => $resultados[0]['nit'],
                'matricula_comercial' => $resultados[0]['matricula_comercial'],
                'correo_comercial' => $resultados[0]['correo_comercial'],
                'telefono_comercial' => $resultados[0]['telefono_comercial'],
                'razon_social' => $resultados[0]['razon_social'],
                'id_direccion_principal' => $resultados[0]['id_direccion_principal'],
                'direcciones' => [] // Inicializamos el array de direcciones.
            ];

            // Recorremos los resultados para agrupar las direcciones.
            foreach ($resultados as $fila) {
                // Si la fila actual tiene datos de una dirección (id_direccion no es null).
                if ($fila['id_direccion']) {
                    $vendedor['direcciones'][] = [
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

            return ResponseHelper::success('Vendedor encontrado', $vendedor);
        } catch (PDOException $e) {
            // En caso de un error con la base de datos, lo reportamos.
            return ResponseHelper::databaseError($e->getMessage());
        }
    }
    /**
     * Recupera un vendedor usando el ID del usuario.
     * @param int $id_usuario El ID del usuario a buscar.
     * @return array Resultado de la operación.
     */
    public function recuperarPorIdUsuario($id_usuario)
    {
        try {
            $sql = "SELECT id_vendedor FROM vendedor WHERE id_usuario = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_usuario]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            // Si el usuario no es un vendedor
            if (!$resultado) {
                return ResponseHelper::error('No se encontró un vendedor asociado a este usuario', 404);
            }

            $id_vendedor = $resultado['id_vendedor'];

            // Reutilizamos la función recuperar para obtener todos los datos
            return $this->recuperar($id_vendedor);
        } catch (PDOException $e) {
            return ResponseHelper::databaseError($e->getMessage());
        }
    }
    /**
     * Actualiza la información de un vendedor y sus direcciones.
     * @param int $id_vendedor El ID del vendedor a actualizar.
     * @param array $datos Los nuevos datos del vendedor, incluyendo un array de 'direcciones'.
     * @return array Resultado de la operación.
     */

    public function modificar($id_usuario, $datos)
    {
        // NO confíes en el ID del JSON. Búscalo en la BD.
        $stmt = $this->db->prepare("SELECT id_vendedor FROM vendedor WHERE id_usuario = ?");
        $stmt->execute([$id_usuario]);
        $vendedor = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si no se encuentra un vendedor para ese usuario, devuelve un error.
        if (!$vendedor) {
            return ResponseHelper::error('Vendedor no encontrado', 404);
        }

        // Ahora sí, usa el ID seguro obtenido de la base de datos.
        $idVendedor = $vendedor['id_vendedor'];

        $this->db->beginTransaction();

        try {
            // Verificar unicidad de datos, excluyendo al propio vendedor
            if ($this->validator->datoExiste('vendedor', 'cuenta_bancaria', $datos['cuenta_bancaria'], 'id_usuario', $id_usuario)) {
                return ResponseHelper::duplicateError('cuenta_bancaria');
            }
            if ($this->validator->datoExiste('vendedor', 'nit', $datos['nit'], 'id_usuario', $id_usuario)) {
                return ResponseHelper::duplicateError('nit');
            }
            if ($this->validator->datoExiste('vendedor', 'razon_social', $datos['razon_social'], 'id_usuario', $id_usuario)) {
                return ResponseHelper::duplicateError('razon_social');
            }

            // 1. Actualizar datos del usuario
            $sqlVendedor = "UPDATE vendedor SET tipo_vendedor = ?, cuenta_bancaria = ?, nit = ?, matricula_comercial = ?, correo_comercial = ?, telefono_comercial = ?, razon_social = ? WHERE id_vendedor = ?";
            $stmtVendedor = $this->db->prepare($sqlVendedor);
            $stmtVendedor->execute([
                $datos['tipo_vendedor'] ?? '',
                $datos['cuenta_bancaria'] ?? '',
                $datos['nit'] ?? '',
                $datos['matricula_comercial'] ?? null,
                $datos['correo_comercial'] ?? null,
                $datos['telefono_comercial'] ?? null,
                $datos['razon_social'] ?? null,
                $idVendedor
            ]);


            // 2. Eliminar referencia a dirección principal para evitar error de integridad
            $stmtNull = $this->db->prepare("UPDATE vendedor SET id_direccion_principal = NULL WHERE id_vendedor = ?");
            $stmtNull->execute([$idVendedor]);

            // 3. Borrar direcciones antiguas
            $stmtDelete = $this->db->prepare("DELETE FROM direccion WHERE id_vendedor = ?");
            $stmtDelete->execute([$idVendedor]);

            $idDireccionPrincipal = null;

            // 4. Insertar nuevas direcciones
            foreach ($datos['direcciones'] as $index => $dir) {
                $stmtDireccion = $this->db->prepare("
                INSERT INTO direccion (id_vendedor, departamento, provincia, ciudad, zona, calle, numero, referencias)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
                $stmtDireccion->execute([
                    $idVendedor,
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
                UPDATE vendedor SET id_direccion_principal = ? WHERE id_vendedor = ?
            ");
                $stmtUpdate->execute([$idDireccionPrincipal, $idVendedor]);
            }

            $this->db->commit();
            return ResponseHelper::success('Vendedor actualizado exitosamente');
        } catch (Exception $e) {
            $this->db->rollBack();
            return ResponseHelper::databaseError($e->getMessage());
        }
    }
}
