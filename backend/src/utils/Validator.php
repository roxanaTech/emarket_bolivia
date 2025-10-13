<?php

namespace App\Utils;

class Validator
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }


    public static function validarLogin($data)
    {
        $errores = [];

        if (!isset($data['email']) || empty(trim($data['email']))) {
            $errores['email'] = 'El email es requerido';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'Formato de email inválido';
        }

        if (!isset($data['password']) || empty($data['password'])) {
            $errores['password'] = 'La contraseña es requerida';
        }

        return $errores;
    }

    /**
     * Valida un conjunto de datos según reglas definidas.
     *
     * Reglas soportadas:
     * - 'requerido'
     * - 'min_len:N' (ej: 'min_len:3')
     * - 'max_len:N'
     * - 'email'
     * - 'numeric'
     * - 'positive' (número > 0)
     * - 'non_negative' (número ≥ 0)
     * - 'integer'
     * - 'in:valor1,valor2,...'
     * - 'unique:tabla,columna[,id_columna,id_actual]'
     * - 'regex:patrón'
     */
    public static function validarCampos(array $data, array $reglas): array
    {
        $errores = [];

        foreach ($reglas as $campo => $validaciones) {
            $valor = $data[$campo] ?? null;

            foreach ($validaciones as $validacion) {
                // Parsear reglas con parámetros (ej: min_len:5)
                if (strpos($validacion, ':') !== false) {
                    [$tipo, $param] = explode(':', $validacion, 2);
                } else {
                    $tipo = $validacion;
                    $param = null;
                }

                switch ($tipo) {
                    case 'requerido':
                        if ($valor === null || $valor === '' || (is_array($valor) && empty($valor))) {
                            $errores[$campo] = "El campo '$campo' es requerido.";
                        }
                        break;
                    case 'email':
                        if (!empty($valor) && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                            $errores[$campo] = "Formato de email inválido.";
                        }
                        break;

                    case 'min8':
                        if (!empty($valor) && strlen($valor) < 8) {
                            $errores[$campo] = "El campo $campo debe tener al menos 8 caracteres.";
                        }
                        break;
                    case 'min_len':
                        if ($valor !== null && strlen(trim($valor)) < (int)$param) {
                            $errores[$campo] = "El campo '$campo' debe tener al menos $param caracteres.";
                        }
                        break;

                    case 'max_len':
                        if ($valor !== null && strlen(trim($valor)) > (int)$param) {
                            $errores[$campo] = "El campo '$campo' no debe exceder $param caracteres.";
                        }
                        break;

                    case 'numeric':
                        if ($valor !== null && !is_numeric($valor)) {
                            $errores[$campo] = "El campo '$campo' debe ser un número.";
                        }
                        break;

                    case 'positive':
                        if ($valor !== null && (!is_numeric($valor) || $valor <= 0)) {
                            $errores[$campo] = "El campo '$campo' debe ser un número mayor a 0.";
                        }
                        break;

                    case 'non_negative':
                        if ($valor !== null && (!is_numeric($valor) || $valor < 0)) {
                            $errores[$campo] = "El campo '$campo' debe ser un número no negativo.";
                        }
                        break;

                    case 'integer':
                        if ($valor !== null && (!is_numeric($valor) || (int)$valor != $valor)) {
                            $errores[$campo] = "El campo '$campo' debe ser un número entero.";
                        }
                        break;

                    case 'in':
                        $opciones = explode(',', $param);
                        if ($valor !== null && !in_array($valor, $opciones)) {
                            $errores[$campo] = "El campo '$campo' debe ser uno de: " . implode(', ', $opciones) . ".";
                        }
                        break;

                    case 'regex':
                        if ($valor !== null && !preg_match($param, $valor)) {
                            $errores[$campo] = "El campo '$campo' no cumple con el formato esperado.";
                        }
                        break;
                }
            }
        }

        return $errores;
    }

    /**
     * Verifica si un valor existe en una columna de una tabla,
     * opcionalmente excluyendo un registro por su ID.
     *
     * @param string $tabla La tabla en la que buscar (ej. 'usuario').
     * @param string $columna La columna a verificar (ej. 'email').
     * @param mixed $valor El valor a buscar.
     * @param string $id_columna_excluir La columna de ID a excluir (ej. 'id_usuario').
     * @param int|null $id_a_excluir El ID del registro a excluir.
     * @return bool
     */
    public function datoExiste(string $tabla, string $columna, $valor, string $id_columna_excluir = 'id_usuario', ?int $id_a_excluir = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$tabla} WHERE {$columna} = ?";
        $params = [$valor];

        if ($id_a_excluir !== null) {
            $sql .= " AND {$id_columna_excluir} != ?";
            $params[] = $id_a_excluir;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }
}
