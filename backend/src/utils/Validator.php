<?php

namespace App\Utils;

class Validator
{
    public static function validarUsuario($data)
    {
        $errores = [];

        if (!isset($data['nombres']) || empty(trim($data['nombres']))) {
            $errores['nombres'] = 'El nombre es requerido';
        }
        if (!isset($data['apellidos']) || empty(trim($data['apellidos']))) {
            $errores['apellidos'] = 'El apellidos es requerido';
        }

        if (!isset($data['email']) || empty(trim($data['email']))) {
            $errores['email'] = 'El email es requerido';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'Formato de email inválido';
        }

        if (!isset($data['password']) || empty($data['password'])) {
            $errores['password'] = 'La contraseña es requerida';
        } elseif (strlen($data['password']) < 6) {
            $errores['password'] = 'La contraseña debe tener al menos 6 caracteres';
        }

        return $errores;
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
}
