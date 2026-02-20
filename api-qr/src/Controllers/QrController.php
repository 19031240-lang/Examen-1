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
                echo json_encode(["error"=>"JSON invalido"]);
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
                echo json_encode(["error"=>"Tamaño invalido (100-1000)"]);
                return;
            }

            if(!Validator::validateErrorLevel($errorLevel)){
                http_response_code(400);
                echo json_encode(["error"=>"Nivel de correccion invalido (L,M,Q,H)"]);
                return;
            }

            switch($data['type']){

                case 'text':
                    if(!isset($data['content'])){
                        http_response_code(400);
                        echo json_encode(["error"=>"Contenido requerido"]);
                        return;
                    }
                    $result = $this->service->generate($data['content'], $size, $errorLevel);
                break;

                case 'url':
                    if(!isset($data['content']) || !Validator::validateURL($data['content'])){
                        http_response_code(400);
                        echo json_encode(["error"=>"URL invalida"]);
                        return;
                    }
                    $result = $this->service->generate($data['content'], $size, $errorLevel);
                break;

                case 'wifi':
                    if(
                        !isset($data['ssid']) ||
                        !isset($data['password']) ||
                        !isset($data['security']) ||
                        !Validator::validateWifiSecurity($data['security'])
                    ){
                        http_response_code(400);
                        echo json_encode(["error"=>"Datos WiFi invalidos"]);
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
                    if(
                        !isset($data['lat']) ||
                        !isset($data['lon']) ||
                        !Validator::validateCoordinates($data['lat'],$data['lon'])
                    ){
                        http_response_code(400);
                        echo json_encode(["error"=>"Coordenadas invalidas"]);
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