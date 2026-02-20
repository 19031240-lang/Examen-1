<?php

require_once __DIR__ . '/../utils/GenPassword.php';

class PasswordService {

    public function generate($params) {

        $length = isset($params['length']) ? (int)$params['length'] : 16;

        if ($length < 4 || $length > 128) {
            throw new Exception("La longitud debe estar entre 4 y 128");
        }

        return generate_password($length, [
            'upper' => $params['includeUppercase'] ?? true,
            'lower' => $params['includeLowercase'] ?? true,
            'digits' => $params['includeNumbers'] ?? true,
            'symbols' => $params['includeSymbols'] ?? true,
            'avoid_ambiguous' => $params['excludeAmbiguous'] ?? true,
            'exclude' => $params['exclude'] ?? '',
            'require_each' => true
        ]);
    }

    public function generateMultiple($data) {

        $count = isset($data['count']) ? (int)$data['count'] : 1;
        $length = isset($data['length']) ? (int)$data['length'] : 16;

        if ($count < 1 || $count > 1000) {
            throw new Exception("La cantidad debe estar entre 1 y 1000");
        }

        if ($length < 4 || $length > 128) {
            throw new Exception("La longitud debe estar entre 4 y 128");
        }

        return generate_passwords(
            $count,
            $length,
            [
                'upper' => $data['includeUppercase'] ?? true,
                'lower' => $data['includeLowercase'] ?? true,
                'digits' => $data['includeNumbers'] ?? true,
                'symbols' => $data['includeSymbols'] ?? true,
                'avoid_ambiguous' => $data['excludeAmbiguous'] ?? true,
                'exclude' => $data['exclude'] ?? '',
                'require_each' => true
            ]
        );
    }

    public function validate($password, $requirements) {

        $errors = [];

        if (strlen($password) < $requirements['minLength']) {
            $errors[] = "No cumple longitud mínima";
        }

        if (!empty($requirements['requireUppercase']) && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Debe incluir mayúsculas";
        }

        if (!empty($requirements['requireNumbers']) && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Debe incluir números";
        }

        if (!empty($requirements['requireSymbols']) && !preg_match('/[\W]/', $password)) {
            $errors[] = "Debe incluir símbolos";
        }

        return empty($errors)
            ? ["valid" => true]
            : ["valid" => false, "errors" => $errors];
    }
}