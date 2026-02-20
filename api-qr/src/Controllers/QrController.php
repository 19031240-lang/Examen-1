<?php
namespace App\Controllers;

use App\Services\QrService;
use App\Utils\Validator;

class QrController
{
    private QrService $service;

    public function __construct()
    {
        $this->service = new QrService();
    }

    public function handleRequest()
    {
        try {

            $data = json_decode(file_get_contents("php://input"), true);

            if(!$data){
                http_response_code(400);
                echo json_encode(["error"=>"JSON inválido"]);
                return;
            }

            if(!isset($data['type'])){
                http_response_code(400);
                echo json_encode(["error"=>"Tipo requerido"]);
                return;
            }

            $size = $data['size'] ?? 300;
            $errorLevel = $data['errorLevel'] ?? 'M';

            if(!Validator::validateSize($size)){
                http_response_code(400);
                echo json_encode(["error"=>"Tamaño inválido"]);
                return;
            }

            if(!Validator::validateErrorLevel($errorLevel)){
                http_response_code(400);
                echo json_encode(["error"=>"Nivel de corrección inválido"]);
                return;
            }

            switch($data['type']){

                case 'text':
                    $result = $this->service->generate($data['content'], $size, $errorLevel);
                break;

                case 'url':
                    if(!Validator::validateURL($data['content'])){
                        http_response_code(400);
                        echo json_encode(["error"=>"URL inválida"]);
                        return;
                    }
                    $result = $this->service->generate($data['content'], $size, $errorLevel);
                break;

                case 'wifi':
                    if(!Validator::validateWifiSecurity($data['security'])){
                        http_response_code(400);
                        echo json_encode(["error"=>"Tipo de seguridad inválido"]);
                        return;
                    }
                    $result = $this->service->generateWifi(
                        $data['ssid'],
                        $data['password'],
                        $data['security'],
                        $size,
                        $errorLevel
                    );
                break;

                case 'geo':
                    if(!Validator::validateCoordinates($data['lat'],$data['lon'])){
                        http_response_code(400);
                        echo json_encode(["error"=>"Coordenadas inválidas"]);
                        return;
                    }
                    $result = $this->service->generateGeo(
                        $data['lat'],
                        $data['lon'],
                        $size,
                        $errorLevel
                    );
                break;

                default:
                    http_response_code(415);
                    echo json_encode(["error"=>"Tipo no soportado"]);
                return;
            }

            http_response_code(200);
            echo json_encode($result);

        } catch(\Exception $e){
            http_response_code(500);
            echo json_encode(["error"=>$e->getMessage()]);
        }
    }
}