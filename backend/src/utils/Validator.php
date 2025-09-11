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


    public static function validarCampos(array $data, array $reglas)
    {
        $errores = [];

        foreach ($reglas as $campo => $validaciones) {
            $valor = isset($data[$campo]) ? trim($data[$campo]) : '';

            foreach ($validaciones as $validacion) {
                switch ($validacion) {
                    case 'requerido':
                        if (empty($valor)) {
                            $errores[$campo] = "El campo $campo es requerido.";
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
