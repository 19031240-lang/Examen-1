<?php
require_once __DIR__ . "/../services/PasswordService.php";

class PasswordController {

    private $service;

    public function __construct() {
        $this->service = new PasswordService();
    }

    public function generatePassword() {
        try {
            $password = $this->service->generate($_GET);

            echo json_encode([
                "success" => true,
                "password" => $password
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                "error" => $e->getMessage()
            ]);
        }
    }

    public function generateMultiplePasswords() {

    $data = json_decode(file_get_contents("php://input"), true);

    try {
        $passwords = $this->service->generateMultiple($data);

        echo json_encode([
            "success" => true,
            "passwords" => $passwords
        ]);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(["error" => $e->getMessage()]);
    }
}
}